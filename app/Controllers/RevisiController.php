<?php

namespace App\Controllers;

use App\Models\RevisiDesainModel;
use CodeIgniter\HTTP\RedirectResponse;

class RevisiController extends BaseController
{
    protected $helpers = ['form', 'url', 'notification'];

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
        $backUrl  = $readOnly ? 'manajemen-desain' : 'antrian-desain';

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

    public function upload(int $idOrder): RedirectResponse
    {
        if ((string) session()->get('role') !== 'produksi') {
            return redirect()->to(site_url('dashboard'));
        }

        $order = $this->fetchOrderById($idOrder);
        if ($order === null) {
            return redirect()->to(site_url('antrian-desain'))
                ->with('error', 'Pesanan tidak ditemukan.');
        }

        if (!in_array((string) ($order['status'] ?? ''), self::STATUSES_UPLOAD_DETAIL, true)) {
            return redirect()->to(site_url('antrian-desain'))
                ->with('error', 'Upload draft tidak tersedia pada status ini.');
        }

        $revisiModel = model(RevisiDesainModel::class);
        $lastRevis   = $revisiModel->getLatestByOrder($idOrder);
        if (!canProduksiUploadDraft($order, $lastRevis)) {
            return redirect()->to(site_url('antrian-desain'))
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

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $revisiModel->insert([
                'id_order'      => $idOrder,
                'id_produksi'   => (int) session()->get('id_user'),
                'versi'         => $versi,
                'file_draft'    => $newName,
                'catatan_prod'  => $catatanProd !== '' ? $catatanProd : null,
                'status'        => 'uploaded',
                'created_at'    => date('Y-m-d H:i:s'),
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

        $idUserPelanggan = (int) ($order['id_user_pelanggan'] ?? 0);
        $emailPelanggan  = (string) ($order['email_pelanggan'] ?? '');
        $namaPelanggan   = (string) ($order['nama_pelanggan'] ?? 'Pelanggan');

        if ($idUserPelanggan > 0) {
            sendNotifInApp(
                $idUserPelanggan,
                $idOrder,
                'Draft Desain Tersedia',
                "Draft v{$versi} pesanan {$kodeOrder} siap direview."
            );
        }

        if ($emailPelanggan !== '') {
            sendNotifEmail(
                $emailPelanggan,
                "Draft Desain v{$versi} Tersedia-{$kodeOrder}",
                '<p>Halo <strong>' . esc($namaPelanggan) . '</strong>,</p>'
                . "<p>Draft v{$versi} untuk pesanan <strong>" . esc($kodeOrder) . '</strong> sudah tersedia.</p>'
                . '<p>Silakan login dan review draft-nya.</p>'
                . '<p><a href="' . esc(site_url('order/detail/' . $kodeOrder)) . '">Buka detail pesanan</a></p>'
            );
        }

        return redirect()->to(site_url('manajemen-desain/' . $idOrder))
            ->with('success', "Draft v{$versi} berhasil diunggah.");
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

        try {
            \Config\Database::connect()
                ->table('orders')
                ->where('id_order', $idOrder)
                ->update(['status' => 'finishing']);
        } catch (\Throwable $e) {
            log_message('error', '[RevisiController::updateStatusProduksi] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal memperbarui status pesanan.');
        }

        $emailPelanggan = trim((string) ($order['email_pelanggan'] ?? ''));
        $namaPelanggan  = (string) ($order['nama_pelanggan'] ?? 'Pelanggan');

        if ($emailPelanggan !== '') {
            sendNotifEmail(
                $emailPelanggan,
                "[No-Reply] Pesanan Masuk Tahap Finishing-{$kodeOrder}",
                '<p>Halo <strong>' . esc($namaPelanggan) . '</strong>,</p>'
                . "<p>Pesanan <strong>" . esc($kodeOrder) . '</strong> telah selesai proses cetak '
                . 'dan sedang dalam tahap <strong>Finishing</strong> (penyelesaian akhir).</p>'
                . '<p>Kami akan segera menghubungi Anda jika pesanan sudah siap dikirim atau diambil.</p>'
                . '<p><a href="' . esc(site_url('order/detail/' . $kodeOrder)) . '">Lihat detail pesanan</a></p>'
            );
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

        $sisaKuota = (int) ($order['sisa_kuota'] ?? 0);
        $revisStatus = (string) ($revisi['status'] ?? '');

        if ($sisaKuota > 0) {
            $latest = model(RevisiDesainModel::class)->getLatestByOrder($idOrder);
            if ($latest === null
                || (int) ($latest['id_revisi'] ?? 0) !== $idRevisi
                || ($latest['status'] ?? '') !== 'uploaded') {
                return redirect()->back()->with('error', 'Hanya draft terbaru yang menunggu review yang dapat di-ACC.');
            }
        } else {
            if (!in_array($revisStatus, ['uploaded', 'diajukan_revisi'], true)) {
                return redirect()->back()->with('error', 'Draft ini tidak dapat dipilih untuk cetak.');
            }

            $sudahAcc = $db->table('revisi_desain')
                ->where('id_order', $idOrder)
                ->where('status', 'acc')
                ->countAllResults();
            if ($sudahAcc > 0) {
                return redirect()->back()->with('error', 'Sudah ada draft yang di-ACC untuk pesanan ini.');
            }
        }

        $kodeOrder = (string) ($order['kode_order'] ?? '');
        $versi     = (int) ($revisi['versi'] ?? 0);

        try {
            $db->table('revisi_desain')->where('id_revisi', $idRevisi)->update(['status' => 'acc']);
            $db->table('orders')->where('id_order', $idOrder)->update(['status' => 'proses_cetak']);
        } catch (\Throwable $e) {
            log_message('error', '[RevisiController::acc] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal ACC desain.');
        }

        $this->notifyAllProduksi(
            $idOrder,
            'Desain di-ACC',
            "Pelanggan memilih draft v{$versi} untuk dicetak-{$kodeOrder}. Lanjut proses cetak."
        );

        $successMsg = $sisaKuota <= 0
            ? "Draft v{$versi} dipilih untuk cetak. Pesanan lanjut ke proses cetak."
            : 'Desain berhasil di-ACC. Pesanan lanjut ke proses cetak.';

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

        return redirect()->to(site_url('order/detail/' . $kodeOrder))
            ->with('warning', 'Revisi berhasil diajukan. Tim produksi akan menyiapkan draft baru.');
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
            'title'      => 'Riwayat Revisi-' . $kodeOrder,
            'page_title' => 'Approval History Revisi',
            'order'      => $order,
            'revisList'  => $revisList,
            'latest'     => $latest,
            'role'       => $role,
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
