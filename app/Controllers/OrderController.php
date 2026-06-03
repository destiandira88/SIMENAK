<?php

namespace App\Controllers;

use App\Models\KatalogModel;
use App\Models\OrderModel;
use CodeIgniter\HTTP\RedirectResponse;

class OrderController extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function index()
    {
        $pelangganCheck = $this->ensurePelanggan();
        if ($pelangganCheck !== null) {
            return $pelangganCheck;
        }

        $idPelanggan = (int) session()->get('id_pelanggan');
        $orders      = model(OrderModel::class)->getByPelanggan($idPelanggan);

        return view('order/index', [
            'title'      => 'Pesanan Saya',
            'page_title' => 'Pesanan Saya',
            'orders'     => $orders,
        ]);
    }

    public function create(int $idKatalog)
    {
        $pelangganCheck = $this->ensurePelanggan();
        if ($pelangganCheck !== null) {
            return $pelangganCheck;
        }

        $katalogModel = model(KatalogModel::class);
        $katalog      = $katalogModel->find($idKatalog);

        if ($katalog === null || (int) ($katalog['is_active'] ?? 0) !== 1) {
            return redirect()->to(site_url('katalog'))
                ->with('error', 'Produk tidak tersedia.');
        }

        $db = \Config\Database::connect();

        $fields = $db->table('form_templates')
            ->where('id_katalog', $idKatalog)
            ->orderBy('urutan', 'ASC')
            ->get()
            ->getResultArray();

        $pelanggan = $db->table('pelanggan')
            ->where('id_pelanggan', (int) session()->get('id_pelanggan'))
            ->get()
            ->getRowArray();

        return view('order/create', [
            'title'      => 'Buat Pesanan — ' . ($katalog['nama_produk'] ?? ''),
            'page_title' => 'Buat Pesanan Baru',
            'katalog'    => $katalog,
            'fields'     => $fields,
            'pelanggan'  => $pelanggan,
        ]);
    }

    public function store()
    {
        if ((string) session()->get('role') !== 'pelanggan') {
            return redirect()->to(site_url('dashboard'));
        }

        $rules = [
            'id_katalog'        => 'required|integer',
            'jenis_pelanggan'   => 'required|in_list[perseorangan,perusahaan]',
            'jumlah_order'      => 'required|integer|greater_than[0]',
            'detail_pesanan'    => 'required|min_length[5]',
            'deadline'          => 'required|valid_date',
            'metode_pengiriman' => 'required|in_list[kurir,ambil_sendiri]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $idKatalog = (int) $this->request->getPost('id_katalog');
        $katalog   = model(KatalogModel::class)->find($idKatalog);

        if ($katalog === null) {
            return redirect()->back()->with('error', 'Produk tidak ditemukan.');
        }

        $jumlahOrder = (int) $this->request->getPost('jumlah_order');
        if ($jumlahOrder < (int) $katalog['min_order']) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Minimum order {$katalog['min_order']} {$katalog['satuan']}.");
        }

        $db          = \Config\Database::connect();
        $idPelanggan = (int) session()->get('id_pelanggan');
        $pelanggan   = $db->table('pelanggan')
            ->where('id_pelanggan', $idPelanggan)
            ->get()
            ->getRowArray();

        $jenisDiminta = (string) $this->request->getPost('jenis_pelanggan');
        $isVerified   = (int) ($pelanggan['is_verified'] ?? 0);

        if ($jenisDiminta === 'perusahaan' && $isVerified === 1) {
            $jenisFinal = 'perusahaan';
            $requireDp  = 0;
            $statusAwal = 'terverifikasi';
        } else {
            $jenisFinal = 'perseorangan';
            $requireDp  = 1;
            $statusAwal = 'menunggu_verifikasi_dp';
            if ($jenisDiminta === 'perusahaan' && $isVerified === 0) {
                session()->setFlashdata(
                    'warning',
                    'Akun belum terverifikasi sebagai perusahaan. Diproses sebagai perseorangan (DP 50%).'
                );
            }
        }

        $isCustom = (int) $this->request->getPost('is_custom');
        if ($isCustom === 1) {
            $statusAwal = 'menunggu_konfirmasi_harga';
            $requireDp  = 1;
        }

        $referensiPath = null;
        $file          = $this->request->getFile('referensi_desain');
        if ($file !== null && $file->isValid() && !$file->hasMoved()) {
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            $ext     = strtolower($file->getExtension());
            if (!in_array($ext, $allowed, true)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Format referensi tidak valid. Gunakan JPG, PNG, atau PDF.');
            }
            if ($file->getSize() > 2 * 1024 * 1024) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ukuran referensi maksimal 2MB.');
            }

            $uploadDir = FCPATH . 'uploads/referensi/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Folder upload tidak tersedia.');
            }

            $newName = time() . '_' . $file->getClientName();
            $file->move($uploadDir, $newName);
            $referensiPath = $newName;
        }

        $totalHarga  = $isCustom === 1 ? 0 : (float) $katalog['harga_dasar'] * $jumlahOrder;
        $kuotaRevisi = (int) $katalog['kuota_revisi_default'];
        $kodeOrder   = generateKodeOrder();
        $idOrder     = 0;

        $db->transStart();

        try {
            $db->table('orders')->insert([
                'kode_order'        => $kodeOrder,
                'id_pelanggan'      => $idPelanggan,
                'id_katalog'        => $idKatalog,
                'jenis_pelanggan'   => $jenisFinal,
                'jumlah_order'      => $jumlahOrder,
                'is_custom'         => $isCustom,
                'catatan_custom'    => $isCustom === 1 ? $this->request->getPost('catatan_custom') : null,
                'referensi_desain'  => $referensiPath,
                'detail_pesanan'    => $this->request->getPost('detail_pesanan'),
                'deadline'          => $this->request->getPost('deadline'),
                'metode_pengiriman' => $this->request->getPost('metode_pengiriman'),
                'alamat_kirim'      => $this->request->getPost('alamat_kirim'),
                'kuota_revisi'      => $kuotaRevisi,
                'sisa_kuota'        => $kuotaRevisi,
                'total_harga'       => $totalHarga,
                'require_dp'        => $requireDp,
                'status'            => $statusAwal,
                'created_at'        => date('Y-m-d H:i:s'),
                'batas_upload_dp'   => ($jenisFinal === 'perseorangan' && $requireDp === 1 && $isCustom === 0)
                    ? date('Y-m-d H:i:s', strtotime('+24 hours'))
                    : null,
                'reminder_dp_sent'  => 0,
            ]);

            $idOrder = (int) $db->insertID();

            $eavData = $this->request->getPost('eav') ?? [];
            if (is_array($eavData)) {
                foreach ($eavData as $key => $val) {
                    if (empty(trim((string) $key))) {
                        continue;
                    }
                    $db->table('order_attributes')->insert([
                        'id_order'      => $idOrder,
                        'attribute_key' => $key,
                        'attribute_val' => $val ?? '',
                    ]);
                }
            }

            $eavFiles = $this->request->getFiles();
            if (!empty($eavFiles['eav_file']) && is_array($eavFiles['eav_file'])) {
                $lampiranDir = FCPATH . 'uploads/lampiran_peta/';
                if (!is_dir($lampiranDir) && !mkdir($lampiranDir, 0755, true) && !is_dir($lampiranDir)) {
                    throw new \RuntimeException('Folder lampiran tidak tersedia.');
                }

                foreach ($eavFiles['eav_file'] as $fieldKey => $eavFile) {
                    if ($eavFile === null || !$eavFile->isValid() || $eavFile->hasMoved()) {
                        continue;
                    }
                    if ($eavFile->getSize() > 2 * 1024 * 1024) {
                        continue;
                    }
                    $eavName = time() . '_' . $eavFile->getClientName();
                    $eavFile->move($lampiranDir, $eavName);
                    $db->table('order_attributes')->insert([
                        'id_order'      => $idOrder,
                        'attribute_key' => $fieldKey,
                        'attribute_val' => $eavName,
                    ]);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi gagal disimpan.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[OrderController::store] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan pesanan. Silakan coba lagi.');
        }

        if ($isCustom === 1) {
            $admin = $db->table('users')->where('role', 'admin')->get()->getRowArray();
            if ($admin !== null) {
                sendNotifInApp(
                    (int) $admin['id_user'],
                    $idOrder,
                    'Pesanan Custom Baru',
                    "Pesanan custom masuk: {$kodeOrder}. Harap set harga."
                );
            }
        } elseif ($requireDp === 0) {
            $userRow = $db->table('users')
                ->where('id_user', (int) session()->get('id_user'))
                ->get()
                ->getRowArray();
            if ($userRow !== null) {
                sendNotifEmail(
                    (string) $userRow['email'],
                    "Pesanan {$kodeOrder} Berhasil Dibuat — SIMENAK Z'Plack",
                    '<p>Halo <strong>' . esc((string) $userRow['nama']) . '</strong>,</p>'
                    . '<p>Pesanan <strong>' . esc($kodeOrder) . '</strong> telah berhasil dibuat '
                    . 'dan langsung masuk ke antrian produksi tanpa DP.</p>'
                    . '<p>Pantau status di dashboard SIMENAK.</p>'
                );
            }
        }

        return redirect()->to(site_url('order/detail/' . $kodeOrder))
            ->with('success', "Pesanan {$kodeOrder} berhasil dibuat!");
    }

    public function redirectPesananSaya(): RedirectResponse
    {
        return redirect()->to(site_url('order'));
    }

    public function redirectPesananSayaDetail(string $segment): RedirectResponse
    {
        if (ctype_digit($segment)) {
            $order = model(OrderModel::class)->find((int) $segment);
            if ($order !== null && !empty($order['kode_order'])) {
                return redirect()->to(site_url('order/detail/' . $order['kode_order']));
            }
        }

        return redirect()->to(site_url('order/detail/' . $segment));
    }

    public function detail(string $kodeOrder)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(site_url('login'));
        }

        if (ctype_digit($kodeOrder)) {
            $orderById = model(OrderModel::class)->find((int) $kodeOrder);
            if ($orderById !== null && !empty($orderById['kode_order'])) {
                return redirect()->to(site_url('order/detail/' . $orderById['kode_order']));
            }
        }

        $db = \Config\Database::connect();

        $order = $db->table('orders o')
            ->select(
                'o.*, k.nama_produk, k.kategori, k.estimasi_hari, '
                . 'k.gambar AS gambar_katalog, k.satuan, k.min_order, '
                . 'k.kuota_revisi_default, u.nama AS nama_pelanggan, u.email AS email_pelanggan, '
                . 'p.no_telp, p.is_verified'
            )
            ->join('katalog k', 'k.id_katalog = o.id_katalog')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('o.kode_order', $kodeOrder)
            ->get()
            ->getRowArray();

        if ($order === null) {
            return redirect()->to(site_url('order'))
                ->with('error', 'Pesanan tidak ditemukan.');
        }

        $role = (string) session()->get('role');
        if ($role === 'pelanggan') {
            $idPelanggan = (int) session()->get('id_pelanggan');
            if ((int) $order['id_pelanggan'] !== $idPelanggan) {
                return redirect()->to(site_url('order'))
                    ->with('error', 'Akses ditolak.');
            }
        }

        $idKatalog = (int) $order['id_katalog'];
        $idOrder   = (int) $order['id_order'];

        $attrs = $db->table('order_attributes oa')
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

        $revisList = $db->table('revisi_desain rd')
            ->select('rd.*, u.nama AS nama_produksi')
            ->join('users u', 'u.id_user = rd.id_produksi', 'left')
            ->where('rd.id_order', $idOrder)
            ->orderBy('rd.versi', 'ASC')
            ->get()
            ->getResultArray();

        $payments = $db->table('payments')
            ->where('id_order', $idOrder)
            ->orderBy('tgl_upload', 'ASC')
            ->get()
            ->getResultArray();

        $pengiriman = $db->table('pengiriman')
            ->where('id_order', $idOrder)
            ->get()
            ->getRowArray();

        return view('order/detail', [
            'title'      => 'Detail Pesanan — ' . $kodeOrder,
            'page_title' => 'Detail Pesanan',
            'order'      => $order,
            'attrs'      => $attrs,
            'revisList'  => $revisList,
            'payments'   => $payments,
            'pengiriman' => $pengiriman,
        ]);
    }

    public function listPemesanan()
    {
        if ((string) session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'));
        }

        $orders = model(OrderModel::class)->getListPemesanan();

        $countAll      = count($orders);
        $countCustom   = 0;
        $countMenunggu = 0;
        foreach ($orders as $row) {
            if ((int) ($row['is_custom'] ?? 0) === 1) {
                $countCustom++;
            }
            if (($row['status'] ?? '') === 'menunggu_konfirmasi_harga') {
                $countMenunggu++;
            }
        }

        $activeTab = (string) ($this->request->getGet('tab') ?? 'semua');
        if (!in_array($activeTab, ['semua', 'custom', 'menunggu-harga'], true)) {
            $activeTab = 'semua';
        }

        return view('order/list_pemesanan', [
            'title'         => 'List Pemesanan',
            'page_title'    => 'List Pemesanan',
            'orders'        => $orders,
            'countAll'      => $countAll,
            'countCustom'   => $countCustom,
            'countMenunggu' => $countMenunggu,
            'activeTab'     => $activeTab,
        ]);
    }

    public function detailById(int $idOrder)
    {
        if ((string) session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'));
        }

        $order = model(OrderModel::class)->find($idOrder);
        if ($order === null) {
            return redirect()->to(site_url('list-pemesanan'))
                ->with('error', 'Pemesanan tidak ditemukan.');
        }

        return redirect()->to(site_url('order/detail/' . $order['kode_order']));
    }

    public function delete(string $kodeOrder)
    {
        if ((string) session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'));
        }

        $db    = \Config\Database::connect();
        $order = $db->table('orders')->where('kode_order', $kodeOrder)->get()->getRowArray();

        if ($order === null) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        $idOrder = (int) $order['id_order'];

        try {
            $db->transStart();
            $db->table('notifications')->where('id_order', $idOrder)->delete();
            $db->table('order_attributes')->where('id_order', $idOrder)->delete();
            $db->table('payments')->where('id_order', $idOrder)->delete();
            $db->table('revisi_desain')->where('id_order', $idOrder)->delete();
            $db->table('pengiriman')->where('id_order', $idOrder)->delete();
            $db->table('orders')->where('id_order', $idOrder)->delete();
            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Gagal menghapus pesanan.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[OrderController::delete] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal menghapus pesanan.');
        }

        return redirect()->to(site_url('list-pemesanan'))
            ->with('success', 'Pemesanan ' . $kodeOrder . ' berhasil dihapus.');
    }

    public function batalkan(): RedirectResponse
    {
        $role    = (string) session()->get('role');
        $idOrder = (int) $this->request->getPost('id_order');
        $alasan  = (string) ($this->request->getPost('alasan') ?? 'Dibatalkan');

        $db    = \Config\Database::connect();
        $order = $db->table('orders o')
            ->select('o.*, u.email, u.nama, u.id_user AS id_user_pelanggan')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('o.id_order', $idOrder)
            ->get()
            ->getRowArray();

        if (!$order) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        if ($role === 'pelanggan') {
            if ((int) $order['id_pelanggan'] !== (int) session()->get('id_pelanggan')) {
                return redirect()->back()->with('error', 'Akses ditolak.');
            }
            $bisaDibatalkan = [
                'menunggu_verifikasi_dp',
                'menunggu_konfirmasi_harga',
                'menunggu_konfirmasi_pelanggan',
                'terverifikasi',
                'proses_desain',
                'proses_revisi',
            ];
            if (!in_array($order['status'], $bisaDibatalkan, true)) {
                return redirect()->back()
                    ->with('error', 'Pesanan tidak dapat dibatalkan di tahap ini.');
            }
        } elseif ($role !== 'admin') {
            return redirect()->to(site_url('dashboard'))->with('error', 'Akses ditolak.');
        }

        $db->table('orders')->update(['status' => 'dibatalkan'], ['id_order' => $idOrder]);

        if ($role === 'admin') {
            sendNotifEmail(
                $order['email'],
                "Pesanan {$order['kode_order']} Dibatalkan — SIMENAK Z'Plack",
                '<p>Halo <strong>' . esc($order['nama']) . '</strong>,</p>'
                . '<p>Pesanan <strong>' . esc($order['kode_order']) . '</strong> dibatalkan. '
                . 'Alasan: <strong>' . esc($alasan) . '</strong>.</p>'
                . '<p>Hubungi admin Z\'Plack jika ada pertanyaan.</p>'
            );
        }

        $redirectUrl = $role === 'admin'
            ? site_url('list-pemesanan')
            : site_url('order/detail/' . $order['kode_order']);

        return redirect()->to($redirectUrl)
            ->with('info', "Pesanan {$order['kode_order']} berhasil dibatalkan.");
    }

    private function ensurePelanggan(): ?RedirectResponse
    {
        if ((string) session()->get('role') !== 'pelanggan') {
            return redirect()->to(site_url('dashboard'));
        }

        return null;
    }
}
