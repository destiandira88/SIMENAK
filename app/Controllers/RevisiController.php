<?php

namespace App\Controllers;

use App\Models\RevisiDesainModel;
use CodeIgniter\HTTP\RedirectResponse;

class RevisiController extends BaseController
{
    protected $helpers = ['form', 'url', 'notification', 'mockup'];

    private const STATUSES_ANTRIAN = [
        'terverifikasi',
        'proses_desain',
        'proses_revisi',
    ];

    private const STATUSES_MANAJEMEN = [
        'terverifikasi',
        'proses_desain',
        'proses_revisi',
        'proses_cetak',
        'finishing',
    ];

    private const STATUSES_UPLOAD_DETAIL = [
        'terverifikasi',
        'proses_desain',
        'proses_revisi',
    ];

    public function antrianDesain(): RedirectResponse|string
    {
        if ((string) session()->get('role') !== 'produksi') {
            return redirect()->to(site_url('dashboard'));
        }

        return $this->renderAntrian(false);
    }

    public function manajemenDesain(): RedirectResponse|string
    {
        $role = (string) session()->get('role');
        if (!in_array($role, ['produksi', 'owner'], true)) {
            return redirect()->to(site_url('dashboard'));
        }

        return $this->renderAntrian(true, $role === 'owner');
    }

    public function detail(int $idOrder): RedirectResponse|string
    {
        $role = (string) session()->get('role');
        if (!in_array($role, ['produksi', 'owner'], true)) {
            return redirect()->to(site_url('dashboard'));
        }

        $readOnly = $role === 'owner';
        $backUrl  = $readOnly ? 'manajemen-desain' : 'dashboard';

        $context = $this->loadWorkspaceContext($idOrder);
        if ($context === null) {
            return redirect()->to(site_url($backUrl))
                ->with('error', 'Pesanan tidak ditemukan.');
        }

        $status = (string) ($context['order']['status'] ?? '');
        if (!in_array($status, self::STATUSES_MANAJEMEN, true)) {
            return redirect()->to(site_url($backUrl))
                ->with('error', 'Pesanan tidak dalam antrian produksi.');
        }

        $context['readOnly'] = $readOnly;
        if ($readOnly) {
            $context['canUpload']   = false;
            $context['page_title']  = 'Detail Produksi';
            $context['title']       = 'Detail Produksi-' . ($context['order']['kode_order'] ?? '');
        }

        return view('revisi/workspace', $context);
    }

    /**
     * Daftar pesanan tahap cetak (Manajemen Produksi).
     */
    public function manajemenProduksi(): RedirectResponse|string
    {
        $role = (string) session()->get('role');
        if (! in_array($role, ['produksi', 'owner'], true)) {
            return redirect()->to(site_url('dashboard'));
        }

        $orders = $this->fetchOrdersQueue(['proses_cetak']);

        return view('revisi/manajemen_produksi', [
            'title'      => 'Manajemen Produksi',
            'page_title' => 'Manajemen Produksi',
            'orders'     => $orders,
            'role'       => $role,
            'readOnly'   => $role === 'owner',
        ]);
    }

