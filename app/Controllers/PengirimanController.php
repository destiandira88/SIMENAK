<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class PengirimanController extends BaseController
{
    protected $helpers = ['form', 'url', 'notification'];

    /** Daftar pesanan yang perlu diproses pengirimannya (admin). */
    public function index(): string|RedirectResponse
    {
        if ((string) session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'));
        }

        $db = \Config\Database::connect();

        $orders = $db->table('orders o')
            ->select('o.id_order, o.kode_order, o.status, o.jenis_pelanggan,
                      o.metode_pengiriman, o.alamat_kirim, o.total_harga, o.require_dp,
                      u.nama as nama_pelanggan, u.email as email_pelanggan,
                      k.nama_produk, p.tier_perusahaan, '
                      . sqlLatestOrderStatusPaymentFields('o.id_order'))
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->join('katalog k', 'k.id_katalog = o.id_katalog', 'left')
            ->groupStart()
                ->whereIn('o.status', ['finishing', 'siap_kirim', 'siap_diambil', 'dikirim', 'pelunasan_terverifikasi'])
            ->groupEnd()
            ->orGroupStart()
                ->where('o.status', 'menunggu_verifikasi_lunas')
                ->groupStart()
                    ->where('o.jenis_pelanggan', 'perseorangan')
                    ->orWhere('p.tier_perusahaan', 'pemula')
                ->groupEnd()
            ->groupEnd()
            ->orderBy('o.created_at', 'ASC')
            ->get()->getResultArray();

        $pengirimanData = $db->table('pengiriman pg')
            ->select('pg.*')
            ->whereIn('pg.id_order', array_column($orders, 'id_order') ?: [0])
            ->get()->getResultArray();

        $pengirimanByOrder = [];
        foreach ($pengirimanData as $pg) {
            $pengirimanByOrder[(int) $pg['id_order']] = $pg;
        }

        return view('pengiriman/index', [
            'title'              => 'Manajemen Pengiriman',
            'page_title'         => 'Manajemen Pengiriman',
            'orders'             => $orders,
            'pengirimanByOrder'  => $pengirimanByOrder,
        ]);
    }

    /**
     * Proses perubahan status pengiriman.
     *
     * POST params:
     *   aksi            : set_siap | set_dikirim | konfirmasi_diambil
     *   metode_kirim    : kurir | ambil_sendiri (set_siap)
     *   no_resi         : wajib untuk set_dikirim (kurir)
     *   nama_ekspedisi  : opsional (JNE, JNT, dll)
     */
    public function proses(int $idOrder): RedirectResponse
    {
        if ((string) session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'));
        }

        $db   = \Config\Database::connect();
        $aksi = (string) $this->request->getPost('aksi');

        $order = $db->table('orders o')
            ->select('o.*, u.id_user AS id_user_pelanggan, u.nama, u.email, pl.tier_perusahaan')
            ->join('pelanggan pl', 'pl.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = pl.id_user')
            ->where('o.id_order', $idOrder)
            ->get()->getRowArray();

        if ($order === null) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        $currentStatus   = (string) $order['status'];
        $kodeOrder       = (string) $order['kode_order'];
        $idUserPelanggan = (int) $order['id_user_pelanggan'];
        $pelangganCtx    = ['tier_perusahaan' => $order['tier_perusahaan'] ?? null];
        $beforeShip      = isPelunasanSebelumKirim($order, $pelangganCtx);

        try {
            $db->transStart();

            if ($aksi === 'set_siap') {
                // Admin set siap kirim/diambil setelah produksi selesai (finishing)
                if ($currentStatus !== 'finishing') {
                    return redirect()->back()
                        ->with('error', 'Status pesanan tidak memungkinkan update ke siap kirim/diambil.');
                }

                $metodeKirim = (string) ($order['metode_pengiriman'] ?? 'kurir');
                $newStatus   = $metodeKirim === 'ambil_sendiri' ? 'siap_diambil' : 'siap_kirim';

                $db->table('orders')->where('id_order', $idOrder)->update([
                    'status' => $newStatus,
                ]);

                $emailSubject = "[No-Reply] Pesanan " . ($newStatus === 'siap_diambil' ? 'Siap Diambil' : 'Siap Dikirim') . "-{$kodeOrder}";
                $emailBody    = '<p>Halo <strong>' . esc((string) $order['nama']) . '</strong>,</p>'
                    . "<p>Pesanan <strong>{$kodeOrder}</strong> "
                    . ($newStatus === 'siap_diambil'
                        ? 'sudah siap untuk diambil di toko kami.'
                        : 'sedang dalam persiapan pengiriman.')
                    . '</p>';

                sendNotifEmail((string) $order['email'], $emailSubject, $emailBody);
                sendNotifInApp($idUserPelanggan, $idOrder, 'Pesanan ' . ($newStatus === 'siap_diambil' ? 'Siap Diambil' : 'Siap Dikirim'), "Pesanan {$kodeOrder} siap.");

            } elseif ($aksi === 'set_dikirim') {
                if (isMetodeAmbilSendiri($order)) {
                    return redirect()->back()->with('error', 'Pesanan ambil sendiri tidak memerlukan nomor resi.');
                }

                $allowedKirim = $beforeShip
                    ? ['pelunasan_terverifikasi']
                    : ['siap_kirim'];

                if (!in_array($currentStatus, $allowedKirim, true)) {
                    return redirect()->back()
                        ->with('error', $beforeShip
                            ? 'Pelunasan belum diverifikasi. Pesanan belum dapat dikirim.'
                            : 'Pesanan belum siap untuk dikirim.');
                }

                $noResi         = trim((string) $this->request->getPost('no_resi'));
                $namaEkspedisi  = trim((string) $this->request->getPost('nama_ekspedisi'));

                if ($noResi === '') {
                    return redirect()->back()->with('error', 'Nomor resi wajib diisi.');
                }

                $now = date('Y-m-d H:i:s');

                $db->table('orders')->where('id_order', $idOrder)->update(['status' => 'dikirim']);

                $existingPg = $db->table('pengiriman')->where('id_order', $idOrder)->get()->getRowArray();
                if ($existingPg) {
                    $db->table('pengiriman')->where('id_order', $idOrder)->update([
                        'no_resi'        => $noResi,
                        'nama_ekspedisi' => $namaEkspedisi ?: null,
                        'status_kirim'   => 'dikirim',
                        'tgl_kirim'      => $now,
                        'tgl_diterima'   => null,
                    ]);
                } else {
                    $db->table('pengiriman')->insert([
                        'id_order'       => $idOrder,
                        'no_resi'        => $noResi,
                        'nama_ekspedisi' => $namaEkspedisi ?: null,
                        'status_kirim'   => 'dikirim',
                        'tgl_kirim'      => $now,
                    ]);
                }

                sendNotifEmail(
                    (string) $order['email'],
                    "[No-Reply] Pesanan Dikirim-{$kodeOrder}",
                    '<p>Halo <strong>' . esc((string) $order['nama']) . '</strong>,</p>'
                    . "<p>Pesanan <strong>{$kodeOrder}</strong> telah dikirim.</p>"
                    . "<p>No. Resi: <strong>{$noResi}</strong>"
                    . ($namaEkspedisi ? " via <strong>{$namaEkspedisi}</strong>" : '')
                    . '</p>'
                );
                sendNotifInApp($idUserPelanggan, $idOrder, 'Pesanan Dikirim', "Pesanan {$kodeOrder} dikirim. Resi: {$noResi}" . ($namaEkspedisi ? " ({$namaEkspedisi})" : ''));

            } elseif ($aksi === 'konfirmasi_diambil') {
                if (!isMetodeAmbilSendiri($order)) {
                    return redirect()->back()->with('error', 'Konfirmasi diambil hanya untuk metode ambil sendiri.');
                }

                $now = date('Y-m-d H:i:s');

                if ($beforeShip) {
                    if ($currentStatus !== 'pelunasan_terverifikasi') {
                        return redirect()->back()->with('error', 'Pelunasan belum diverifikasi. Barang belum dapat diserahkan.');
                    }

                    $db->table('orders')->where('id_order', $idOrder)->update(['status' => 'selesai']);

                    $existingPg = $db->table('pengiriman')->where('id_order', $idOrder)->get()->getRowArray();
                    if ($existingPg) {
                        $db->table('pengiriman')->where('id_order', $idOrder)->update([
                            'status_kirim' => 'diterima',
                            'tgl_diterima' => $now,
                        ]);
                    } else {
                        $db->table('pengiriman')->insert([
                            'id_order'     => $idOrder,
                            'status_kirim' => 'diterima',
                            'tgl_kirim'    => $now,
                            'tgl_diterima' => $now,
                        ]);
                    }

                    sendNotifInApp($idUserPelanggan, $idOrder, 'Pesanan Selesai', "Pesanan {$kodeOrder} telah diambil. Terima kasih!");
                    sendNotifEmail(
                        (string) $order['email'],
                        "[No-Reply] Pesanan Selesai-{$kodeOrder}",
                        '<p>Halo <strong>' . esc((string) $order['nama']) . '</strong>,</p>'
                        . "<p>Pesanan <strong>{$kodeOrder}</strong> telah diambil. Terima kasih!</p>"
                    );
                } else {
                    if ($currentStatus !== 'siap_diambil') {
                        return redirect()->back()->with('error', 'Pesanan belum siap diambil.');
                    }

                    $db->table('orders')->where('id_order', $idOrder)->update(['status' => 'menunggu_verifikasi_lunas']);

                    $existingPg = $db->table('pengiriman')->where('id_order', $idOrder)->get()->getRowArray();
                    if ($existingPg) {
                        $db->table('pengiriman')->where('id_order', $idOrder)->update([
                            'status_kirim' => 'diambil',
                            'tgl_diterima' => $now,
                        ]);
                    } else {
                        $db->table('pengiriman')->insert([
                            'id_order'     => $idOrder,
                            'status_kirim' => 'diambil',
                            'tgl_kirim'    => $now,
                            'tgl_diterima' => $now,
                        ]);
                    }

                    sendNotifInApp(
                        $idUserPelanggan,
                        $idOrder,
                        'Nota Tagihan Pelunasan',
                        "Pesanan {$kodeOrder} telah diambil. Silakan upload bukti transfer pelunasan."
                    );
                    sendNotifEmail(
                        (string) $order['email'],
                        "[No-Reply] Pesanan Diambil-Nota Tagihan {$kodeOrder}",
                        '<p>Halo <strong>' . esc((string) $order['nama']) . '</strong>,</p>'
                        . "<p>Pesanan <strong>{$kodeOrder}</strong> telah diambil.</p>"
                        . '<p>Silakan lakukan transfer pelunasan sesuai nota tagihan di detail pesanan.</p>'
                    );
                }

            } else {
                return redirect()->back()->with('error', 'Aksi tidak dikenal.');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[PengirimanController::proses] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal memproses pengiriman.');
        }

        return redirect()->back()->with('success', 'Status pengiriman berhasil diperbarui.');
    }

    /**
     * Pelanggan konfirmasi penerimaan-hanya kurir, status dikirim.
     */
    public function konfirmasiDiterimaPelanggan(): RedirectResponse
    {
        if ((string) session()->get('role') !== 'pelanggan') {
            return redirect()->to(site_url('dashboard'));
        }

        $idOrder     = (int) $this->request->getPost('id_order');
        $idPelanggan = (int) session()->get('id_pelanggan');
        $db          = \Config\Database::connect();

        $order = $db->table('orders o')
            ->select('o.*, u.id_user AS id_user_pelanggan, u.nama, u.email, pl.tier_perusahaan')
            ->join('pelanggan pl', 'pl.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = pl.id_user')
            ->where('o.id_order', $idOrder)
            ->where('o.id_pelanggan', $idPelanggan)
            ->where('o.status', 'dikirim')
            ->get()->getRowArray();

        if ($order === null) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan atau tidak dapat dikonfirmasi.');
        }

        if (isMetodeAmbilSendiri($order)) {
            return redirect()->back()->with('error', 'Pesanan ambil sendiri dikonfirmasi oleh admin saat pengambilan.');
        }

        $kodeOrder       = (string) $order['kode_order'];
        $idUserPelanggan = (int) $order['id_user_pelanggan'];
        $pelangganCtx    = ['tier_perusahaan' => $order['tier_perusahaan'] ?? null];
        $beforeShip      = isPelunasanSebelumKirim($order, $pelangganCtx);

        try {
            $db->transStart();

            $now = date('Y-m-d H:i:s');

            if ($beforeShip) {
                $db->table('orders')->where('id_order', $idOrder)->update(['status' => 'selesai']);
                $db->table('pengiriman')->where('id_order', $idOrder)->update([
                    'status_kirim' => 'diterima',
                    'tgl_diterima' => $now,
                ]);
                sendNotifInApp($idUserPelanggan, $idOrder, 'Pesanan Selesai', "Pesanan {$kodeOrder} telah diterima. Terima kasih!");
                sendNotifEmail(
                    (string) $order['email'],
                    "[No-Reply] Pesanan Selesai-{$kodeOrder}",
                    '<p>Halo <strong>' . esc((string) $order['nama']) . '</strong>,</p>'
                    . "<p>Pesanan <strong>{$kodeOrder}</strong> telah diterima. Terima kasih!</p>"
                );
            } else {
                $db->table('orders')->where('id_order', $idOrder)->update(['status' => 'menunggu_verifikasi_lunas']);
                $db->table('pengiriman')->where('id_order', $idOrder)->update([
                    'status_kirim' => 'diterima',
                    'tgl_diterima' => $now,
                ]);
                sendNotifInApp(
                    $idUserPelanggan,
                    $idOrder,
                    'Nota Tagihan Pelunasan',
                    "Pesanan {$kodeOrder} diterima. Silakan upload bukti transfer pelunasan."
                );
                sendNotifEmail(
                    (string) $order['email'],
                    "[No-Reply] Pesanan Diterima-Nota Tagihan {$kodeOrder}",
                    '<p>Halo <strong>' . esc((string) $order['nama']) . '</strong>,</p>'
                    . "<p>Pesanan <strong>{$kodeOrder}</strong> telah diterima.</p>"
                    . '<p>Silakan lakukan transfer pelunasan sesuai nota tagihan di detail pesanan.</p>'
                );
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[PengirimanController::konfirmasiDiterimaPelanggan] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal mengkonfirmasi penerimaan pesanan.');
        }

        return redirect()->to(site_url('order/detail/' . $kodeOrder))
            ->with('success', 'Terima kasih! Pesanan dikonfirmasi diterima.');
    }
}
