<?php

namespace App\Controllers;

use App\Models\PaymentModel;
use CodeIgniter\HTTP\RedirectResponse;

class PaymentController extends BaseController
{
    protected $helpers = ['form', 'url', 'notification'];

    public function uploadDp(string $kodeOrder): RedirectResponse
    {
        if ((string) session()->get('role') !== 'pelanggan') {
            return redirect()->to(site_url('dashboard'));
        }

        $db          = \Config\Database::connect();
        $idPelanggan = (int) session()->get('id_pelanggan');

        $order = $db->table('orders o')
            ->select('o.*, u.nama, u.email')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('o.kode_order', $kodeOrder)
            ->where('o.id_pelanggan', $idPelanggan)
            ->get()
            ->getRowArray();

        if ($order === null) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        if (($order['status'] ?? '') !== 'menunggu_verifikasi_dp' || (int) ($order['require_dp'] ?? 0) !== 1) {
            return redirect()->to(site_url('order/detail/' . $kodeOrder))
                ->with('error', 'Upload DP tidak tersedia pada tahap ini.');
        }

        $idOrder = (int) $order['id_order'];

        $existingDp = $db->table('payments')
            ->where('id_order', $idOrder)
            ->where('jenis', 'dp')
            ->orderBy('tgl_upload', 'DESC')
            ->get()
            ->getRowArray();

        if ($existingDp !== null) {
            $existingStatus = (string) ($existingDp['status'] ?? '');
            if (in_array($existingStatus, ['menunggu', 'terverifikasi'], true)) {
                return redirect()->to(site_url('order/detail/' . $kodeOrder))
                    ->with('error', 'Bukti DP sudah diunggah.');
            }
            if ($existingStatus !== 'ditolak') {
                return redirect()->to(site_url('order/detail/' . $kodeOrder))
                    ->with('error', 'Tidak dapat mengunggah bukti DP.');
            }
        }

        $file = $this->request->getFile('bukti_dp');
        if ($file === null || !$file->isValid()) {
            return redirect()->to(site_url('order/detail/' . $kodeOrder))
                ->with('error', 'File bukti transfer wajib diunggah.');
        }

        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $ext     = strtolower($file->getExtension());
        if (!in_array($ext, $allowed, true)) {
            return redirect()->to(site_url('order/detail/' . $kodeOrder))
                ->with('error', 'Format file tidak valid. Gunakan JPG, PNG, atau PDF.');
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return redirect()->to(site_url('order/detail/' . $kodeOrder))
                ->with('error', 'Ukuran file maksimal 2MB.');
        }

        helper('notification');

        $totalHarga = (int) round((float) ($order['total_harga'] ?? 0));
        if ($totalHarga <= 0) {
            return redirect()->to(site_url('order/detail/' . $kodeOrder))
                ->with('error', 'Nominal belum ditentukan.');
        }

        $nominalDp = nominalDpFromTotal($totalHarga);

        try {
            $uploadDir = FCPATH . 'uploads/bukti_bayar/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                throw new \RuntimeException('Folder upload tidak tersedia.');
            }

            $newName = time() . '_' . $file->getClientName();
            $file->move($uploadDir, $newName);

            $paymentData = [
                'kode_payment' => generateKodePayment(),
                'id_order'     => $idOrder,
                'jenis'        => 'dp',
                'nominal'      => $nominalDp,
                'bukti_tf'     => $newName,
                'status'       => 'menunggu',
                'tgl_upload'   => date('Y-m-d H:i:s'),
            ];

            if ($existingDp !== null && ($existingDp['status'] ?? '') === 'ditolak') {
                unset($paymentData['kode_payment']);
                $db->table('payments')
                    ->where('id_payment', (int) $existingDp['id_payment'])
                    ->update([
                        'nominal'               => $nominalDp,
                        'bukti_tf'              => $newName,
                        'status'                => 'menunggu',
                        'catatan_tolak'         => null,
                        'id_verifikator'        => null,
                        'tgl_upload'            => date('Y-m-d H:i:s'),
                        'tgl_verifikasi'        => null,
                        'reminder_verif_2j_sent' => 0,
                        'reminder_verif_6j_sent' => 0,
                    ]);
            } else {
                $db->table('payments')->insert($paymentData);
            }

            $keuanganUsers = $db->table('users')->where('role', 'keuangan')->get()->getResultArray();
            $verifUrl      = site_url('verifikasi-dp');
            $namaPelanggan = (string) ($order['nama'] ?? 'Pelanggan');

            foreach ($keuanganUsers as $ku) {
                sendNotifEmail(
                    (string) $ku['email'],
                    "Bukti DP Baru-{$kodeOrder}",
                    '<p>Halo <strong>' . esc((string) $ku['nama']) . '</strong>,</p>'
                    . '<p>Pelanggan <strong>' . esc($namaPelanggan) . '</strong> mengunggah bukti DP untuk '
                    . '<strong>' . esc($kodeOrder) . '</strong>.</p>'
                    . '<p>Nominal DP: <strong>Rp ' . esc(number_format($nominalDp, 0, ',', '.')) . '</strong></p>'
                    . '<p><a href="' . esc($verifUrl) . '">Buka halaman verifikasi DP</a></p>'
                );

                sendNotifInApp(
                    (int) $ku['id_user'],
                    $idOrder,
                    'Bukti DP Baru',
                    "{$namaPelanggan} mengunggah bukti DP untuk {$kodeOrder}."
                );
            }
        } catch (\Throwable $e) {
            log_message('error', '[PaymentController::uploadDp] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->to(site_url('order/detail/' . $kodeOrder))
                ->with('error', 'Gagal mengunggah bukti DP. Silakan coba lagi.');
        }

        return redirect()->to(site_url('order/detail/' . $kodeOrder))
            ->with('success', 'Bukti DP berhasil diunggah. Menunggu verifikasi keuangan.');
    }

