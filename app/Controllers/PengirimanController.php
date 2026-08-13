<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class PengirimanController extends BaseController
{
    protected $helpers = ['form', 'url', 'notification'];

    /** Daftar pesanan yang perlu diproses pengirimannya (admin; owner hanya lihat). */
    public function index(): string|RedirectResponse
    {
        $role = (string) session()->get('role');
        if (! in_array($role, ['admin', 'owner'], true)) {
            return redirect()->to(site_url('dashboard'));
        }

        $readOnly = $role === 'owner';
        $db       = \Config\Database::connect();

        $orders = $db->table('orders o')
            ->select('o.id_order, o.kode_order, o.status, o.jenis_pelanggan,
                      o.metode_pengiriman, o.alamat_kirim, o.total_harga, o.require_dp,
                      u.nama as nama_pelanggan, u.email as email_pelanggan,
                      k.nama_produk, '
                      . sqlLatestOrderStatusPaymentFields('o.id_order'))
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->join('katalog k', 'k.id_katalog = o.id_katalog', 'left')
            ->groupStart()
                ->whereIn('o.status', ['finishing', 'siap_kirim', 'siap_diambil', 'dikirim', 'pelunasan_terverifikasi'])
            ->groupEnd()
            ->orGroupStart()
                ->where('o.status', 'menunggu_verifikasi_lunas')
                ->where('o.jenis_pelanggan', 'perseorangan')
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
            'title'             => $readOnly ? 'Pengiriman' : 'Manajemen Pengiriman',
            'page_title'        => $readOnly ? 'Pengiriman' : 'Manajemen Pengiriman',
            'orders'            => $orders,
            'pengirimanByOrder' => $pengirimanByOrder,
            'readOnly'          => $readOnly,
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
            ->select('o.*, u.id_user AS id_user_pelanggan, u.nama, u.email')
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
        $beforeShip      = isPelunasanSebelumKirim($order);
        $activityKeterangan = null;

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
                $activityKeterangan = "Mengubah status pesanan {$kodeOrder} dari {$currentStatus} menjadi {$newStatus}";

                $emailSubject = "[No-Reply] Pesanan " . ($newStatus === 'siap_diambil' ? 'Siap Diambil' : 'Siap Dikirim') . "-{$kodeOrder}";
                $emailBody    = '<p>Halo <strong>' . esc((string) $order['nama']) . '</strong>,</p>'
                    . "<p>Pesanan <strong>{$kodeOrder}</strong> "
                    . ($newStatus === 'siap_diambil'
                        ? 'sudah siap untuk diambil di toko kami.'
                        : 'sedang dalam persiapan pengiriman.')
                    . '</p>';

                sendNotifEmail((string) $order['email'], $emailSubject, $emailBody);
                sendNotifWaForEmail(
                    $db,
                    (string) $order['email'],
                    buildNotifWaText(
                        $newStatus === 'siap_diambil' ? "Pesanan Siap Diambil-{$kodeOrder}" : "Pesanan Siap Dikirim-{$kodeOrder}",
                        $newStatus === 'siap_diambil'
                            ? "Pesanan {$kodeOrder} siap diambil di toko kami."
                            : "Pesanan {$kodeOrder} sedang dalam persiapan pengiriman.",
                        site_url('order/detail/' . $kodeOrder)
                    )
                );
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
                $activityKeterangan = "Mengubah status pesanan {$kodeOrder} dari {$currentStatus} menjadi dikirim (resi: {$noResi})";

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
                        'kode_kirim'     => 'KRM-' . $kodeOrder,
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
                sendNotifWaForEmail(
                    $db,
                    (string) $order['email'],
                    buildNotifWaText(
                        "Pesanan Dikirim-{$kodeOrder}",
                        "Pesanan {$kodeOrder} dikirim. Resi: {$noResi}" . ($namaEkspedisi ? " ({$namaEkspedisi})" : ''),
                        site_url('order/detail/' . $kodeOrder)
                    )
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
                    $activityKeterangan = "Mengubah status pesanan {$kodeOrder} dari {$currentStatus} menjadi selesai (diambil pelanggan)";

                    $existingPg = $db->table('pengiriman')->where('id_order', $idOrder)->get()->getRowArray();
                    if ($existingPg) {
                        $db->table('pengiriman')->where('id_order', $idOrder)->update([
                            'status_kirim' => 'diterima',
                            'tgl_diterima' => $now,
                        ]);
                    } else {
                        $db->table('pengiriman')->insert([
                            'id_order'     => $idOrder,
                            'kode_kirim'   => 'KRM-' . $kodeOrder,
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
                    sendNotifWaForEmail(
                        $db,
                        (string) $order['email'],
                        buildNotifWaText(
                            "Pesanan Selesai-{$kodeOrder}",
                            "Pesanan {$kodeOrder} telah diambil. Terima kasih!",
                            site_url('order/detail/' . $kodeOrder)
                        )
                    );
                } else {
                    if ($currentStatus !== 'siap_diambil') {
                        return redirect()->back()->with('error', 'Pesanan belum siap diambil.');
                    }

                    $db->table('orders')->where('id_order', $idOrder)->update(['status' => 'menunggu_verifikasi_lunas']);
                    $activityKeterangan = "Mengubah status pesanan {$kodeOrder} dari {$currentStatus} menjadi menunggu_verifikasi_lunas (diambil pelanggan)";

                    $existingPg = $db->table('pengiriman')->where('id_order', $idOrder)->get()->getRowArray();
                    if ($existingPg) {
                        $db->table('pengiriman')->where('id_order', $idOrder)->update([
                            'status_kirim' => 'diambil',
                            'tgl_diterima' => $now,
                        ]);
                    } else {
                        $db->table('pengiriman')->insert([
                            'id_order'     => $idOrder,
                            'kode_kirim'   => 'KRM-' . $kodeOrder,
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
                    sendNotifWaForEmail(
                        $db,
                        (string) $order['email'],
                        buildNotifWaText(
                            "Pesanan Diambil-Nota Tagihan {$kodeOrder}",
                            "Pesanan {$kodeOrder} telah diambil. Silakan transfer pelunasan sesuai nota tagihan.",
                            site_url('order/detail/' . $kodeOrder)
                        )
                    );
                }

            } else {
                return redirect()->back()->with('error', 'Aksi tidak dikenal.');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal.');
            }

            if ($activityKeterangan !== null) {
                helper('activity_log');
                logActivity('ubah', 'pengiriman', $activityKeterangan);
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
            ->select('o.*, u.id_user AS id_user_pelanggan, u.nama, u.email')
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

        $kodeOrder = (string) $order['kode_order'];

        try {
            $this->applyKonfirmasiDiterima($db, $order);
        } catch (\Throwable $e) {
            log_message('error', '[PengirimanController::konfirmasiDiterimaPelanggan] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal mengkonfirmasi penerimaan pesanan.');
        }

        return redirect()->to(site_url('order/detail/' . $kodeOrder))
            ->with('success', 'Terima kasih! Pesanan dikonfirmasi diterima.');
    }

    /**
     * Admin konfirmasi diterima manual (failsafe) — efek status sama dengan pelanggan.
     * Hanya jalur ini yang menulis activity_logs.
     */
    public function konfirmasiDiterimaManualAdmin(): RedirectResponse
    {
        if ((string) session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'));
        }

        $idOrder = (int) $this->request->getPost('id_order');
        $db      = \Config\Database::connect();

        $order = $db->table('orders o')
            ->select('o.*, u.id_user AS id_user_pelanggan, u.nama, u.email')
            ->join('pelanggan pl', 'pl.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = pl.id_user')
            ->where('o.id_order', $idOrder)
            ->where('o.status', 'dikirim')
            ->get()->getRowArray();

        if ($order === null) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan atau tidak dapat dikonfirmasi.');
        }

        if (isMetodeAmbilSendiri($order)) {
            return redirect()->back()->with('error', 'Pesanan ambil sendiri dikonfirmasi melalui aksi pengambilan.');
        }

        $kodeOrder = (string) $order['kode_order'];

        try {
            $this->applyKonfirmasiDiterima($db, $order);

            helper('activity_log');
            logActivity(
                'ubah',
                'pengiriman',
                "Mengonfirmasi diterima secara manual untuk pesanan {$kodeOrder} (kurir)"
            );
        } catch (\Throwable $e) {
            log_message('error', '[PengirimanController::konfirmasiDiterimaManualAdmin] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal mengonfirmasi penerimaan pesanan.');
        }

        return redirect()->back()
            ->with('success', "Pesanan {$kodeOrder} dikonfirmasi diterima (manual admin).");
    }

    /**
     * Logic bersama: update status order + pengiriman + notifikasi pelanggan.
     * Perseorangan → selesai; kerja sama → menunggu_verifikasi_lunas.
     *
     * @param array<string, mixed> $order Harus berisi id_order, kode_order, id_user_pelanggan, nama, email, jenis_pelanggan
     */
    private function applyKonfirmasiDiterima(\CodeIgniter\Database\BaseConnection $db, array $order): void
    {
        $idOrder         = (int) $order['id_order'];
        $kodeOrder       = (string) $order['kode_order'];
        $idUserPelanggan = (int) $order['id_user_pelanggan'];
        $beforeShip      = isPelunasanSebelumKirim($order);
        $now             = date('Y-m-d H:i:s');

        $db->transStart();

        try {
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
                sendNotifWaForEmail(
                    $db,
                    (string) $order['email'],
                    buildNotifWaText(
                        "Pesanan Selesai-{$kodeOrder}",
                        "Pesanan {$kodeOrder} telah diterima. Terima kasih!",
                        site_url('order/detail/' . $kodeOrder)
                    )
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
                sendNotifWaForEmail(
                    $db,
                    (string) $order['email'],
                    buildNotifWaText(
                        "Pesanan Diterima-Nota Tagihan {$kodeOrder}",
                        "Pesanan {$kodeOrder} telah diterima. Silakan transfer pelunasan sesuai nota tagihan.",
                        site_url('order/detail/' . $kodeOrder)
                    )
                );
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }
    }
}
