<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index()
    {
        $role = (string) session()->get('role');
        $nama = (string) session()->get('nama');

        return match ($role) {
            'pelanggan' => $this->dashboardPelanggan($nama),
            'admin'     => $this->dashboardAdmin($nama),
            'keuangan'  => $this->dashboardKeuangan($nama),
            'produksi'  => $this->dashboardProduksi($nama),
            'owner'     => $this->dashboardOwner($nama),
            default     => redirect()->to('/login')
                ->with('error', 'Role tidak dikenali.'),
        };
    }

    public function notifikasi()
    {
        return redirect()->to(site_url('dashboard') . '?openNotif=1');
    }

    private function dashboardPelanggan(string $nama)
    {
        $idPelanggan = (int) session()->get('id_pelanggan');

        if ($idPelanggan <= 0) {
            return view('dashboard/pelanggan', [
                'nama'         => $nama,
                'cards'        => $this->emptyCards(4),
                'recentOrders' => [],
            ]);
        }

        try {
            $db = \Config\Database::connect();

            $statusCounts = $this->countOrdersByStatus($db, ['id_pelanggan' => $idPelanggan]);

            $totalPesanan = array_sum($statusCounts);
            $dalamProses  = $this->sumStatuses($statusCounts, [
                'menunggu_konfirmasi_harga',
                'menunggu_konfirmasi_pelanggan',
                'menunggu_verifikasi_dp',
                'terverifikasi',
                'proses_desain',
                'proses_revisi',
                'proses_cetak',
                'finishing',
                'siap_kirim',
                'siap_diambil',
                'dikirim',
                'pesanan_diterima',
                'menunggu_verifikasi_lunas',
            ]);
            $menungguBayar = $this->sumStatuses($statusCounts, [
                'menunggu_verifikasi_dp',
                'menunggu_verifikasi_lunas',
            ]);
            $selesai = $statusCounts['selesai'] ?? 0;

            $recentOrders = $this->getRecentOrders($db, ['id_pelanggan' => $idPelanggan]);

            return view('dashboard/pelanggan', [
                'nama'         => $nama,
                'cards'        => [
                    ['label' => 'Total Pesanan', 'value' => $totalPesanan, 'icon' => 'clipboard', 'color' => '#2E5CE6'],
                    ['label' => 'Dalam Proses', 'value' => $dalamProses, 'icon' => 'clock', 'color' => '#8B5CF6'],
                    ['label' => 'Menunggu Bayar', 'value' => $menungguBayar, 'icon' => 'wallet', 'color' => '#F59E0B'],
                    ['label' => 'Selesai', 'value' => $selesai, 'icon' => 'check', 'color' => '#10B981'],
                ],
                'recentOrders' => $recentOrders,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Dashboard pelanggan: {message}', ['message' => $e->getMessage()]);

            return view('dashboard/pelanggan', [
                'nama'         => $nama,
                'cards'        => $this->emptyCards(4),
                'recentOrders' => [],
            ]);
        }
    }

    private function dashboardAdmin(string $nama)
    {
        try {
            $db    = \Config\Database::connect();
            $today = date('Y-m-d');

            $totalHariIni = $db->table('orders')
                ->where('created_at >=', $today . ' 00:00:00')
                ->where('created_at <', date('Y-m-d', strtotime($today . ' +1 day')) . ' 00:00:00')
                ->countAllResults();

            $totalSemua = $db->table('orders')->countAllResults();

            $pendingCustom = $db->table('orders')
                ->where('is_custom', 1)
                ->whereIn('status', ['menunggu_konfirmasi_harga', 'menunggu_konfirmasi_pelanggan'])
                ->countAllResults();

            $pendingPengiriman = $db->table('orders')
                ->whereIn('status', ['siap_kirim', 'siap_diambil'])
                ->countAllResults();

            $recentOrders = $db->table('orders o')
                ->select('o.id_order, o.kode_order, o.status, o.total_harga, o.created_at, o.is_custom, k.nama_produk, u.nama AS nama_pelanggan')
                ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan', 'left')
                ->join('users u', 'u.id_user = p.id_user', 'left')
                ->join('katalog k', 'k.id_katalog = o.id_katalog', 'left')
                ->orderBy('o.created_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();

            return view('dashboard/admin', [
                'nama'         => $nama,
                'cards'        => [
                    ['label' => 'Pesanan Hari Ini', 'value' => $totalHariIni, 'icon' => 'calendar', 'color' => '#2E5CE6'],
                    ['label' => 'Total Pesanan', 'value' => $totalSemua, 'icon' => 'clipboard', 'color' => '#051747'],
                    ['label' => 'Pending Custom', 'value' => $pendingCustom, 'icon' => 'star', 'color' => '#F59E0B'],
                    ['label' => 'Pending Kirim', 'value' => $pendingPengiriman, 'icon' => 'truck', 'color' => '#10B981'],
                ],
                'recentOrders' => $recentOrders,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Dashboard admin: {message}', ['message' => $e->getMessage()]);

            return view('dashboard/admin', [
                'nama'         => $nama,
                'cards'        => $this->emptyCards(4),
                'recentOrders' => [],
            ]);
        }
    }

    private function dashboardKeuangan(string $nama)
    {
        try {
            $db = \Config\Database::connect();

            $pendingDp = $db->table('payments')
                ->where('jenis', 'dp')
                ->where('status', 'menunggu')
                ->countAllResults();

            $pendingPelunasan = $db->table('payments')
                ->where('jenis', 'pelunasan')
                ->where('status', 'menunggu')
                ->countAllResults();

            $totalMenunggu = $pendingDp + $pendingPelunasan;

            $totalNominalMenunggu = $db->table('payments')
                ->selectSum('nominal', 'total')
                ->where('status', 'menunggu')
                ->get()
                ->getRowArray()['total'] ?? 0;

            $recentPayments = $db->table('payments py')
                ->select('py.id_payment, py.jenis, py.nominal, py.status AS payment_status, py.created_at, o.kode_order, o.status AS order_status, u.nama AS nama_pelanggan')
                ->join('orders o', 'o.id_order = py.id_order', 'left')
                ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan', 'left')
                ->join('users u', 'u.id_user = p.id_user', 'left')
                ->where('py.status', 'menunggu')
                ->orderBy('py.created_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();

            return view('dashboard/keuangan', [
                'nama'            => $nama,
                'cards'           => [
                    ['label' => 'DP Menunggu', 'value' => $pendingDp, 'icon' => 'wallet', 'color' => '#F59E0B'],
                    ['label' => 'Pelunasan Menunggu', 'value' => $pendingPelunasan, 'icon' => 'check-circle', 'color' => '#EF4444'],
                    ['label' => 'Total Verifikasi', 'value' => $totalMenunggu, 'icon' => 'clipboard', 'color' => '#2E5CE6'],
                    ['label' => 'Nominal Menunggu', 'value' => 'Rp ' . number_format((float) $totalNominalMenunggu, 0, ',', '.'), 'icon' => 'cash', 'color' => '#10B981', 'isText' => true],
                ],
                'recentPayments' => $recentPayments,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Dashboard keuangan: {message}', ['message' => $e->getMessage()]);

            return view('dashboard/keuangan', [
                'nama'            => $nama,
                'cards'           => $this->emptyCards(4),
                'recentPayments'  => [],
            ]);
        }
    }

    private function dashboardProduksi(string $nama)
    {
        try {
            $db = \Config\Database::connect();

            $productionStatuses = [
                'terverifikasi',
                'proses_desain',
                'proses_revisi',
                'proses_cetak',
                'finishing',
            ];

            $statusCounts = $this->countOrdersByStatus($db, [], $productionStatuses);

            $totalAntrian = array_sum($statusCounts);
            $prosesDesain = ($statusCounts['terverifikasi'] ?? 0)
                + ($statusCounts['proses_desain'] ?? 0)
                + ($statusCounts['proses_revisi'] ?? 0);
            $prosesCetak  = ($statusCounts['proses_cetak'] ?? 0) + ($statusCounts['finishing'] ?? 0);

            $recentOrders = $this->getRecentOrders($db, [], $productionStatuses);

            return view('dashboard/produksi', [
                'nama'         => $nama,
                'cards'        => [
                    ['label' => 'Total Antrian', 'value' => $totalAntrian, 'icon' => 'layers', 'color' => '#2E5CE6'],
                    ['label' => 'Tahap Desain', 'value' => $prosesDesain, 'icon' => 'edit', 'color' => '#8B5CF6'],
                    ['label' => 'Tahap Cetak', 'value' => $prosesCetak, 'icon' => 'printer', 'color' => '#F59E0B'],
                    ['label' => 'Finishing', 'value' => $statusCounts['finishing'] ?? 0, 'icon' => 'sparkles', 'color' => '#10B981'],
                ],
                'recentOrders' => $recentOrders,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Dashboard produksi: {message}', ['message' => $e->getMessage()]);

            return view('dashboard/produksi', [
                'nama'         => $nama,
                'cards'        => $this->emptyCards(4),
                'recentOrders' => [],
            ]);
        }
    }

    private function dashboardOwner(string $nama)
    {
        try {
            $db = \Config\Database::connect();

            $bulanIni = date('Y-m');

            $totalPesanan = $db->table('orders')->countAllResults();
            $pesananSelesai = $db->table('orders')->where('status', 'selesai')->countAllResults();
            $pesananAktif = $db->table('orders')
                ->whereNotIn('status', ['selesai', 'dibatalkan'])
                ->countAllResults();

            $transaksiBulanIni = $db->table('orders')
                ->selectSum('total_harga', 'total')
                ->where('status', 'selesai')
                ->where('created_at >=', $bulanIni . '-01 00:00:00')
                ->where('created_at <', date('Y-m-d', strtotime($bulanIni . '-01 +1 month')) . ' 00:00:00')
                ->get()
                ->getRowArray()['total'] ?? 0;

            $recentOrders = $this->getRecentOrders($db);

            // Data grafik 7 hari terakhir
            $chartRows = $db->table('orders')
                ->select("DATE(created_at) as tgl, COUNT(*) as total")
                ->where('created_at >=', date('Y-m-d', strtotime('-6 days')))
                ->groupBy('DATE(created_at)')
                ->orderBy('tgl', 'ASC')
                ->get()->getResultArray();

            $chartLabels = array_column($chartRows, 'tgl');
            $chartValues = array_map('intval', array_column($chartRows, 'total'));

            return view('dashboard/owner', [
                'nama'         => $nama,
                'cards'        => [
                    ['label' => 'Total Pesanan', 'value' => $totalPesanan, 'icon' => 'clipboard', 'color' => '#2E5CE6'],
                    ['label' => 'Pesanan Aktif', 'value' => $pesananAktif, 'icon' => 'clock', 'color' => '#F59E0B'],
                    ['label' => 'Pesanan Selesai', 'value' => $pesananSelesai, 'icon' => 'check', 'color' => '#10B981'],
                    ['label' => 'Transaksi Bulan Ini', 'value' => 'Rp ' . number_format((float) $transaksiBulanIni, 0, ',', '.'), 'icon' => 'cash', 'color' => '#051747', 'isText' => true],
                ],
                'recentOrders' => $recentOrders,
                'chartLabels'  => $chartLabels,
                'chartValues'  => $chartValues,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Dashboard owner: {message}', ['message' => $e->getMessage()]);

            return view('dashboard/owner', [
                'nama'         => $nama,
                'cards'        => $this->emptyCards(4),
                'recentOrders' => [],
                'chartLabels'  => [],
                'chartValues'  => [],
            ]);
        }
    }

    /**
     * @param array<string, mixed> $where
     * @param list<string>|null     $statusFilter
     * @return array<string, int>
     */
    private function countOrdersByStatus(\CodeIgniter\Database\BaseConnection $db, array $where = [], ?array $statusFilter = null): array
    {
        $builder = $db->table('orders')->select('status, COUNT(*) AS total');

        foreach ($where as $col => $val) {
            $builder->where($col, $val);
        }

        if ($statusFilter !== null) {
            $builder->whereIn('status', $statusFilter);
        }

        $rows = $builder->groupBy('status')->get()->getResultArray();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    /**
     * @param array<string, mixed> $where
     * @param list<string>|null    $statusFilter
     * @return list<array<string, mixed>>
     */
    private function getRecentOrders(\CodeIgniter\Database\BaseConnection $db, array $where = [], ?array $statusFilter = null): array
    {
        $builder = $db->table('orders o')
            ->select('o.id_order, o.kode_order, o.status, o.total_harga, o.created_at, o.is_custom, k.nama_produk')
            ->join('katalog k', 'k.id_katalog = o.id_katalog', 'left');

        foreach ($where as $col => $val) {
            $builder->where('o.' . $col, $val);
        }

        if ($statusFilter !== null) {
            $builder->whereIn('o.status', $statusFilter);
        }

        return $builder->orderBy('o.created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
    }

    /**
     * @param array<string, int> $counts
     * @param list<string>       $statuses
     */
    private function sumStatuses(array $counts, array $statuses): int
    {
        $sum = 0;
        foreach ($statuses as $status) {
            $sum += $counts[$status] ?? 0;
        }

        return $sum;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function emptyCards(int $count): array
    {
        $labels = ['Statistik 1', 'Statistik 2', 'Statistik 3', 'Statistik 4'];
        $icons  = ['clipboard', 'clock', 'wallet', 'check'];
        $colors = ['#2E5CE6', '#8B5CF6', '#F59E0B', '#10B981'];
        $cards  = [];

        for ($i = 0; $i < $count; $i++) {
            $cards[] = [
                'label' => $labels[$i] ?? 'Statistik',
                'value' => 0,
                'icon'  => $icons[$i] ?? 'clipboard',
                'color' => $colors[$i] ?? '#2E5CE6',
            ];
        }

        return $cards;
    }
}