    /**
     * Halaman monitoring cetak → finishing (1 pesanan).
     */
    public function monitoringCetak(string $kodeOrder): RedirectResponse|string
    {
        $role = (string) session()->get('role');
        if (! in_array($role, ['produksi', 'owner'], true)) {
            return redirect()->to(site_url('dashboard'));
        }

        helper('deadline');

        $order = $this->fetchOrderByKode($kodeOrder);
        if ($order === null) {
            return redirect()->to(site_url($role === 'owner' ? 'manajemen-produksi' : 'manajemen-produksi'))
                ->with('error', 'Pesanan tidak ditemukan.');
        }

        $status = (string) ($order['status'] ?? '');
        $allowed = ['proses_cetak', 'finishing', 'siap_kirim', 'siap_diambil', 'dikirim', 'menunggu_verifikasi_lunas', 'pelunasan_terverifikasi', 'selesai'];
        if (! in_array($status, $allowed, true)) {
            return redirect()->to(site_url('manajemen-produksi'))
                ->with('error', 'Pesanan belum masuk tahap cetak.');
        }

        // Jangan backfill tanggal saat buka halaman — hanya dari ACC (mulai cetak) / klik Finishing.
        $tglMulaiCetak = trim((string) ($order['tgl_mulai_cetak'] ?? ''));
        $tglFinishing  = trim((string) ($order['tgl_finishing'] ?? ''));
        $durasiHari    = null;
        if ($tglMulaiCetak !== '' && $tglFinishing !== '') {
            try {
                $start = new \DateTimeImmutable($tglMulaiCetak);
                $end   = new \DateTimeImmutable($tglFinishing);
                $durasiHari = (int) $start->diff($end)->days;
            } catch (\Throwable) {
                $durasiHari = null;
            }
        }

        $stepCetakDone      = $tglMulaiCetak !== '' || in_array($status, ['proses_cetak', 'finishing', 'siap_kirim', 'siap_diambil', 'dikirim', 'menunggu_verifikasi_lunas', 'pelunasan_terverifikasi', 'selesai'], true);
        $stepFinishingDone  = $tglFinishing !== '' || in_array($status, ['finishing', 'siap_kirim', 'siap_diambil', 'dikirim', 'menunggu_verifikasi_lunas', 'pelunasan_terverifikasi', 'selesai'], true);
        $canKlikFinishing   = $role === 'produksi' && $status === 'proses_cetak';

        return view('revisi/monitoring_cetak', [
            'title'             => 'Monitoring Produksi-' . ($order['kode_order'] ?? ''),
            'page_title'        => 'Monitoring Produksi',
            'order'             => $order,
            'role'              => $role,
            'tglMulaiCetak'     => $tglMulaiCetak,
            'tglFinishing'      => $tglFinishing,
            'durasiHari'        => $durasiHari,
            'stepCetakDone'     => $stepCetakDone,
            'stepFinishingDone' => $stepFinishingDone,
            'canKlikFinishing'  => $canKlikFinishing,
        ]);
    }

    public function upload(int $idOrder): RedirectResponse
    {
        if ((string) session()->get('role') !== 'produksi') {
            return redirect()->to(site_url('dashboard'));
        }

        $order = $this->fetchOrderById($idOrder);
        if ($order === null) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Pesanan tidak ditemukan.');
        }