    public function uploadPelunasan(string $kodeOrder): RedirectResponse
    {
        if ((string) session()->get('role') !== 'pelanggan') {
            return redirect()->to(site_url('dashboard'));
        }

        $db          = \Config\Database::connect();
        $idPelanggan = (int) session()->get('id_pelanggan');

        $order = $db->table('orders o')
            ->select('o.*, u.nama, u.email, p.is_suspended')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('o.kode_order', $kodeOrder)
            ->where('o.id_pelanggan', $idPelanggan)
            ->get()
            ->getRowArray();

        if ($order === null) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        if (!canUploadPelunasan($order)) {
            return redirect()->to(site_url('order/detail/' . $kodeOrder))
                ->with('error', 'Upload pelunasan tidak tersedia pada tahap ini.');
        }

        $idOrder = (int) $order['id_order'];

        $existingLunas = $db->table('payments')
            ->where('id_order', $idOrder)
            ->where('jenis', 'pelunasan')
            ->orderBy('tgl_upload', 'DESC')
            ->get()
            ->getRowArray();

        if ($existingLunas !== null) {
            $existingStatus = (string) ($existingLunas['status'] ?? '');
            if (in_array($existingStatus, ['menunggu', 'terverifikasi'], true)) {
                return redirect()->to(site_url('order/detail/' . $kodeOrder))
                    ->with('error', 'Bukti pelunasan sudah diunggah.');
            }
            if ($existingStatus !== 'ditolak') {
                return redirect()->to(site_url('order/detail/' . $kodeOrder))
                    ->with('error', 'Tidak dapat mengunggah bukti pelunasan.');
            }
        }

        $file = $this->request->getFile('bukti_tf');
        if ($file === null || !$file->isValid()) {
            return redirect()->to(site_url('order/detail/' . $kodeOrder))
                ->with('error', 'File bukti transfer wajib diunggah.');
        }

        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $ext     = strtolower($file->getExtension());
        if (!in_array($ext, $allowed, true)) {
            return redirect()->to(site_url('order/detail/' . $kodeOrder))
                ->with('error', 'Format file tidak valid. Gunakan JPG, PNG, atau PDF.');
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return redirect()->to(site_url('order/detail/' . $kodeOrder))
                ->with('error', 'Ukuran file maksimal 2MB.');
        }

        $totalHarga = (int) round((float) ($order['total_harga'] ?? 0));
        if ($totalHarga <= 0) {
            return redirect()->to(site_url('order/detail/' . $kodeOrder))
                ->with('error', 'Nominal belum ditentukan.');
        }

        $requireDp     = (int) ($order['require_dp'] ?? 0);
        $nominalLunas  = nominalPelunasanFromOrder($totalHarga, $requireDp);
        $namaPelanggan = (string) ($order['nama'] ?? 'Pelanggan');

        try {
            $uploadDir = FCPATH . 'uploads/bukti_bayar/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                throw new \RuntimeException('Folder upload tidak tersedia.');
            }

            $newName = time() . '_' . $file->getClientName();
            $file->move($uploadDir, $newName);

            $paymentData = [
                'kode_payment' => generateKodePayment(),
                'id_order'     => $idOrder,
                'jenis'        => 'pelunasan',
                'nominal'      => $nominalLunas,
                'bukti_tf'     => $newName,
                'status'       => 'menunggu',
                'tgl_upload'   => date('Y-m-d H:i:s'),
            ];

            if ($existingLunas !== null && ($existingLunas['status'] ?? '') === 'ditolak') {
                $db->table('payments')
                    ->where('id_payment', (int) $existingLunas['id_payment'])
                    ->update([
                        'nominal'                => $nominalLunas,
                        'bukti_tf'               => $newName,
                        'status'                 => 'menunggu',
                        'catatan_tolak'          => null,
                        'id_verifikator'         => null,
                        'tgl_upload'             => date('Y-m-d H:i:s'),
                        'tgl_verifikasi'         => null,
                        'reminder_verif_2j_sent' => 0,
                        'reminder_verif_6j_sent' => 0,
                    ]);
            } else {
                $db->table('payments')->insert($paymentData);
            }

            $db->table('orders')->where('id_order', $idOrder)->update([
                'status' => 'menunggu_verifikasi_lunas',
            ]);

            $keuanganUsers = $db->table('users')->where('role', 'keuangan')->get()->getResultArray();
            $verifUrl      = site_url('verifikasi-pelunasan');

            foreach ($keuanganUsers as $ku) {
                sendNotifEmail(
                    (string) $ku['email'],
                    "Bukti Pelunasan Baru-{$kodeOrder}",
                    '<p>Halo <strong>' . esc((string) $ku['nama']) . '</strong>,</p>'
                    . '<p>Pelanggan <strong>' . esc($namaPelanggan) . '</strong> mengunggah bukti pelunasan untuk '
                    . '<strong>' . esc($kodeOrder) . '</strong>.</p>'
                    . '<p>Nominal: <strong>Rp ' . esc(number_format($nominalLunas, 0, ',', '.')) . '</strong></p>'
                    . '<p><a href="' . esc($verifUrl) . '">Buka halaman verifikasi pelunasan</a></p>'
                );

                sendNotifInApp(
                    (int) $ku['id_user'],
                    $idOrder,
                    'Bukti Pelunasan Baru',
                    "{$namaPelanggan} mengunggah bukti pelunasan untuk {$kodeOrder}."
                );
            }
        } catch (\Throwable $e) {
            log_message('error', '[PaymentController::uploadPelunasan] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->to(site_url('order/detail/' . $kodeOrder))
                ->with('error', 'Gagal mengunggah bukti pelunasan. Silakan coba lagi.');
        }

        return redirect()->to(site_url('order/detail/' . $kodeOrder))
            ->with('success', 'Bukti pelunasan berhasil diunggah. Menunggu verifikasi keuangan.');
    }

