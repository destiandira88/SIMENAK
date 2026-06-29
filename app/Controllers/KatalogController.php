<?php

namespace App\Controllers;

use App\Models\KatalogModel;
use CodeIgniter\HTTP\RedirectResponse;

class KatalogController extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index()
    {
        $role = (string) session()->get('role');
        if (!in_array($role, ['admin', 'owner'], true)) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses ditolak.');
        }

        $readOnly = $role === 'owner';
        $katalogModel = model(KatalogModel::class);

        try {
            $katalog = $katalogModel->getWithFormCount();
        } catch (\Throwable $e) {
            log_message('error', 'Katalog index: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Gagal memuat data katalog.');
        }

        return view('katalog/index', [
            'katalog'  => $katalog,
            'title'    => $readOnly ? 'Katalog Produk' : 'Kelola Katalog',
            'readOnly' => $readOnly,
        ]);
    }

    public function list()
    {
        $katalogModel = model(KatalogModel::class);

        try {
            $katalog = $katalogModel->getAktif();
        } catch (\Throwable $e) {
            log_message('error', 'Katalog list: {message}', ['message' => $e->getMessage()]);

            $katalog = [];
        }

        $canCreateOrder = true;

        if ((string) session()->get('role') === 'pelanggan' && (int) session()->get('id_pelanggan') > 0) {
            helper('notification');
            $pelanggan = \Config\Database::connect()
                ->table('pelanggan')
                ->where('id_pelanggan', (int) session()->get('id_pelanggan'))
                ->get()
                ->getRowArray();

            $canCreateOrder = pelangganCanCreateOrder($pelanggan);
        }

        return view('katalog/list', [
            'katalog'        => $katalog,
            'title'          => 'Katalog Produk',
            'canCreateOrder' => $canCreateOrder,
        ]);
    }

    public function create()
    {
        $adminCheck = $this->ensureAdmin();
        if ($adminCheck !== null) {
            return $adminCheck;
        }

        return view('katalog/create', [
            'title' => 'Tambah Produk',
        ]);
    }

    public function store()
    {
        $adminCheck = $this->ensureAdmin();
        if ($adminCheck !== null) {
            return $adminCheck;
        }

        if (!$this->validate($this->validationRules())) {
            return redirect()->back()
                ->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $katalogModel = model(KatalogModel::class);
        $data         = $this->buildKatalogDataFromPost();
        $data['is_active'] = 1;

        $uploadResult = $this->processGambarUpload();
        if (isset($uploadResult['error'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', $uploadResult['error']);
        }
        $data['gambar'] = $uploadResult['path'];

        try {
            $katalogModel->insert($data);
        } catch (\Throwable $e) {
            log_message('error', 'Katalog store: {message}', ['message' => $e->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan produk. Silakan coba lagi.');
        }

        return redirect()->to(site_url('katalog/kelola'))
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $adminCheck = $this->ensureAdmin();
        if ($adminCheck !== null) {
            return $adminCheck;
        }

        return $this->renderKatalogForm($id, false);
    }

    public function detail(int $id)
    {
        $role = (string) session()->get('role');
        if (!in_array($role, ['admin', 'owner'], true)) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses ditolak.');
        }

        return $this->renderKatalogForm($id, true);
    }

    /**
     * @return RedirectResponse|string
     */
    private function renderKatalogForm(int $id, bool $readOnly)
    {
        $katalogModel = model(KatalogModel::class);

        try {
            $katalog = $katalogModel->find($id);
        } catch (\Throwable $e) {
            log_message('error', 'Katalog form: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('katalog/kelola'))
                ->with('error', 'Gagal memuat data produk.');
        }

        if ($katalog === null) {
            return redirect()->to(site_url('katalog/kelola'))
                ->with('error', 'Produk tidak ditemukan.');
        }

        return view('katalog/edit', [
            'katalog'  => $katalog,
            'title'    => $readOnly ? 'Detail Produk' : 'Edit Produk',
            'readOnly' => $readOnly,
        ]);
    }

    public function update(int $id)
    {
        $adminCheck = $this->ensureAdmin();
        if ($adminCheck !== null) {
            return $adminCheck;
        }

        $katalogModel = model(KatalogModel::class);

        try {
            $existing = $katalogModel->find($id);
        } catch (\Throwable $e) {
            log_message('error', 'Katalog update find: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('katalog'))
                ->with('error', 'Gagal memuat data produk.');
        }

        if ($existing === null) {
            return redirect()->to(site_url('katalog/kelola'))
                ->with('error', 'Produk tidak ditemukan.');
        }

        if (!$this->validate($this->validationRules())) {
            return redirect()->back()
                ->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $isActive = (int) $this->request->getPost('is_active') === 1 ? 1 : 0;

        $uploadResult = $this->processGambarUpload($existing['gambar'] ?? null);
        if (isset($uploadResult['error'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', $uploadResult['error']);
        }

        try {
            $payload = $this->buildKatalogDataFromPost();
            $katalogModel->update($id, array_merge($payload, [
                'is_active' => $isActive,
                'gambar'    => $uploadResult['path'],
            ]));
        } catch (\Throwable $e) {
            log_message('error', 'Katalog update: {message}', ['message' => $e->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui produk. Silakan coba lagi.');
        }

        return redirect()->to(site_url('katalog/kelola'))
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function toggleStatus(int $id)
    {
        $adminCheck = $this->ensureAdmin();
        if ($adminCheck !== null) {
            return $adminCheck;
        }

        $katalogModel = model(KatalogModel::class);

        try {
            $katalog = $katalogModel->find($id);
        } catch (\Throwable $e) {
            log_message('error', 'Katalog toggleStatus find: {message}', ['message' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Gagal memuat data produk.');
        }

        if ($katalog === null) {
            return redirect()->back()
                ->with('error', 'Produk tidak ditemukan.');
        }

        $newStatus = (int) ($katalog['is_active'] ?? 0) === 1 ? 0 : 1;

        try {
            $katalogModel->update($id, ['is_active' => $newStatus]);
        } catch (\Throwable $e) {
            log_message('error', 'Katalog toggleStatus: {message}', ['message' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Gagal mengubah status produk.');
        }

        $msg = $newStatus === 1 ? 'Produk diaktifkan.' : 'Produk dinonaktifkan.';

        return redirect()->back()->with('success', $msg);
    }

    public function delete(int $id)
    {
        $adminCheck = $this->ensureAdmin();
        if ($adminCheck !== null) {
            return $adminCheck;
        }

        $katalogModel = model(KatalogModel::class);

        try {
            $katalog = $katalogModel->find($id);

            if ($katalog === null) {
                return redirect()->to(site_url('katalog/kelola'))
                    ->with('error', 'Produk tidak ditemukan.');
            }

            $db = \Config\Database::connect();

            $orderCount = $db->table('orders')
                ->where('id_katalog', $id)
                ->countAllResults();

            if ($orderCount > 0) {
                $katalogModel->update($id, ['is_active' => 0]);

                return redirect()->to(site_url('katalog/kelola'))
                    ->with('warning', 'Produk tidak dapat dihapus karena memiliki riwayat pesanan. Produk telah dinonaktifkan.');
            }

            $db->table('form_templates')->where('id_katalog', $id)->delete();

            if (!empty($katalog['gambar'])) {
                $gambarFile = FCPATH . 'uploads/katalog/' . $katalog['gambar'];
                if (is_file($gambarFile)) {
                    unlink($gambarFile);
                }
            }

            $katalogModel->delete($id);

            return redirect()->to(site_url('katalog/kelola'))
                ->with('success', 'Produk berhasil dihapus.');
        } catch (\Throwable $e) {
            log_message('error', 'Katalog delete: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('katalog/kelola'))
                ->with('error', 'Gagal menghapus produk.');
        }
    }

    private function ensureAdmin(): ?RedirectResponse
    {
        if ((string) session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses ditolak.');
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function validationRules(): array
    {
        return [
            'nama_produk'          => 'required|max_length[150]',
            'kategori'             => 'required|in_list[desain_grafis,cetak_digital,cetak_offset,media_promosi]',
            'harga_dasar'          => 'required|numeric|greater_than[0]',
            'kuota_revisi_default' => 'required|integer|greater_than[0]|less_than_equal_to[10]',
            'min_order'            => 'required|integer|greater_than[0]',
            'satuan'               => 'required|max_length[30]',
            'estimasi_hari'        => 'required|integer|greater_than[0]|less_than_equal_to[180]',
            'deskripsi'            => 'permit_empty|max_length[500]',
        ];
    }

    /**
     * @return array{path: string|null}|array{error: string}
     */
    private function processGambarUpload(?string $existingGambar = null): array
    {
        $gambar = $this->request->getFile('gambar');

        if ($gambar === null || $gambar->getError() === UPLOAD_ERR_NO_FILE) {
            return ['path' => $existingGambar];
        }

        if (!$gambar->isValid() || $gambar->hasMoved()) {
            return ['error' => 'Upload gambar gagal. Silakan coba lagi.'];
        }

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array(strtolower($gambar->getExtension()), $allowed, true)) {
            return ['error' => 'Format gambar tidak valid. Gunakan JPG, PNG, atau WEBP.'];
        }

        if ($gambar->getSize() > 2 * 1024 * 1024) {
            return ['error' => 'Ukuran gambar maksimal 2MB.'];
        }

        $uploadDir = FCPATH . 'uploads/katalog/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            return ['error' => 'Folder upload tidak tersedia.'];
        }

        if ($existingGambar !== null && $existingGambar !== '') {
            $oldFile = $uploadDir . $existingGambar;
            if (is_file($oldFile)) {
                unlink($oldFile);
            }
        }

        $newName = time() . '_' . $gambar->getName();
        $gambar->move($uploadDir, $newName);

        return ['path' => $newName];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildKatalogDataFromPost(): array
    {
        helper('deadline');
        $estimasiHari = (int) $this->request->getPost('estimasi_hari');

        return [
            'nama_produk'          => $this->request->getPost('nama_produk'),
            'kategori'             => $this->request->getPost('kategori'),
            'harga_dasar'          => $this->request->getPost('harga_dasar'),
            'kuota_revisi_default' => $this->request->getPost('kuota_revisi_default'),
            'min_order'            => $this->request->getPost('min_order'),
            'satuan'               => $this->request->getPost('satuan'),
            'estimasi_hari'        => formatEstimasiHariKerja($estimasiHari),
            'deskripsi'            => $this->request->getPost('deskripsi'),
        ];
    }
}