        if (!in_array((string) ($order['status'] ?? ''), self::STATUSES_UPLOAD_DETAIL, true)) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Upload draft tidak tersedia pada status ini.');
        }

        $revisiModel = model(RevisiDesainModel::class);
        $lastRevis   = $revisiModel->getLatestByOrder($idOrder);
        if (!canProduksiUploadDraft($order, $lastRevis)) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Upload draft tidak tersedia. Menunggu review atau ACC pelanggan.');
        }

        $file = $this->request->getFile('file_draft');
        if ($file === null || !$file->isValid()) {
            return redirect()->back()->with('error', 'File draft wajib diunggah.');
        }

        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            return redirect()->back()->with('error', 'Format file harus JPG atau PNG.');
        }

        if ($file->getSize() > 1024 * 1024) {
            return redirect()->back()->with('error', 'Ukuran file maksimal 1MB.');
        }

        $uploadDir = FCPATH . 'uploads/draft_desain/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newName = time() . '_' . $file->getClientName();
        try {
            $file->move($uploadDir, $newName);
        } catch (\Throwable $e) {
            log_message('error', '[RevisiController::upload] move file: {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal menyimpan file draft.');
        }

        $revisiModel = model(RevisiDesainModel::class);
        $versi       = $revisiModel->getNextVersi($idOrder);
        $kodeOrder   = (string) ($order['kode_order'] ?? '');
        $catatanProd = trim((string) $this->request->getPost('catatan_prod'));
        $adjustArr   = normalizeMockupAdjust($this->request->getPost('mockup_adjust'));

        $pendingMap = [];
        $uploaded   = $this->request->getFiles();
        $layerFiles = $uploaded['layer_file'] ?? null;
        if (is_array($layerFiles)) {
            foreach ($layerFiles as $pendingId => $file) {
                if (! is_object($file) || ! method_exists($file, 'isValid') || ! $file->isValid()) {
                    continue;
                }
                $ext = strtolower($file->getExtension());
                if (! in_array($ext, ['jpg', 'jpeg', 'png'], true) || $file->getSize() > 1024 * 1024) {
                    continue;
                }
                $pendingKey = (string) $pendingId;
                if ($pendingKey === '' || ! str_starts_with($pendingKey, 'pending_')) {
                    continue;
                }
                $layerName = time() . '_' . $file->getClientName();
                try {
                    $file->move($uploadDir, $layerName);
                    $pendingMap[$pendingKey] = $layerName;
                } catch (\Throwable $e) {
                    log_message('error', '[upload] layer: {msg}', ['msg' => $e->getMessage()]);
                }
            }
        }
        if ($pendingMap !== []) {
            $adjustArr = remapMockupLayerFiles($adjustArr, $pendingMap);
        }
        $mockupJson = encodeMockupAdjust($adjustArr);
        if ($mockupJson === null && $this->request->getPost('mockup_adjust') !== null) {
            $mockupJson = '{}';
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $revisiModel->insert([
                'id_order'       => $idOrder,
                'id_produksi'    => (int) session()->get('id_user'),
                'versi'          => $versi,
                'kode_revisi'    => 'REV-' . $kodeOrder . '-V' . $versi,
                'file_draft'     => $newName,
                'mockup_adjust'  => $mockupJson,
                'catatan_prod'   => $catatanProd !== '' ? $catatanProd : null,
                'status'         => 'uploaded',
                'created_at'     => date('Y-m-d H:i:s'),
            ]);

            $db->table('orders')->where('id_order', $idOrder)->update([
                'status' => 'proses_desain',
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[RevisiController::upload] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal menyimpan draft desain.');
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal menyimpan draft desain.');
        }

        helper('activity_log');
        logActivity(
            'ubah',
            'produksi',
            "Mengunggah draft desain v{$versi} pesanan {$kodeOrder}, status menjadi proses_desain"
        );

        $idUserPelanggan = (int) ($order['id_user_pelanggan'] ?? 0);
        $emailPelanggan  = (string) ($order['email_pelanggan'] ?? '');
        $namaPelanggan   = (string) ($order['nama_pelanggan'] ?? 'Pelanggan');

        $judulDraft = 'Draft Desain Tersedia';
        $pesanDraft = "Draft v{$versi} pesanan {$kodeOrder} siap Anda review.";
        $detailUrlDraft = pelangganOrderDetailUrl($kodeOrder, $judulDraft);

        if ($idUserPelanggan > 0) {
            sendNotifInApp(
                $idUserPelanggan,
                $idOrder,
                $judulDraft,
                $pesanDraft
            );
        }

        if ($emailPelanggan !== '') {
            $tglUploadDraft = date('Y-m-d H:i:s');
            $deadlineRaw    = (string) ($order['deadline_produksi'] ?? $order['deadline_diajukan'] ?? '');

            sendNotifEmail(
                $emailPelanggan,
                "Draft Desain v{$versi} Tersedia-{$kodeOrder}",
                renderNotifEmail('draft_siap', [
                    'pesanHtml' => '<p style="margin:0 0 12px;">Halo <strong>' . esc($namaPelanggan) . '</strong>,</p>'
                        . '<p style="margin:0 0 8px;">Draft desain <strong>versi ' . (int) $versi . '</strong> untuk pesanan '
                        . emailHighlightKodeOrder($kodeOrder)
                        . ' sudah siap direview.</p>'
                        . '<p style="margin:0;color:#64748B;font-size:13px;">Silakan tinjau dan ACC atau ajukan revisi dari halaman pesanan.</p>',
                    'ctaUrl'         => $detailUrlDraft,
                    'ctaLabel'       => 'Review Draft Sekarang',
                    'kodeOrder'      => $kodeOrder,
                    'namaProduk'     => (string) ($order['nama_produk'] ?? 'Produk Custom'),
                    'gambarUrl'      => resolveKatalogGambarEmailUrl($order['gambar_katalog'] ?? null),
                    'produkSubteks'  => buildEmailProdukSubteks($order),
                    'tglOrderLabel'  => formatEmailDatetime($order['created_at'] ?? null),
                    'deadlineLabel'  => formatEmailDate($deadlineRaw !== '' ? $deadlineRaw : null),
                    'versi'          => $versi,
                    'draftUrl'       => resolveDraftGambarEmailUrl($newName),
                    'tglUploadLabel' => formatEmailDatetime($tglUploadDraft),
                    'sisaKuota'      => (int) ($order['sisa_kuota'] ?? 0),
                    'kuotaRevisi'    => (int) ($order['kuota_revisi'] ?? 0),
                ])
            );
            sendNotifWaForEmail(
                \Config\Database::connect(),
                $emailPelanggan,
                buildNotifWaText(
                    "Draft Desain v{$versi} Tersedia-{$kodeOrder}",
                    $pesanDraft,
                    $detailUrlDraft
                )
            );
        }

        return redirect()->to(site_url('manajemen-desain/' . $idOrder))
            ->with('success', "Draft v{$versi} berhasil diunggah.");
    }

    /**
     * Simpan penyesuaian mockup (zoom/geser) untuk draft yang sudah ada — tanpa unggah ulang.
     */
    public function saveMockupAdjust(int $idOrder): RedirectResponse
    {
        if ((string) session()->get('role') !== 'produksi') {
            return redirect()->to(site_url('dashboard'));
        }

        $order = $this->fetchOrderById($idOrder);
        if ($order === null) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Pesanan tidak ditemukan.');
        }

        if (! in_array((string) ($order['status'] ?? ''), self::STATUSES_UPLOAD_DETAIL, true)) {
            return redirect()->back()
                ->with('error', 'Penyesuaian mockup tidak tersedia pada status ini.');
        }

        $revisiModel = model(RevisiDesainModel::class);
        $idRevisi    = (int) $this->request->getPost('id_revisi');
        $revisi      = null;

        if ($idRevisi > 0) {
            $revisi = $revisiModel->where('id_revisi', $idRevisi)
                ->where('id_order', $idOrder)
                ->first();
        }

        if ($revisi === null) {
            $revisi = $revisiModel->getLatestByOrder($idOrder);
        }

        if ($revisi === null || trim((string) ($revisi['file_draft'] ?? '')) === '') {
            return redirect()->back()
                ->with('error', 'Belum ada draft yang bisa disesuaikan.');
        }

        $adjustArr = normalizeMockupAdjust($this->request->getPost('mockup_adjust'));

        // Upload gambar layer tambahan (layer_file[pending_xxx])
        $pendingMap = [];
        $uploaded    = $this->request->getFiles();
        $layerFiles  = $uploaded['layer_file'] ?? null;
        if (is_array($layerFiles)) {
            $uploadDir = FCPATH . 'uploads/draft_desain/';
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            foreach ($layerFiles as $pendingId => $file) {
                if (! is_object($file) || ! method_exists($file, 'isValid') || ! $file->isValid()) {
                    continue;
                }
                $ext = strtolower($file->getExtension());
                if (! in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
                    continue;
                }
                if ($file->getSize() > 1024 * 1024) {
                    continue;
                }
                $pendingKey = (string) $pendingId;
                if ($pendingKey === '' || ! str_starts_with($pendingKey, 'pending_')) {
                    continue;
                }
                $newName = time() . '_' . $file->getClientName();
                try {
                    $file->move($uploadDir, $newName);
                    $pendingMap[$pendingKey] = $newName;
                } catch (\Throwable $e) {
                    log_message('error', '[saveMockupAdjust] layer upload: {msg}', ['msg' => $e->getMessage()]);
                }
            }
        }

        if ($pendingMap !== []) {
            $adjustArr = remapMockupLayerFiles($adjustArr, $pendingMap);
        }
        $mockupJson = encodeMockupAdjust($adjustArr);
        if ($mockupJson === null) {
            $mockupJson = '{}';
        }

        try {
            $revisiModel->update((int) $revisi['id_revisi'], [
                'mockup_adjust' => $mockupJson,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[RevisiController::saveMockupAdjust] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal menyimpan penyesuaian mockup.');
        }

        $versi     = (int) ($revisi['versi'] ?? 0);
        $kodeOrder = (string) ($order['kode_order'] ?? '');

        helper('activity_log');
        logActivity(
            'ubah',
            'produksi',
            "Menyesuaikan preview mockup draft v{$versi} pesanan {$kodeOrder}"
        );

        return redirect()->to(site_url('manajemen-desain/' . $idOrder))
            ->with('success', "Penyesuaian mockup draft v{$versi} tersimpan. Pelanggan akan melihat preview yang sama.");
    }

    public function updateStatusProduksi(): RedirectResponse
    {
        if ((string) session()->get('role') !== 'produksi') {
            return redirect()->to(site_url('dashboard'));
        }

        $idOrder   = (int) $this->request->getPost('id_order');
        $newStatus = (string) $this->request->getPost('new_status');

        if ($newStatus !== 'finishing') {
            return redirect()->back()->with('error', 'Aksi tidak valid.');
        }

        $order = $this->fetchOrderById($idOrder);
        if ($order === null) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        if ((string) ($order['status'] ?? '') !== 'proses_cetak') {
            return redirect()->back()->with('error', 'Pesanan tidak dalam tahap proses cetak.');
        }

        $kodeOrder = (string) ($order['kode_order'] ?? '');

        $tglFinishing  = date('Y-m-d');
        $updatePayload = [
            'status'        => 'finishing',
            'tgl_finishing' => $tglFinishing,
        ];

        try {
            \Config\Database::connect()
                ->table('orders')
                ->where('id_order', $idOrder)
                ->update($updatePayload);
        } catch (\Throwable $e) {
            log_message('error', '[RevisiController::updateStatusProduksi] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal memperbarui status pesanan.');
        }

        helper('activity_log');
        logActivity(
            'ubah',
            'produksi',
            "Mengubah status pesanan {$kodeOrder} dari proses_cetak menjadi finishing"
        );

        $emailPelanggan = trim((string) ($order['email_pelanggan'] ?? ''));
        $namaPelanggan  = (string) ($order['nama_pelanggan'] ?? 'Pelanggan');

        if ($emailPelanggan !== '') {
            $judulFinishing = 'Pesanan Masuk Tahap Finishing';
            $detailUrlFinishing = pelangganOrderDetailUrl($kodeOrder, $judulFinishing);
            sendNotifEmail(
                $emailPelanggan,
                "[No-Reply] {$judulFinishing}-{$kodeOrder}",
                renderNotifEmail('finishing', array_merge(buildEmailOrderViewData($order), [
                    'pesanHtml' => '<p style="margin:0 0 12px;">Halo <strong>' . esc($namaPelanggan) . '</strong>,</p>'
                        . '<p style="margin:0;">Pesanan '
                        . emailHighlightKodeOrder($kodeOrder)
                        . ' telah selesai dicetak dan sedang dalam tahap penyelesaian akhir (finishing).</p>',
                    'ctaUrl'      => $detailUrlFinishing,
                    'ctaLabel'    => 'Lihat Status Pesanan',
                    'statusLabel' => 'Finishing',
                    'statusNote'  => 'Kami akan memberi tahu Anda saat pesanan siap dikirim atau diambil.',
                ]))
            );
            sendNotifWaForEmail(
                \Config\Database::connect(),
                $emailPelanggan,
                buildNotifWaText(
                    "{$judulFinishing}-{$kodeOrder}",
                    "Pesanan {$kodeOrder} selesai dicetak dan masuk tahap finishing.",
                    $detailUrlFinishing
                )
            );
        }

        $returnTo = (string) $this->request->getPost('return_to');
        if ($returnTo === 'monitoring') {
            return redirect()->to(site_url('monitoring-produksi/' . $kodeOrder))
                ->with('success', "Pesanan {$kodeOrder} masuk tahap Finishing.");
        }

        return redirect()->back()
            ->with('success', "Pesanan {$kodeOrder} masuk tahap Finishing.");
    }

    public function acc(): RedirectResponse
    {
        if ((string) session()->get('role') !== 'pelanggan') {
            return redirect()->to(site_url('dashboard'));
        }

        $idOrder  = (int) $this->request->getPost('id_order');
        $idRevisi = (int) $this->request->getPost('id_revisi');
        $idPelanggan = (int) session()->get('id_pelanggan');

        $db = \Config\Database::connect();

        $revisi = $db->table('revisi_desain')->where('id_revisi', $idRevisi)->get()->getRowArray();
        if ($revisi === null || (int) ($revisi['id_order'] ?? 0) !== $idOrder) {
            return redirect()->back()->with('error', 'Data revisi tidak valid.');
        }

        $order = $this->fetchOrderById($idOrder);
        if ($order === null || (int) ($order['id_pelanggan'] ?? 0) !== $idPelanggan) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $revisStatus = (string) ($revisi['status'] ?? '');

        // Draft terbaru harus sudah uploaded (Produksi selesai iterasi)
        // sebelum ACC ke versi mana pun diperbolehkan.
        $latest = model(RevisiDesainModel::class)->getLatestByOrder($idOrder);
        if ($latest === null || ($latest['status'] ?? '') !== 'uploaded') {
            return redirect()->back()->with(
                'error',
                'ACC belum bisa dilakukan. Menunggu Produksi mengunggah draft terbaru terlebih dahulu.'
            );
        }

        // Draft target harus berstatus uploaded atau diajukan_revisi (belum pernah ditolak/acc).
        if (!in_array($revisStatus, ['uploaded', 'diajukan_revisi'], true)) {
            return redirect()->back()->with('error', 'Draft ini tidak dapat dipilih untuk cetak.');
        }

        // Pastikan belum ada draft lain yang di-ACC untuk pesanan ini.
        $sudahAcc = $db->table('revisi_desain')
            ->where('id_order', $idOrder)
            ->where('status', 'acc')
            ->countAllResults();
        if ($sudahAcc > 0) {
            return redirect()->back()->with('error', 'Sudah ada draft yang di-ACC untuk pesanan ini.');
        }

        $kodeOrder = (string) ($order['kode_order'] ?? '');
        $versi     = (int) ($revisi['versi'] ?? 0);

        try {
            $db->table('revisi_desain')->where('id_revisi', $idRevisi)->update(['status' => 'acc']);
            $db->table('orders')->where('id_order', $idOrder)->update([
                'status'          => 'proses_cetak',
                'tgl_mulai_cetak' => date('Y-m-d'),
                'tgl_finishing'   => null,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[RevisiController::acc] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal ACC desain.');
        }

        $this->notifyAllProduksi(
            $idOrder,
            'Desain di-ACC',
            "Pelanggan memilih draft v{$versi} untuk dicetak-{$kodeOrder}. Lanjut proses cetak."
        );

        $successMsg = "Draft v{$versi} dipilih untuk cetak. Pesanan lanjut ke proses cetak.";

        return redirect()->to(site_url('order/detail/' . $kodeOrder))
            ->with('success', $successMsg);
    }

    public function ajukan(): RedirectResponse
    {
        if ((string) session()->get('role') !== 'pelanggan') {
            return redirect()->to(site_url('dashboard'));
        }

        $idOrder       = (int) $this->request->getPost('id_order');
        $idRevisi      = (int) $this->request->getPost('id_revisi');
        $catatanRevisi = trim((string) $this->request->getPost('catatan_revisi'));
        $idPelanggan   = (int) session()->get('id_pelanggan');

        if ($catatanRevisi === '') {
            return redirect()->back()->with('error', 'Catatan revisi wajib diisi.');
        }

        $db = \Config\Database::connect();

        $revisi = $db->table('revisi_desain')->where('id_revisi', $idRevisi)->get()->getRowArray();
        if ($revisi === null || (int) ($revisi['id_order'] ?? 0) !== $idOrder) {
            return redirect()->back()->with('error', 'Data revisi tidak valid.');
        }

        $order = $this->fetchOrderById($idOrder);
        if ($order === null || (int) ($order['id_pelanggan'] ?? 0) !== $idPelanggan) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        if ((int) ($order['sisa_kuota'] ?? 0) <= 0) {
            return redirect()->back()->with('error', 'Kuota revisi habis. Hanya bisa ACC.');
        }

        if ((string) ($revisi['status'] ?? '') !== 'uploaded') {
            return redirect()->back()->with('error', 'Draft ini tidak dapat diajukan revisi.');
        }

        $kodeOrder  = (string) ($order['kode_order'] ?? '');
        $versi      = (int) ($revisi['versi'] ?? 0);
        $sisaBaru   = (int) ($order['sisa_kuota'] ?? 0) - 1;

        try {
            $db->table('revisi_desain')->where('id_revisi', $idRevisi)->update([
                'status'          => 'diajukan_revisi',
                'catatan_revisi'  => $catatanRevisi,
            ]);
            $db->table('orders')->where('id_order', $idOrder)->update([
                'status'     => 'proses_revisi',
                'sisa_kuota' => $sisaBaru,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[RevisiController::ajukan] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal mengajukan revisi.');
        }

        $this->notifyAllProduksi(
            $idOrder,
            'Revisi Diajukan',
            "Pelanggan ajukan revisi v{$versi} {$kodeOrder}. Sisa kuota: {$sisaBaru}."
        );

        if ($sisaBaru <= 0) {
            return redirect()->to(site_url('order/detail/' . $kodeOrder))
                ->with(
                    'warning',
                    'Revisi terakhir berhasil diajukan. Setelah draft baru diunggah produksi, Anda hanya dapat ACC desain (tidak bisa mengajukan revisi lagi).'
                );
        }

        return redirect()->to(site_url('order/detail/' . $kodeOrder))
            ->with(
                'success',
                'Revisi berhasil diajukan. Tim produksi akan menyiapkan draft baru. Sisa kuota revisi: ' . $sisaBaru . '.'
            );
    }

    public function approvalHistory(string $kodeOrder): RedirectResponse|string
    {
        $role = (string) session()->get('role');
        if (!in_array($role, ['pelanggan', 'produksi', 'admin', 'owner'], true)) {
            return redirect()->to(site_url('dashboard'));
        }

        $order = $this->fetchOrderByKode($kodeOrder);
        if ($order === null) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Pesanan tidak ditemukan.');
        }

        if ($role === 'pelanggan' && (int) ($order['id_pelanggan'] ?? 0) !== (int) session()->get('id_pelanggan')) {
            return redirect()->to(site_url('order'))
                ->with('error', 'Akses ditolak.');
        }

        if ($role === 'produksi') {
            return redirect()->to(site_url('manajemen-desain/' . (int) $order['id_order']) . '#history');
        }

        $idOrder   = (int) $order['id_order'];
        $revisList = model(RevisiDesainModel::class)->getByOrder($idOrder);
        $latest    = model(RevisiDesainModel::class)->getLatestByOrder($idOrder);

        return view('revisi/history', [
            'title'         => 'Riwayat Revisi-' . $kodeOrder,
            'page_title'    => 'Approval History Revisi',
            'order'         => $order,
            'revisList'     => $revisList,
            'latest'        => $latest,
            'role'          => $role,
            'mockupAngles'  => getMockupAnglesForProduk((int) ($order['id_katalog'] ?? 0)),
        ]);
    }

    /**
     * @return RedirectResponse|string
     */
    private function renderAntrian(bool $showAll, bool $readOnly = false)
    {
        $statuses = $showAll ? self::STATUSES_MANAJEMEN : self::STATUSES_ANTRIAN;
        $orders   = $this->fetchOrdersQueue($statuses);

        $revisiModel = model(RevisiDesainModel::class);
        foreach ($orders as &$item) {
            $item['last_revisi'] = $revisiModel->getLatestByOrder((int) $item['id_order']);
        }
        unset($item);

        $statusCounts = [];
        foreach ($orders as $row) {
            $st = (string) ($row['status'] ?? '');
            $statusCounts[$st] = ($statusCounts[$st] ?? 0) + 1;
        }

        return view('revisi/antrian', [
            'title'        => $showAll ? 'Manajemen Desain' : 'Antrian Desain',
            'page_title'   => $showAll ? 'Manajemen Desain' : 'Antrian Desain',
            'orders'       => $orders,
            'showAll'      => $showAll,
            'statusCounts' => $statusCounts,
            'readOnly'     => $readOnly,
        ]);
    }

    /**
     * @param list<string> $statuses
     * @return list<array<string, mixed>>
     */
    private function fetchOrdersQueue(array $statuses): array
    {
        return \Config\Database::connect()->table('orders o')
            ->select(
                'o.*, k.nama_produk, k.kategori, k.gambar AS gambar_katalog, '
                . 'u.nama AS nama_pelanggan, u.email AS email_pelanggan, p.no_telp, '
                . sqlLatestDpPaymentFields('o.id_order')
            )
            ->join('katalog k', 'k.id_katalog = o.id_katalog')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->whereIn('o.status', $statuses)
            ->orderBy('o.deadline_produksi', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadWorkspaceContext(int $idOrder): ?array
    {
        $order = $this->fetchOrderById($idOrder);
        if ($order === null) {
            return null;
        }

        $revisiModel = model(RevisiDesainModel::class);
        $lastRevis   = $revisiModel->getLatestByOrder($idOrder);
        $revisList   = $revisiModel->getByOrder($idOrder);
        $attrs       = $this->getOrderAttrsWithLabels(
            $idOrder,
            (int) ($order['id_katalog'] ?? 0)
        );

        $kodeOrder = (string) ($order['kode_order'] ?? '');

        return [
            'title'      => 'Workspace-' . $kodeOrder,
            'page_title' => 'Workspace Produksi',
            'order'      => $order,
            'attrs'      => $attrs,
            'revisList'  => $revisList,
            'lastRevis'  => $lastRevis,
            'canUpload'  => canProduksiUploadDraft($order, $lastRevis)
                && in_array((string) ($order['status'] ?? ''), self::STATUSES_UPLOAD_DETAIL, true),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getOrderAttrsWithLabels(int $idOrder, int $idKatalog): array
    {
        if ($idOrder <= 0) {
            return [];
        }

        $db = \Config\Database::connect();

        if ($idKatalog > 0) {
            return $db->table('order_attributes oa')
                ->select('oa.attribute_key, oa.attribute_val, ft.field_label, ft.field_type, ft.urutan')
                ->join(
                    'form_templates ft',
                    'ft.field_key = oa.attribute_key AND ft.id_katalog = ' . $idKatalog,
                    'left'
                )
                ->where('oa.id_order', $idOrder)
                ->orderBy('ft.urutan', 'ASC')
                ->get()
                ->getResultArray();
        }

        $rows = $db->table('order_attributes')
            ->where('id_order', $idOrder)
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $row['field_label'] = (string) ($row['attribute_key'] ?? '');
            $row['field_type']  = 'text';
        }
        unset($row);

        return $rows;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchOrderById(int $idOrder): ?array
    {
        $row = \Config\Database::connect()->table('orders o')
            ->select(
                'o.*, k.nama_produk, k.kategori, k.satuan, k.gambar AS gambar_katalog, '
                . 'u.nama AS nama_pelanggan, u.email AS email_pelanggan, '
                . 'u.id_user AS id_user_pelanggan, p.no_telp, '
                . sqlLatestDpPaymentFields('o.id_order')
            )
            ->join('katalog k', 'k.id_katalog = o.id_katalog')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('o.id_order', $idOrder)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchOrderByKode(string $kodeOrder): ?array
    {
        $row = \Config\Database::connect()->table('orders o')
            ->select(
                'o.*, k.nama_produk, k.kategori, k.satuan, k.gambar AS gambar_katalog, '
                . 'u.nama AS nama_pelanggan, u.email AS email_pelanggan, '
                . 'u.id_user AS id_user_pelanggan, p.no_telp, '
                . sqlLatestDpPaymentFields('o.id_order')
            )
            ->join('katalog k', 'k.id_katalog = o.id_katalog')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('o.kode_order', $kodeOrder)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    private function notifyAllProduksi(int $idOrder, string $judul, string $pesan): void
    {
        $users = \Config\Database::connect()->table('users')
            ->where('role', 'produksi')
            ->get()
            ->getResultArray();

        foreach ($users as $user) {
            sendNotifInApp((int) $user['id_user'], $idOrder, $judul, $pesan);
        }
    }
}