    public function verifikasiDp()
    {
        if ((string) session()->get('role') !== 'keuangan') {
            return redirect()->to(site_url('dashboard'));
        }

        $payments = model(PaymentModel::class)->getDpMenunggu();

        return view('payment/verifikasi_dp', [
            'title'      => 'Verifikasi DP',
            'page_title' => 'Verifikasi Pembayaran DP',
            'payments'   => $payments,
        ]);
    }

    public function accDp(int $idPayment): RedirectResponse
    {
        if ((string) session()->get('role') !== 'keuangan') {
            return redirect()->to(site_url('dashboard'));
        }

        $db      = \Config\Database::connect();
        $payment = $db->table('payments')
            ->where('id_payment', $idPayment)
            ->where('jenis', 'dp')
            ->where('status', 'menunggu')
            ->get()
            ->getRowArray();

        if ($payment === null) {
            return redirect()->back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $order = $db->table('orders o')
            ->select('o.*, u.id_user AS id_user_pelanggan, u.nama, u.email')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('o.id_order', (int) $payment['id_order'])
            ->get()
            ->getRowArray();

        if ($order === null) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        $idVerifikator = (int) session()->get('id_user');
        $kodeOrder     = (string) $order['kode_order'];

        try {
            $db->transStart();

            $db->table('payments')->where('id_payment', $idPayment)->update([
                'status'         => 'terverifikasi',
                'id_verifikator' => $idVerifikator,
                'tgl_verifikasi' => date('Y-m-d H:i:s'),
            ]);

            $db->table('orders')->where('id_order', (int) $order['id_order'])->update([
                'status' => 'terverifikasi',
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Gagal memverifikasi DP.');
            }

            sendNotifEmail(
                (string) $order['email'],
                "DP Terverifikasi-{$kodeOrder}",
                '<p>Halo <strong>' . esc((string) $order['nama']) . '</strong>,</p>'
                . "<p>DP Anda untuk pesanan <strong>{$kodeOrder}</strong> sudah diverifikasi.</p>"
                . '<p>Tim produksi akan segera memproses desain Anda.</p>'
            );

            sendNotifInApp(
                (int) $order['id_user_pelanggan'],
                (int) $order['id_order'],
                'DP Terverifikasi',
                "Pembayaran DP pesanan {$kodeOrder} telah diverifikasi."
            );
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[PaymentController::accDp] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal memverifikasi DP.');
        }

        return redirect()->back()->with('success', 'DP berhasil diverifikasi.');
    }

    public function tolakDp(int $idPayment): RedirectResponse
    {
        if ((string) session()->get('role') !== 'keuangan') {
            return redirect()->to(site_url('dashboard'));
        }

        $catatanTolak = trim((string) $this->request->getPost('catatan_tolak'));
        if ($catatanTolak === '') {
            return redirect()->back()->with('error', 'Alasan penolakan wajib diisi.');
        }

        $db      = \Config\Database::connect();
        $payment = $db->table('payments')
            ->where('id_payment', $idPayment)
            ->where('jenis', 'dp')
            ->where('status', 'menunggu')
            ->get()
            ->getRowArray();

        if ($payment === null) {
            return redirect()->back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $order = $db->table('orders o')
            ->select('o.*, u.id_user AS id_user_pelanggan, u.nama, u.email')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('o.id_order', (int) $payment['id_order'])
            ->get()
            ->getRowArray();

        if ($order === null) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        $kodeOrder = (string) $order['kode_order'];

        try {
            $db->table('payments')->where('id_payment', $idPayment)->update([
                'status'         => 'ditolak',
                'catatan_tolak'  => $catatanTolak,
                'id_verifikator' => (int) session()->get('id_user'),
                'tgl_verifikasi' => date('Y-m-d H:i:s'),
            ]);

            $db->table('orders')->where('id_order', (int) $order['id_order'])->update([
                'batas_upload_dp'  => date('Y-m-d H:i:s', strtotime('+24 hours')),
                'reminder_dp_sent' => 0,
            ]);

            sendNotifEmail(
                (string) $order['email'],
                "Bukti DP Ditolak-{$kodeOrder}",
                '<p>Halo <strong>' . esc((string) $order['nama']) . '</strong>,</p>'
                . "<p>Bukti DP untuk pesanan <strong>{$kodeOrder}</strong> <strong>ditolak</strong>.</p>"
                . '<p><strong>Alasan:</strong> ' . esc($catatanTolak) . '</p>'
                . '<p>Anda punya waktu <strong>24 jam</strong> untuk mengunggah ulang bukti transfer yang benar.</p>'
                . '<p>Batas waktu baru: <strong>'
                . date('d M Y', strtotime('+24 hours')) . ' pukul '
                . date('H:i', strtotime('+24 hours')) . ' WIB</strong></p>'
                . '<p>Login ke SIMENAK dan buka detail pesanan untuk upload ulang.</p>'
            );

            sendNotifInApp(
                (int) $order['id_user_pelanggan'],
                (int) $order['id_order'],
                'Bukti DP Ditolak',
                "Bukti DP {$kodeOrder} ditolak: {$catatanTolak}. Silakan upload ulang."
            );
        } catch (\Throwable $e) {
            log_message('error', '[PaymentController::tolakDp] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal menolak bukti DP.');
        }

        return redirect()->back()->with('error', 'Bukti DP ditolak.');
    }

    public function verifikasiPelunasan()
    {
        if ((string) session()->get('role') !== 'keuangan') {
            return redirect()->to(site_url('dashboard'));
        }

        $payments = model(PaymentModel::class)->getPelunasanMenunggu();

        return view('payment/verifikasi_pelunasan', [
            'title'      => 'Verifikasi Pelunasan',
            'page_title' => 'Verifikasi Pelunasan',
            'payments'   => $payments,
        ]);
    }

    public function accPelunasan(int $idPayment): RedirectResponse
    {
        if ((string) session()->get('role') !== 'keuangan') {
            return redirect()->to(site_url('dashboard'));
        }

        $db      = \Config\Database::connect();
        $payment = $db->table('payments')
            ->where('id_payment', $idPayment)
            ->where('jenis', 'pelunasan')
            ->where('status', 'menunggu')
            ->get()
            ->getRowArray();

        if ($payment === null) {
            return redirect()->back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $order = $db->table('orders o')
            ->select('o.*, u.id_user AS id_user_pelanggan, u.nama, u.email')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('o.id_order', (int) $payment['id_order'])
            ->get()
            ->getRowArray();

        if ($order === null) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        $idVerifikator   = (int) session()->get('id_user');
        $kodeOrder       = (string) $order['kode_order'];
        $idUserPelanggan = (int) $order['id_user_pelanggan'];
        $idOrder         = (int) $order['id_order'];
        $newOrderStatus  = accPelunasanTargetStatus($order);
        $selesaiLangsung = $newOrderStatus === 'selesai';

        try {
            $db->transStart();

            $db->table('payments')->where('id_payment', $idPayment)->update([
                'status'         => 'terverifikasi',
                'id_verifikator' => $idVerifikator,
                'tgl_verifikasi' => date('Y-m-d H:i:s'),
            ]);

            $db->table('orders')->where('id_order', $idOrder)->update([
                'status' => $newOrderStatus,
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Gagal memverifikasi pelunasan.');
            }

            if ($selesaiLangsung) {
                sendNotifEmail(
                    (string) $order['email'],
                    "[No-Reply] Pelunasan Dikonfirmasi-{$kodeOrder}",
                    '<p>Halo <strong>' . esc((string) $order['nama']) . '</strong>,</p>'
                    . "<p>Pembayaran pelunasan pesanan <strong>{$kodeOrder}</strong> telah dikonfirmasi.</p>"
                    . '<p>Pesanan telah <strong>selesai</strong>. Terima kasih telah mempercayakan kebutuhan cetak kepada Z\'Plack!</p>'
                );
                sendNotifInApp($idUserPelanggan, $idOrder, 'Pesanan Selesai', "Pelunasan {$kodeOrder} dikonfirmasi. Pesanan selesai!");
            } else {
                sendNotifEmail(
                    (string) $order['email'],
                    "[No-Reply] Pelunasan Terverifikasi-{$kodeOrder}",
                    '<p>Halo <strong>' . esc((string) $order['nama']) . '</strong>,</p>'
                    . "<p>Pembayaran pelunasan pesanan <strong>{$kodeOrder}</strong> telah terverifikasi.</p>"
                    . '<p>'
                    . (isMetodeAmbilSendiri($order)
                        ? 'Pesanan siap diambil di toko kami. Tim kami akan menunggu kedatangan Anda.'
                        : 'Tim kami akan segera memproses pengiriman pesanan Anda.')
                    . '</p>'
                );
                sendNotifInApp(
                    $idUserPelanggan,
                    $idOrder,
                    'Pelunasan Terverifikasi',
                    isMetodeAmbilSendiri($order)
                        ? "Pelunasan {$kodeOrder} terverifikasi. Silakan ambil pesanan di toko."
                        : "Pelunasan {$kodeOrder} terverifikasi. Pesanan akan segera dikirim."
                );

                $admins = $db->table('users')->where('role', 'admin')->get()->getResultArray();
                foreach ($admins as $adm) {
                    sendNotifInApp(
                        (int) $adm['id_user'],
                        $idOrder,
                        'Siap Diproses Pengiriman',
                        "Pelunasan {$kodeOrder} terverifikasi. Silakan set pengiriman."
                    );
                }
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[PaymentController::accPelunasan] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal memverifikasi pelunasan.');
        }

        return redirect()->back()->with('success', 'Pelunasan berhasil diverifikasi.');
    }

    public function tolakPelunasan(int $idPayment): RedirectResponse
    {
        if ((string) session()->get('role') !== 'keuangan') {
            return redirect()->to(site_url('dashboard'));
        }

        $catatanTolak = trim((string) $this->request->getPost('catatan_tolak'));
        if ($catatanTolak === '') {
            return redirect()->back()->with('error', 'Alasan penolakan wajib diisi.');
        }

        $db      = \Config\Database::connect();
        $payment = $db->table('payments')
            ->where('id_payment', $idPayment)
            ->where('jenis', 'pelunasan')
            ->where('status', 'menunggu')
            ->get()
            ->getRowArray();

        if ($payment === null) {
            return redirect()->back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $order = $db->table('orders o')
            ->select('o.*, u.id_user AS id_user_pelanggan, u.nama, u.email')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('o.id_order', (int) $payment['id_order'])
            ->get()
            ->getRowArray();

        if ($order === null) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        $kodeOrder = (string) $order['kode_order'];

        try {
            $db->transStart();

            $db->table('payments')->where('id_payment', $idPayment)->update([
                'status'         => 'ditolak',
                'catatan_tolak'  => $catatanTolak,
                'id_verifikator' => (int) session()->get('id_user'),
                'tgl_verifikasi' => date('Y-m-d H:i:s'),
            ]);

            $db->table('orders')->where('id_order', (int) $order['id_order'])->update([
                'status' => revertStatusAfterPelunasanDitolak($order),
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Gagal menolak pelunasan.');
            }

            sendNotifEmail(
                (string) $order['email'],
                "Bukti Pelunasan Ditolak-{$kodeOrder}",
                '<p>Halo <strong>' . esc((string) $order['nama']) . '</strong>,</p>'
                . "<p>Bukti pelunasan untuk pesanan <strong>{$kodeOrder}</strong> ditolak.</p>"
                . '<p><strong>Alasan:</strong> ' . esc($catatanTolak) . '</p>'
                . '<p>Silakan upload ulang bukti transfer yang valid.</p>'
            );

            sendNotifInApp(
                (int) $order['id_user_pelanggan'],
                (int) $order['id_order'],
                'Bukti Pelunasan Ditolak',
                "Bukti pelunasan {$kodeOrder} ditolak: {$catatanTolak}. Silakan upload ulang."
            );
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[PaymentController::tolakPelunasan] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal menolak bukti pelunasan.');
        }

        return redirect()->back()->with('error', 'Bukti pelunasan ditolak.');
    }

    public function notaTagihan(string $kodeOrder): RedirectResponse|string
    {
        $db   = \Config\Database::connect();
        $role = (string) session()->get('role');

        $order = $db->table('orders o')
            ->select('o.*, u.nama, u.email, p.no_telp, p.nama_perusahaan, p.is_verified, k.nama_produk, k.satuan')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->join('katalog k', 'k.id_katalog = o.id_katalog', 'left')
            ->where('o.kode_order', $kodeOrder)
            ->get()
            ->getRowArray();

        if ($order === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Pesanan tidak ditemukan.');
        }

        if (!canViewNotaTagihan($order)) {
            return redirect()->to(site_url('order/detail/' . $kodeOrder))
                ->with('error', 'Nota tagihan belum tersedia untuk pesanan ini.');
        }

        if ($role === 'pelanggan') {
            $idPelanggan = (int) session()->get('id_pelanggan');
            if ((int) ($order['id_pelanggan'] ?? 0) !== $idPelanggan) {
                return redirect()->to(site_url('dashboard'))->with('error', 'Akses ditolak.');
            }
        } elseif (!in_array($role, ['admin', 'keuangan', 'owner'], true)) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Akses ditolak.');
        }

        $requireDp        = (int) ($order['require_dp'] ?? 0);
        $totalHarga       = (int) round((float) ($order['total_harga'] ?? 0));
        $nominalPelunasan = nominalPelunasanFromOrder($totalHarga, $requireDp);
        $isPerusahaan     = ($order['jenis_pelanggan'] ?? '') === 'perusahaan'
            && (int) ($order['is_verified'] ?? 0) === 1;
        $sebelumKirim     = isPelunasanSebelumKirim($order);

        return view('payment/nota_tagihan', [
            'title'            => 'Nota Tagihan',
            'order'            => $order,
            'kodeNota'         => generateKodeNota($kodeOrder),
            'nominalPelunasan' => $nominalPelunasan,
            'totalHarga'       => $totalHarga,
            'isPerusahaan'     => $isPerusahaan,
            'sebelumKirim'     => $sebelumKirim,
            'batasBayar'       => date('d M Y', strtotime('+3 weekdays')),
        ]);
    }

    public function buktiPembayaranPelunasan(string $kodeOrder): RedirectResponse|string
    {
        $db   = \Config\Database::connect();
        $role = (string) session()->get('role');

        $order = $db->table('orders o')
            ->select('o.*, u.nama, u.email, p.no_telp, p.nama_perusahaan, p.is_verified, k.nama_produk, k.satuan')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->join('katalog k', 'k.id_katalog = o.id_katalog', 'left')
            ->where('o.kode_order', $kodeOrder)
            ->get()
            ->getRowArray();

        if ($order === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Pesanan tidak ditemukan.');
        }

        $payment = $db->table('payments py')
            ->select('py.*, uv.nama AS nama_verifikator')
            ->join('users uv', 'uv.id_user = py.id_verifikator', 'left')
            ->where('py.id_order', (int) $order['id_order'])
            ->where('py.jenis', 'pelunasan')
            ->where('py.status', 'terverifikasi')
            ->orderBy('py.tgl_verifikasi', 'DESC')
            ->get()
            ->getRowArray();

        if (!canViewBuktiPembayaranPelunasan($payment)) {
            return redirect()->to(site_url('order/detail/' . $kodeOrder))
                ->with('error', 'Bukti pembayaran pelunasan belum tersedia.');
        }

        if ($role === 'pelanggan') {
            $idPelanggan = (int) session()->get('id_pelanggan');
            if ((int) ($order['id_pelanggan'] ?? 0) !== $idPelanggan) {
                return redirect()->to(site_url('dashboard'))->with('error', 'Akses ditolak.');
            }
        } elseif (!in_array($role, ['admin', 'keuangan', 'owner'], true)) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Akses ditolak.');
        }

        $isPerusahaan = ($order['jenis_pelanggan'] ?? '') === 'perusahaan'
            && (int) ($order['is_verified'] ?? 0) === 1;

        return view('payment/bukti_pembayaran_pelunasan', [
            'title'      => 'Bukti Pembayaran Pelunasan',
            'order'      => $order,
            'payment'    => $payment,
            'isPerusahaan' => $isPerusahaan,
        ]);
    }

    // GET /riwayat-pembayaran (role: keuangan, owner)
    public function riwayat(): RedirectResponse|string
    {
        $role = (string) session()->get('role');
        if (!in_array($role, ['keuangan', 'owner'], true)) {
            return redirect()->to(site_url('dashboard'));
        }

        $readOnly = $role === 'owner';
        $payments = model(PaymentModel::class)->getRiwayatSemua();

        return view('payment/riwayat', [
            'title'      => 'Riwayat Pembayaran',
            'page_title' => $readOnly ? 'Riwayat Pembayaran' : 'Riwayat Semua Pembayaran',
            'payments'   => $payments,
            'readOnly'   => $readOnly,
            'viewerRole' => $role,
        ]);
    }
}
