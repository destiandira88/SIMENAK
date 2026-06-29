<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

class LaporanModel
{
    private const KATEGORI_LABELS = [
        'desain_grafis' => 'Desain Grafis',
        'cetak_digital' => 'Cetak Digital',
        'cetak_offset'  => 'Cetak Offset',
        'media_promosi' => 'Media Promosi',
    ];

    private const ADMIN_STATUS_FILTERS = ['semua', 'selesai', 'proses', 'dibatalkan'];

    private const KEUANGAN_STATUS_FILTERS = ['semua', 'terverifikasi', 'menunggu'];

    private const KEUANGAN_JENIS_FILTERS = ['semua', 'dp', 'pelunasan'];

    private const PRODUKSI_PIPELINE = [
        'terverifikasi',
        'proses_desain',
        'proses_revisi',
        'proses_cetak',
        'finishing',
    ];

    /**
     * @return array{
     *     start: string,
     *     end: string,
     *     prevStart: string,
     *     prevEnd: string,
     *     label: string,
     *     prevLabel: string
     * }
     */
    public function getPeriodBounds(int $month, int $year): array
    {
        $month = max(1, min(12, $month));
        $year  = max(2000, min(2100, $year));

        $start = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $end   = date('Y-m-d H:i:s', strtotime($start . ' +1 month'));

        $prevTs    = strtotime($start . ' -1 month');
        $prevStart = date('Y-m-01 00:00:00', $prevTs);
        $prevEnd   = $start;

        $bulanNama = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return [
            'start'     => $start,
            'end'       => $end,
            'prevStart' => $prevStart,
            'prevEnd'   => $prevEnd,
            'label'     => ($bulanNama[$month] ?? (string) $month) . ' ' . $year,
            'prevLabel' => ($bulanNama[(int) date('n', $prevTs)] ?? '') . ' ' . date('Y', $prevTs),
        ];
    }

    /**
     * Pesanan selesai dalam periode (berdasarkan tanggal selesai, bukan created_at).
     *
     * @return list<array<string, mixed>>
     */
    public function getCompletedOrdersInPeriod(string $start, string $end): array
    {
        $orders = $this->db()->table('orders o')
            ->select(
                'o.id_order, o.kode_order, o.total_harga, o.created_at, o.status, '
                    . 'o.jenis_pelanggan, o.is_custom, pg.tgl_diterima, '
                    . 'k.id_katalog, k.nama_produk, k.kategori'
            )
            ->join('katalog k', 'k.id_katalog = o.id_katalog', 'left')
            ->join('pengiriman pg', 'pg.id_order = o.id_order', 'left')
            ->where('o.status', 'selesai')
            ->orderBy('o.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $orders = $this->attachCompletionDates($orders);

        return $this->filterSelesaiByCompletionDate($orders, $start, $end);
    }

    public function countCancelledOrdersInPeriod(string $start, string $end): int
    {
        return $this->db()->table('orders')
            ->where('status', 'dibatalkan')
            ->where('created_at >=', $start)
            ->where('created_at <', $end)
            ->countAllResults();
    }

    /**
     * @return array<string, mixed>
     */
    public function buildOwnerReport(int $month, int $year): array
    {
        $period  = $this->getPeriodBounds($month, $year);
        $current = $this->getCompletedOrdersInPeriod($period['start'], $period['end']);
        $prev    = $this->getCompletedOrdersInPeriod($period['prevStart'], $period['prevEnd']);

        $dariTgl       = substr($period['start'], 0, 10);
        $sampaiTgl     = date('Y-m-d', strtotime($period['start'] . ' +1 month -1 day'));
        $prevDariTgl   = substr($period['prevStart'], 0, 10);
        $prevSampaiTgl = date('Y-m-d', strtotime($period['prevStart'] . ' +1 month -1 day'));

        $totalPendapatan = getTotalPendapatanPeriode($dariTgl, $sampaiTgl);
        $prevPendapatan  = getTotalPendapatanPeriode($prevDariTgl, $prevSampaiTgl);
        $growthOrders    = $this->percentGrowth(count($current), count($prev));
        $growthRevenue   = $this->percentGrowth($totalPendapatan, $prevPendapatan);
        $avgSelesaiHari  = $this->averageCompletionDays($current);

        $rekapKategori = $this->buildCategorySummary($current);
        $topProduk     = $this->buildTopProducts($current, 5);
        $chartDaily    = $this->buildDailyPaymentRevenueChart($month, $year);
        $segmenPelanggan  = $this->buildPelangganSegment($current);
        $segmenPemesanan  = $this->buildPemesananSegment($current);
        $pesananDibatalkan = $this->countCancelledOrdersInPeriod($period['start'], $period['end']);

        return [
            'period'          => $period,
            'month'           => $month,
            'year'            => $year,
            'totalPesanan'    => count($current),
            'totalPendapatan' => $totalPendapatan,
            'growthOrders'    => $growthOrders,
            'growthRevenue'   => $growthRevenue,
            'avgSelesaiHari'  => $avgSelesaiHari,
            'pesananDibatalkan' => $pesananDibatalkan,
            'rekapKategori'   => $rekapKategori,
            'topProduk'       => $topProduk,
            'segmenPelanggan' => $segmenPelanggan,
            'segmenPemesanan' => $segmenPemesanan,
            'chartLabels'     => $chartDaily['labels'],
            'chartValues'     => $chartDaily['values'],
            'chartCounts'     => $chartDaily['counts'],
            'orders'          => $current,
        ];
    }

    /**
     * @param list<array<string, mixed>> $orders
     */
    private function sumRevenue(array $orders): float
    {
        $sum = 0.0;
        foreach ($orders as $row) {
            $sum += (float) ($row['total_harga'] ?? 0);
        }

        return $sum;
    }

    private function percentGrowth(float|int $current, float|int $previous): ?float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Estimasi hari selesai: selisih created_at order dan verifikasi pelunasan terakhir.
     *
     * @param list<array<string, mixed>> $orders
     */
    private function averageCompletionDays(array $orders): ?float
    {
        if ($orders === []) {
            return null;
        }

        $orderIds = array_map(static fn($o) => (int) ($o['id_order'] ?? 0), $orders);
        $orderIds = array_values(array_filter($orderIds));

        if ($orderIds === []) {
            return null;
        }

        $rows = $this->db()->table('payments')
            ->select('id_order, MAX(tgl_verifikasi) AS selesai_bayar')
            ->whereIn('id_order', $orderIds)
            ->where('status', 'terverifikasi')
            ->groupBy('id_order')
            ->get()
            ->getResultArray();

        $selesaiMap = [];
        foreach ($rows as $row) {
            $selesaiMap[(int) $row['id_order']] = (string) ($row['selesai_bayar'] ?? '');
        }

        $totalDays = 0;
        $count     = 0;
        foreach ($orders as $order) {
            $idOrder = (int) ($order['id_order'] ?? 0);
            $created = strtotime((string) ($order['created_at'] ?? ''));
            $selesai = $selesaiMap[$idOrder] ?? '';
            $endTs   = $selesai !== '' ? strtotime($selesai) : false;

            if ($created > 0 && $endTs !== false && $endTs >= $created) {
                $totalDays += (int) floor(($endTs - $created) / 86400);
                $count++;
            }
        }

        if ($count === 0) {
            return null;
        }

        return round($totalDays / $count, 1);
    }

    /**
     * @param list<array<string, mixed>> $current
     * @return list<array<string, mixed>>
     */
    private function buildCategorySummary(array $current): array
    {
        $currentMap = $this->aggregateByCategory($current);
        $result     = [];

        foreach ($currentMap as $key => $row) {
            $jumlah     = (int) $row['count'];
            $pendapatan = (float) $row['revenue'];
            $result[]   = [
                'kategori'   => $key,
                'label'      => self::KATEGORI_LABELS[$key] ?? ucwords(str_replace('_', ' ', $key)),
                'jumlah'     => $jumlah,
                'pendapatan' => $pendapatan,
                'rata_rata'  => $jumlah > 0 ? round($pendapatan / $jumlah, 0) : 0.0,
            ];
        }

        usort($result, static fn($a, $b) => ($b['pendapatan'] <=> $a['pendapatan']));

        return $result;
    }

    /**
     * @param list<array<string, mixed>> $orders
     * @return array<string, array{count: int, revenue: float}>
     */
    private function aggregateByCategory(array $orders): array
    {
        $map = [];
        foreach ($orders as $order) {
            $key = (string) ($order['kategori'] ?? 'lainnya');
            if (!isset($map[$key])) {
                $map[$key] = ['count' => 0, 'revenue' => 0.0];
            }
            $map[$key]['count']++;
            $map[$key]['revenue'] += (float) ($order['total_harga'] ?? 0);
        }

        return $map;
    }

    /**
     * @param list<array<string, mixed>> $orders
     * @return list<array<string, mixed>>
     */
    private function buildTopProducts(array $orders, int $limit): array
    {
        $map = [];
        foreach ($orders as $order) {
            $id = (int) ($order['id_katalog'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            if (!isset($map[$id])) {
                $map[$id] = [
                    'id_katalog'  => $id,
                    'nama_produk' => (string) ($order['nama_produk'] ?? '-'),
                    'jumlah'      => 0,
                    'pendapatan'  => 0.0,
                ];
            }
            $map[$id]['jumlah']++;
            $map[$id]['pendapatan'] += (float) ($order['total_harga'] ?? 0);
        }

        $list = array_values($map);
        usort($list, static fn($a, $b) => ($b['jumlah'] <=> $a['jumlah']) ?: ($b['pendapatan'] <=> $a['pendapatan']));
        $list = array_slice($list, 0, $limit);

        $maxJumlah = $list[0]['jumlah'] ?? 1;
        foreach ($list as &$item) {
            $item['progress'] = $maxJumlah > 0
                ? (int) round(((int) $item['jumlah'] / $maxJumlah) * 100)
                : 0;
        }
        unset($item);

        return $list;
    }

    /**
     * Grafik pemasukan harian berdasarkan tgl_verifikasi payment.
     *
     * @return array{labels: list<string>, values: list<float>, counts: list<int>}
     */
    private function buildDailyPaymentRevenueChart(int $month, int $year): array
    {
        $start      = sprintf('%04d-%02d-01', $year, $month);
        $endDay     = date('Y-m-t', strtotime($start));
        $bounds     = metricPeriodBounds($start, $endDay);
        $daysInMonth = (int) date('t', strtotime($start));

        $rows = $this->db()->table('payments')
            ->select('DAY(tgl_verifikasi) AS hari, SUM(nominal) AS total, COUNT(*) AS cnt', false)
            ->where('status', 'terverifikasi')
            ->where('tgl_verifikasi >=', $bounds['start'])
            ->where('tgl_verifikasi <=', $bounds['end'])
            ->groupBy('DAY(tgl_verifikasi)', false)
            ->get()
            ->getResultArray();

        $dailyRevenue = [];
        $dailyCount   = [];
        foreach ($rows as $row) {
            $day                  = (int) ($row['hari'] ?? 0);
            $dailyRevenue[$day]   = (float) ($row['total'] ?? 0);
            $dailyCount[$day]     = (int) ($row['cnt'] ?? 0);
        }

        $labels = [];
        $values = [];
        $counts = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $labels[] = (string) $d;
            $values[] = (float) ($dailyRevenue[$d] ?? 0);
            $counts[] = (int) ($dailyCount[$d] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values, 'counts' => $counts];
    }

    /**
     * @param list<array<string, mixed>> $orders
     * @return array{labels: list<string>, values: list<float>, counts: list<int>}
     */
    private function buildDailyRevenueChart(array $orders, int $month, int $year): array
    {
        $daysInMonth = (int) date('t', strtotime(sprintf('%04d-%02d-01', $year, $month)));
        $labels      = [];
        $values      = [];
        $counts      = [];

        $dailyRevenue = [];
        $dailyCount   = [];
        foreach ($orders as $order) {
            $day = (int) date('j', strtotime((string) ($order['created_at'] ?? 'now')));
            $dailyRevenue[$day] = ($dailyRevenue[$day] ?? 0) + (float) ($order['total_harga'] ?? 0);
            $dailyCount[$day]   = ($dailyCount[$day] ?? 0) + 1;
        }

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $labels[] = (string) $d;
            $values[] = (float) ($dailyRevenue[$d] ?? 0);
            $counts[] = (int) ($dailyCount[$d] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values, 'counts' => $counts];
    }

    /**
     * Segmen jenis pelanggan: perusahaan vs perseorangan.
     *
     * @param list<array<string, mixed>> $orders
     * @return array<string, mixed>
     */
    private function buildPelangganSegment(array $orders): array
    {
        return $this->buildSegmentGroup($orders, static function (array $order): string {
            $jenis = (string) ($order['jenis_pelanggan'] ?? '');

            return $jenis === 'perusahaan' ? 'perusahaan' : 'perseorangan';
        }, [
            'perusahaan'   => ['label' => 'Perusahaan', 'color' => '#051747'],
            'perseorangan' => ['label' => 'Perseorangan', 'color' => '#2E5CE6'],
        ]);
    }

    /**
     * Segmen jenis pesanan: standar (katalog) vs custom.
     *
     * @param list<array<string, mixed>> $orders
     * @return array<string, mixed>
     */
    private function buildPemesananSegment(array $orders): array
    {
        return $this->buildSegmentGroup($orders, static function (array $order): string {
            return (int) ($order['is_custom'] ?? 0) === 1 ? 'custom' : 'standar';
        }, [
            'standar' => ['label' => 'Standar (Katalog)', 'color' => '#10B981'],
            'custom'  => ['label' => 'Custom', 'color' => '#F59E0B'],
        ]);
    }

    /**
     * @param list<array<string, mixed>> $orders
     * @param callable(array<string, mixed>): string $keyResolver
     * @param array<string, array{label: string, color: string}> $definitions
     * @return array<string, mixed>
     */
    private function buildSegmentGroup(array $orders, callable $keyResolver, array $definitions): array
    {
        $buckets = [];
        foreach ($definitions as $key => $meta) {
            $buckets[$key] = [
                'key'       => $key,
                'label'     => $meta['label'],
                'color'     => $meta['color'],
                'count'     => 0,
                'revenue'   => 0.0,
                'pct_count' => 0.0,
            ];
        }

        $totalCount = count($orders);

        foreach ($orders as $order) {
            $key = $keyResolver($order);
            if (!isset($buckets[$key])) {
                continue;
            }
            $buckets[$key]['count']++;
            $buckets[$key]['revenue'] += (float) ($order['total_harga'] ?? 0);
        }

        $slices = array_values($buckets);
        foreach ($slices as &$slice) {
            $slice['pct_count'] = $totalCount > 0
                ? round(($slice['count'] / $totalCount) * 100, 1)
                : 0.0;
            $slice['rata_rata'] = (int) $slice['count'] > 0
                ? round($slice['revenue'] / $slice['count'], 0)
                : 0.0;
        }
        unset($slice);

        return [
            'totalCount'   => $totalCount,
            'totalRevenue' => $this->sumRevenue($orders),
            'slices'       => $slices,
            'chartLabels'  => array_column($slices, 'label'),
            'chartCounts'  => array_map(static fn($s) => (int) $s['count'], $slices),
            'chartColors'  => array_column($slices, 'color'),
        ];
    }

    public function kategoriLabel(string $key): string
    {
        return self::KATEGORI_LABELS[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    public function jenisPelangganLabel(string $jenis): string
    {
        return match ($jenis) {
            'perusahaan'   => 'Perusahaan',
            'perseorangan' => 'Perseorangan',
            default        => ucwords(str_replace('_', ' ', $jenis)),
        };
    }

    public function metodePengirimanLabel(string $metode): string
    {
        return match ($metode) {
            'kurir'         => 'Kurir',
            'ambil_sendiri' => 'Ambil Sendiri',
            default         => ucwords(str_replace('_', ' ', $metode)),
        };
    }

    public function tipePesananLabel(int $isCustom): string
    {
        return $isCustom === 1 ? 'Custom' : 'Standar';
    }

    public function paymentJenisLabel(string $jenis): string
    {
        return match ($jenis) {
            'dp'        => 'DP',
            'pelunasan' => 'Pelunasan',
            default     => ucwords($jenis),
        };
    }

    /**
     * Laporan keuangan: pemasukan terverifikasi per periode + antrian menunggu real-time.
     *
     * @return array<string, mixed>
     */
    public function buildKeuanganReport(
        string $dateFrom,
        string $dateTo,
        string $jenisFilter,
        string $statusFilter
    ): array {
        $range        = $this->resolveAdminDateRange($dateFrom, $dateTo);
        $jenisFilter  = in_array($jenisFilter, self::KEUANGAN_JENIS_FILTERS, true) ? $jenisFilter : 'semua';
        $statusFilter = in_array($statusFilter, self::KEUANGAN_STATUS_FILTERS, true) ? $statusFilter : 'semua';

        $verifiedInPeriod = $this->fetchVerifiedPaymentsInPeriod($range, $jenisFilter);
        $pemasukanDp      = $this->sumPaymentNominal(array_values(array_filter(
            $verifiedInPeriod,
            static fn($p) => ($p['jenis'] ?? '') === 'dp'
        )));
        $pemasukanPelunasan = $this->sumPaymentNominal(array_values(array_filter(
            $verifiedInPeriod,
            static fn($p) => ($p['jenis'] ?? '') === 'pelunasan'
        )));

        $menungguBreakdown = $this->countMenungguBreakdown($jenisFilter);

        $dariTgl   = substr($range['start'], 0, 10);
        $sampaiTgl = substr($range['endExclusive'], 0, 10);
        $sampaiTgl = date('Y-m-d', strtotime($sampaiTgl . ' -1 day'));
        $totalPemasukan = $jenisFilter === 'semua'
            ? getTotalPendapatanPeriode($dariTgl, $sampaiTgl)
            : $this->sumPaymentNominal($verifiedInPeriod);

        return [
            'range'           => $range,
            'jenisFilter'     => $jenisFilter,
            'statusFilter'    => $statusFilter,
            'summaryCards'    => [
                'totalPemasukan'      => $totalPemasukan,
                'pemasukanDp'         => $pemasukanDp,
                'pemasukanPelunasan'  => $pemasukanPelunasan,
                'menungguVerifikasi'  => $menungguBreakdown['total'],
                'menungguDp'          => $menungguBreakdown['dp'],
                'menungguPelunasan'   => $menungguBreakdown['pelunasan'],
            ],
            'rekapJenis'      => $this->buildKeuanganJenisSummary($verifiedInPeriod, $jenisFilter),
            'daftarTransaksi' => $this->filterKeuanganPaymentList($range, $jenisFilter, $statusFilter),
        ];
    }

    /**
     * Laporan operasional admin: filter rentang tanggal, status, kategori.
     *
     * @return array<string, mixed>
     */
    public function buildAdminReport(
        string $dateFrom,
        string $dateTo,
        string $statusFilter,
        string $kategoriFilter,
        string $jenisPelangganFilter = 'semua',
        string $tipePesananFilter = 'semua'
    ): array {
        $range          = $this->resolveAdminDateRange($dateFrom, $dateTo);
        $statusFilter   = in_array($statusFilter, self::ADMIN_STATUS_FILTERS, true) ? $statusFilter : 'semua';
        $kategoriFilter = $kategoriFilter === 'semua' ? '' : $kategoriFilter;

        $allOrders = $this->attachCompletionDates(
            $this->fetchAdminOrdersBase($kategoriFilter, $jenisPelangganFilter, $tipePesananFilter)
        );

        $selesaiInPeriod = $this->filterSelesaiByCompletionDate(
            $allOrders,
            $range['start'],
            $range['endExclusive']
        );

        $summaryCards  = $this->buildAdminSummaryCards($allOrders, $range);
        $rekapKategori = $this->buildAdminCategorySummary($selesaiInPeriod);
        $potensiAktif  = $this->sumPotensiAktif($allOrders, $range);
        $daftarPesanan = $this->filterAdminOrderList($allOrders, $range, $statusFilter);

        return [
            'range'                => $range,
            'statusFilter'         => $statusFilter,
            'kategoriFilter'       => $kategoriFilter === '' ? 'semua' : $kategoriFilter,
            'jenisPelangganFilter' => $jenisPelangganFilter,
            'tipePesananFilter'    => $tipePesananFilter,
            'summaryCards'         => $summaryCards,
            'potensiAktif'         => $potensiAktif,
            'rekapKategori'        => $rekapKategori,
            'daftarPesanan'        => $daftarPesanan,
        ];
    }

    /**
     * @return array{
     *     dateFrom: string,
     *     dateTo: string,
     *     start: string,
     *     endExclusive: string,
     *     label: string
     * }
     */
    public function resolveAdminDateRange(string $dateFrom, string $dateTo): array
    {
        $fromTs = strtotime($dateFrom) ?: strtotime(date('Y-m-01'));
        $toTs   = strtotime($dateTo) ?: time();

        if ($fromTs > $toTs) {
            [$fromTs, $toTs] = [$toTs, $fromTs];
        }

        $dateFrom = date('Y-m-d', $fromTs);
        $dateTo   = date('Y-m-d', $toTs);

        return [
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
            'start'        => $dateFrom . ' 00:00:00',
            'endExclusive' => date('Y-m-d', strtotime($dateTo . ' +1 day')) . ' 00:00:00',
            'label'        => date('d M Y', $fromTs) . ' – ' . date('d M Y', $toTs),
            'labelShort'   => date('j M', $fromTs) . ' – ' . date('j M Y', $toTs),
        ];
    }

    public function buildProduksiReport(string $dateFrom, string $dateTo, string $kategoriFilter): array
    {
        $range          = $this->resolveAdminDateRange($dateFrom, $dateTo);
        $kategoriFilter = $kategoriFilter === 'semua' ? '' : $kategoriFilter;
        $snapshot       = $this->buildProduksiSnapshot($kategoriFilter);

        return [
            'range'         => $range,
            'snapshot'      => $snapshot,
            'aktivitas'     => $this->buildProduksiAktivitasRevisi($range, $kategoriFilter),
            'segmenTahap'   => $this->buildProduksiSegmenTahap($snapshot),
            'daftarPesanan' => $this->fetchProduksiOrdersWithRevisiInPeriod($range, $kategoriFilter),
        ];
    }

    /**
     * Snapshot antrian produksi hari ini (tidak terpengaruh filter periode).
     *
     * @return array{
     *     pesananAktif: int,
     *     tahapDesain: int,
     *     tahapCetakFinish: int,
     *     melewatiDeadline: int,
     *     perStatus: array<string, int>
     * }
     */
    private function buildProduksiSnapshot(string $kategoriFilter = ''): array
    {
        $today = date('Y-m-d');

        $baseOrders = function () use ($kategoriFilter) {
            $builder = $this->db()->table('orders o')
                ->join('katalog k', 'k.id_katalog = o.id_katalog', 'left');

            if ($kategoriFilter !== '') {
                $builder->where('k.kategori', $kategoriFilter);
            }

            return $builder;
        };

        $pesananAktif = (int) $baseOrders()
            ->whereIn('o.status', self::PRODUKSI_PIPELINE)
            ->countAllResults();

        $tahapDesain = (int) $baseOrders()
            ->whereIn('o.status', ['terverifikasi', 'proses_desain', 'proses_revisi'])
            ->countAllResults();

        $tahapCetakFinish = (int) $baseOrders()
            ->whereIn('o.status', ['proses_cetak', 'finishing'])
            ->countAllResults();

        $melewatiDeadline = (int) $baseOrders()
            ->whereIn('o.status', self::PRODUKSI_PIPELINE)
            ->where('o.deadline_produksi IS NOT NULL', null, false)
            ->where('o.deadline_produksi <', $today)
            ->countAllResults();

        $perStatus = [];
        foreach (self::PRODUKSI_PIPELINE as $status) {
            $perStatus[$status] = (int) $baseOrders()
                ->where('o.status', $status)
                ->countAllResults();
        }

        return [
            'pesananAktif'     => $pesananAktif,
            'tahapDesain'      => $tahapDesain,
            'tahapCetakFinish' => $tahapCetakFinish,
            'melewatiDeadline' => $melewatiDeadline,
            'perStatus'        => $perStatus,
        ];
    }

    /**
     * @param array<string, string> $range
     * @return array{
     *     totalDraft: int,
     *     acc: int,
     *     ditolak: int,
     *     diajukanRevisi: int,
     *     uploaded: int,
     *     menungguRespon: int,
     *     rataRevisi: float|null
     * }
     */
    private function buildProduksiAktivitasRevisi(array $range, string $kategoriFilter): array
    {
        $totalDraft     = $this->countRevisiInPeriod($range, $kategoriFilter);
        $acc            = $this->countRevisiInPeriod($range, $kategoriFilter, 'acc');
        $ditolak        = $this->countRevisiInPeriod($range, $kategoriFilter, 'ditolak');
        $diajukanRevisi = $this->countRevisiInPeriod($range, $kategoriFilter, 'diajukan_revisi');
        $uploaded       = $this->countRevisiInPeriod($range, $kategoriFilter, 'uploaded');

        // Rata-rata penolakan (status ditolak) per pesanan yang punya aktivitas draft dalam periode.
        $avgRows = $this->revisiPeriodBuilder($range, $kategoriFilter)
            ->select('r.id_order, SUM(CASE WHEN r.status = \'ditolak\' THEN 1 ELSE 0 END) AS jumlah', false)
            ->groupBy('r.id_order')
            ->get()
            ->getResultArray();

        $rataRevisi = null;
        if ($avgRows !== []) {
            $counts = array_map(static fn ($row) => (int) ($row['jumlah'] ?? 0), $avgRows);
            $rataRevisi = round(array_sum($counts) / count($counts), 1);
        }

        return [
            'totalDraft'     => $totalDraft,
            'acc'            => $acc,
            'ditolak'        => $ditolak,
            'diajukanRevisi' => $diajukanRevisi,
            'uploaded'       => $uploaded,
            'menungguRespon' => $uploaded,
            'rataRevisi'     => $rataRevisi,
        ];
    }

    /**
     * @param array<string, string> $range
     */
    private function revisiPeriodBuilder(array $range, string $kategoriFilter): BaseBuilder
    {
        $builder = $this->db()->table('revisi_desain r')
            ->join('orders o', 'o.id_order = r.id_order')
            ->join('katalog k', 'k.id_katalog = o.id_katalog', 'left')
            ->where('r.created_at >=', $range['start'])
            ->where('r.created_at <', $range['endExclusive']);

        if ($kategoriFilter !== '') {
            $builder->where('k.kategori', $kategoriFilter);
        }

        return $builder;
    }

    /**
     * @param array<string, string> $range
     */
    private function countRevisiInPeriod(array $range, string $kategoriFilter, ?string $status = null): int
    {
        $builder = $this->revisiPeriodBuilder($range, $kategoriFilter);

        if ($status !== null) {
            $builder->where('r.status', $status);
        }

        return (int) $builder->countAllResults();
    }

    /**
     * @param array{perStatus: array<string, int>} $snapshot
     * @return array<string, mixed>
     */
    private function buildProduksiSegmenTahap(array $snapshot): array
    {
        $definitions = [
            'terverifikasi' => ['label' => 'Terverifikasi', 'color' => '#2E5CE6'],
            'proses_desain' => ['label' => 'Proses Desain', 'color' => '#8B5CF6'],
            'proses_revisi' => ['label' => 'Proses Revisi', 'color' => '#F59E0B'],
            'proses_cetak'  => ['label' => 'Proses Cetak', 'color' => '#10B981'],
            'finishing'     => ['label' => 'Finishing', 'color' => '#051747'],
        ];

        $orders = [];
        foreach (($snapshot['perStatus'] ?? []) as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $orders[] = ['status' => $status, 'total_harga' => 0];
            }
        }

        return $this->buildSegmentGroup(
            $orders,
            static fn(array $order): string => (string) ($order['status'] ?? ''),
            $definitions
        );
    }

    /**
     * Pesanan dengan minimal satu aktivitas revisi dalam periode (Opsi A).
     *
     * @param array<string, string> $range
     * @return list<array<string, mixed>>
     */
    private function fetchProduksiOrdersWithRevisiInPeriod(array $range, string $kategoriFilter): array
    {
        $idRows = $this->revisiPeriodBuilder($range, $kategoriFilter)
            ->select('r.id_order')
            ->distinct()
            ->get()
            ->getResultArray();

        $orderIds = array_values(array_unique(array_filter(array_map(
            static fn ($row) => (int) ($row['id_order'] ?? 0),
            $idRows
        ))));

        if ($orderIds === []) {
            return [];
        }

        $orders = $this->db()->table('orders o')
            ->select(
                'o.id_order, o.kode_order, o.status, o.deadline_produksi AS deadline, o.created_at, o.sisa_kuota, o.kuota_revisi, '
                    . 'o.is_custom, k.nama_produk, k.kategori, u.nama AS nama_pelanggan'
            )
            ->join('katalog k', 'k.id_katalog = o.id_katalog', 'left')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan', 'left')
            ->join('users u', 'u.id_user = p.id_user', 'left')
            ->whereIn('o.id_order', $orderIds)
            ->orderBy('o.deadline_produksi', 'ASC')
            ->get()
            ->getResultArray();

        $revisiStats = $this->db()->table('revisi_desain r')
            ->select(
                'r.id_order, '
                    . 'SUM(CASE WHEN r.status = \'ditolak\' THEN 1 ELSE 0 END) AS jumlah_penolakan, '
                    . 'MAX(r.versi) AS versi_terakhir',
                false
            )
            ->whereIn('r.id_order', $orderIds)
            ->where('r.created_at >=', $range['start'])
            ->where('r.created_at <', $range['endExclusive'])
            ->groupBy('r.id_order')
            ->get()
            ->getResultArray();

        $statsMap = [];
        foreach ($revisiStats as $row) {
            $statsMap[(int) ($row['id_order'] ?? 0)] = $row;
        }

        $accRows = $this->db()->table('revisi_desain r')
            ->select('r.id_order, MAX(r.versi) AS versi_dipilih', false)
            ->whereIn('r.id_order', $orderIds)
            ->where('r.status', 'acc')
            ->groupBy('r.id_order')
            ->get()
            ->getResultArray();

        $accMap = [];
        foreach ($accRows as $row) {
            $accMap[(int) ($row['id_order'] ?? 0)] = (int) ($row['versi_dipilih'] ?? 0);
        }

        $today = date('Y-m-d');
        foreach ($orders as &$order) {
            $idOrder = (int) ($order['id_order'] ?? 0);
            $stat    = $statsMap[$idOrder] ?? [];
            $order['jumlah_revisi_periode'] = (int) ($stat['jumlah_penolakan'] ?? 0);
            $order['versi_terakhir']        = (int) ($stat['versi_terakhir'] ?? 0);
            $order['versi_dipilih']         = $accMap[$idOrder] ?? 0;
            $deadline = trim((string) ($order['deadline'] ?? ''));
            $status   = (string) ($order['status'] ?? '');
            $order['is_telat'] = $deadline !== ''
                && $deadline < $today
                && in_array($status, self::PRODUKSI_PIPELINE, true);
        }
        unset($order);

        return $orders;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchAdminOrdersBase(
        string $kategoriFilter,
        string $jenisPelangganFilter = 'semua',
        string $tipePesananFilter = 'semua'
    ): array {
        $builder = $this->db()->table('orders o')
            ->select(
                'o.id_order, o.kode_order, o.total_harga, o.created_at, o.deadline_produksi AS deadline, o.status, '
                    . 'o.jenis_pelanggan, o.metode_pengiriman, o.is_custom, k.nama_produk, k.kategori, '
                    . 'u.nama AS nama_pelanggan, pg.tgl_diterima'
            )
            ->join('katalog k', 'k.id_katalog = o.id_katalog', 'left')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan', 'left')
            ->join('users u', 'u.id_user = p.id_user', 'left')
            ->join('pengiriman pg', 'pg.id_order = o.id_order', 'left')
            ->orderBy('o.created_at', 'DESC');

        if ($kategoriFilter !== '') {
            $builder->where('k.kategori', $kategoriFilter);
        }

        if (in_array($jenisPelangganFilter, ['perusahaan', 'perseorangan'], true)) {
            $builder->where('o.jenis_pelanggan', $jenisPelangganFilter);
        }

        if ($tipePesananFilter === 'custom') {
            $builder->where('o.is_custom', 1);
        } elseif ($tipePesananFilter === 'standar') {
            $builder->where('o.is_custom', 0);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * @param list<array<string, mixed>> $orders
     * @return list<array<string, mixed>>
     */
    private function attachCompletionDates(array $orders): array
    {
        if ($orders === []) {
            return [];
        }

        $orderIds = array_values(array_filter(array_map(
            static fn($o) => (int) ($o['id_order'] ?? 0),
            $orders
        )));

        $pelunasanMap = [];
        if ($orderIds !== []) {
            $rows = $this->db()->table('payments')
                ->select('id_order, MAX(tgl_verifikasi) AS tgl_pelunasan')
                ->whereIn('id_order', $orderIds)
                ->where('jenis', 'pelunasan')
                ->where('status', 'terverifikasi')
                ->groupBy('id_order')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $pelunasanMap[(int) $row['id_order']] = (string) ($row['tgl_pelunasan'] ?? '');
            }
        }

        foreach ($orders as &$order) {
            $idOrder = (int) ($order['id_order'] ?? 0);
            $jenis   = (string) ($order['jenis_pelanggan'] ?? '');

            if ($jenis === 'perusahaan') {
                $tgl = $pelunasanMap[$idOrder] ?? '';
                $order['tgl_selesai'] = $tgl !== '' ? $tgl : null;
            } else {
                $tgl = trim((string) ($order['tgl_diterima'] ?? ''));
                $order['tgl_selesai'] = $tgl !== '' ? $tgl : null;
            }
        }
        unset($order);

        return $orders;
    }

    /**
     * @param list<array<string, mixed>> $orders
     * @return list<array<string, mixed>>
     */
    private function filterSelesaiByCompletionDate(array $orders, string $start, string $endExclusive): array
    {
        $result = [];
        foreach ($orders as $order) {
            if ((string) ($order['status'] ?? '') !== 'selesai') {
                continue;
            }
            $tgl = (string) ($order['tgl_selesai'] ?? '');
            if ($tgl === '') {
                continue;
            }
            if ($tgl >= $start && $tgl < $endExclusive) {
                $result[] = $order;
            }
        }

        return $result;
    }

    /**
     * @param list<array<string, mixed>> $orders
     * @param array<string, string>     $range
     * @return list<array<string, mixed>>
     */
    private function filterAdminOrderList(array $orders, array $range, string $statusFilter): array
    {
        $result = [];
        foreach ($orders as $order) {
            $status = (string) ($order['status'] ?? '');
            if (!$this->matchesAdminStatusFilter($status, $statusFilter)) {
                continue;
            }

            if ($status === 'selesai') {
                $tgl = (string) ($order['tgl_selesai'] ?? '');
                if ($tgl === '' || $tgl < $range['start'] || $tgl >= $range['endExclusive']) {
                    continue;
                }
            } else {
                $created = (string) ($order['created_at'] ?? '');
                if ($created === '' || $created < $range['start'] || $created >= $range['endExclusive']) {
                    continue;
                }
            }

            $result[] = $order;
        }

        return $result;
    }

    private function matchesAdminStatusFilter(string $status, string $filter): bool
    {
        return match ($filter) {
            'selesai'    => $status === 'selesai',
            'proses'     => $status !== 'selesai' && $status !== 'dibatalkan',
            'dibatalkan' => $status === 'dibatalkan',
            default      => true,
        };
    }

    /**
     * @param list<array<string, mixed>> $orders
     * @param array<string, string>     $range
     * @return array{
     *     totalPesanan: int,
     *     pesananMasuk: int,
     *     pesananSelesai: int,
     *     pesananDibatalkan: int
     * }
     */
    private function buildAdminSummaryCards(array $orders, array $range): array
    {
        $start        = (string) ($range['start'] ?? '');
        $endExclusive = (string) ($range['endExclusive'] ?? '');

        $totalPesanan      = 0;
        $pesananMasuk      = 0;
        $pesananDibatalkan = 0;

        foreach ($orders as $order) {
            $status  = (string) ($order['status'] ?? '');
            $created = (string) ($order['created_at'] ?? '');

            if ($created !== '' && $created >= $start && $created < $endExclusive) {
                $pesananMasuk++;
            }

            $inPeriod = false;
            if ($status === 'selesai') {
                $tgl = (string) ($order['tgl_selesai'] ?? '');
                $inPeriod = $tgl !== '' && $tgl >= $start && $tgl < $endExclusive;
            } else {
                $inPeriod = $created !== '' && $created >= $start && $created < $endExclusive;
            }

            if (!$inPeriod) {
                continue;
            }

            $totalPesanan++;
            if ($status === 'dibatalkan') {
                $pesananDibatalkan++;
            }
        }

        $dariTgl   = substr($start, 0, 10);
        $sampaiTgl = date('Y-m-d', strtotime($endExclusive . ' -1 day'));

        return [
            'totalPesanan'      => $totalPesanan,
            'pesananMasuk'      => $pesananMasuk,
            'pesananSelesai'    => getPesananSelesaiPeriode($dariTgl, $sampaiTgl),
            'pesananDibatalkan' => $pesananDibatalkan,
        ];
    }

    /**
     * @param list<array<string, mixed>> $orders
     * @param array<string, string>     $range
     */
    private function sumPotensiAktif(array $orders, array $range): float
    {
        $start        = (string) ($range['start'] ?? '');
        $endExclusive = (string) ($range['endExclusive'] ?? '');
        $sum          = 0.0;

        foreach ($orders as $order) {
            $status = (string) ($order['status'] ?? '');
            if ($status === 'selesai' || $status === 'dibatalkan') {
                continue;
            }

            $created = (string) ($order['created_at'] ?? '');
            if ($created === '' || $created < $start || $created >= $endExclusive) {
                continue;
            }

            $sum += (float) ($order['total_harga'] ?? 0);
        }

        return $sum;
    }

    /**
     * @param list<array<string, mixed>> $orders
     * @return array{rows: list<array<string, mixed>>, totals: array<string, float|int>}
     */
    private function buildAdminCategorySummary(array $orders): array
    {
        $currentMap = $this->aggregateByCategory($orders);
        $rows       = [];

        foreach ($currentMap as $key => $row) {
            $jumlah     = (int) $row['count'];
            $pendapatan = (float) $row['revenue'];
            $rows[]     = [
                'kategori'   => $key,
                'label'      => self::KATEGORI_LABELS[$key] ?? ucwords(str_replace('_', ' ', $key)),
                'jumlah'     => $jumlah,
                'pendapatan' => $pendapatan,
                'rata_rata'  => $jumlah > 0 ? round($pendapatan / $jumlah, 0) : 0.0,
            ];
        }

        usort($rows, static fn($a, $b) => ($b['pendapatan'] <=> $a['pendapatan']));

        $totalJumlah     = count($orders);
        $totalPendapatan = $this->sumRevenue($orders);

        return [
            'rows' => $rows,
            'totals' => [
                'jumlah'     => $totalJumlah,
                'pendapatan' => $totalPendapatan,
                'rata_rata'  => $totalJumlah > 0 ? round($totalPendapatan / $totalJumlah, 0) : 0.0,
            ],
        ];
    }

    private function paymentReportSelect(): string
    {
        return 'py.id_payment, py.kode_payment, py.jenis, py.nominal, py.status, '
            . 'py.tgl_upload, py.tgl_verifikasi, o.kode_order, u.nama AS nama_pelanggan, '
            . 'verif.email AS email_verifikator';
    }

    private function applyPaymentReportJoins(BaseBuilder $builder): void
    {
        $builder
            ->join('orders o', 'o.id_order = py.id_order', 'left')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan', 'left')
            ->join('users u', 'u.id_user = p.id_user', 'left')
            ->join('users verif', 'verif.id_user = py.id_verifikator', 'left');
    }

    /**
     * @return array{dp: int, pelunasan: int, total: int}
     */
    private function countMenungguBreakdown(string $jenisFilter): array
    {
        $dp = $this->db()->table('payments')
            ->where('status', 'menunggu')
            ->where('jenis', 'dp')
            ->countAllResults();

        $pelunasan = $this->db()->table('payments')
            ->where('status', 'menunggu')
            ->where('jenis', 'pelunasan')
            ->countAllResults();

        if ($jenisFilter === 'dp') {
            return ['dp' => $dp, 'pelunasan' => 0, 'total' => $dp];
        }
        if ($jenisFilter === 'pelunasan') {
            return ['dp' => 0, 'pelunasan' => $pelunasan, 'total' => $pelunasan];
        }

        return [
            'dp'        => $dp,
            'pelunasan' => $pelunasan,
            'total'     => $dp + $pelunasan,
        ];
    }

    /**
     * @param array<string, string> $range
     * @return list<array<string, mixed>>
     */
    private function fetchVerifiedPaymentsInPeriod(array $range, string $jenisFilter): array
    {
        $builder = $this->db()->table('payments py')
            ->select($this->paymentReportSelect())
            ->where('py.status', 'terverifikasi')
            ->where('py.tgl_verifikasi >=', $range['start'])
            ->where('py.tgl_verifikasi <', $range['endExclusive']);

        $this->applyPaymentReportJoins($builder);

        if ($jenisFilter === 'dp' || $jenisFilter === 'pelunasan') {
            $builder->where('py.jenis', $jenisFilter);
        }

        return $builder->orderBy('py.tgl_verifikasi', 'DESC')->get()->getResultArray();
    }

    private function countMenungguRealtime(string $jenisFilter): int
    {
        return $this->countMenungguBreakdown($jenisFilter)['total'];
    }

    /**
     * @param list<array<string, mixed>> $verified
     * @return array{rows: list<array<string, mixed>>, totals: array<string, float|int>}
     */
    private function buildKeuanganJenisSummary(array $verified, string $jenisFilter): array
    {
        $definitions = [
            'dp'        => 'DP',
            'pelunasan' => 'Pelunasan',
        ];

        if ($jenisFilter === 'dp' || $jenisFilter === 'pelunasan') {
            $definitions = [$jenisFilter => $definitions[$jenisFilter]];
        }

        $rows = [];
        foreach ($definitions as $key => $label) {
            $items  = array_values(array_filter(
                $verified,
                static fn($p) => ($p['jenis'] ?? '') === $key
            ));
            $jumlah = count($items);
            $total  = $this->sumPaymentNominal($items);
            $rows[] = [
                'jenis'     => $key,
                'label'     => $label,
                'jumlah'    => $jumlah,
                'nominal'   => $total,
                'rata_rata' => $jumlah > 0 ? round($total / $jumlah, 0) : 0.0,
            ];
        }

        $totalJumlah  = count($verified);
        $totalNominal = $this->sumPaymentNominal($verified);

        return [
            'rows'   => $rows,
            'totals' => [
                'jumlah'    => $totalJumlah,
                'nominal'   => $totalNominal,
                'rata_rata' => $totalJumlah > 0 ? round($totalNominal / $totalJumlah, 0) : 0.0,
            ],
        ];
    }

    /**
     * @param array<string, string> $range
     * @return list<array<string, mixed>>
     */
    private function filterKeuanganPaymentList(array $range, string $jenisFilter, string $statusFilter): array
    {
        $builder = $this->db()->table('payments py')
            ->select($this->paymentReportSelect())
            ->whereIn('py.status', ['terverifikasi', 'menunggu']);

        $this->applyPaymentReportJoins($builder);

        if ($jenisFilter === 'dp' || $jenisFilter === 'pelunasan') {
            $builder->where('py.jenis', $jenisFilter);
        }

        $all    = $builder->orderBy('py.tgl_upload', 'DESC')->get()->getResultArray();
        $result = [];

        foreach ($all as $payment) {
            $status = (string) ($payment['status'] ?? '');
            if (!$this->matchesKeuanganStatusFilter($status, $statusFilter)) {
                continue;
            }

            if ($status === 'terverifikasi') {
                $tgl = trim((string) ($payment['tgl_verifikasi'] ?? ''));
                if ($tgl === '' || $tgl < $range['start'] || $tgl >= $range['endExclusive']) {
                    continue;
                }
            } else {
                $tgl = trim((string) ($payment['tgl_upload'] ?? ''));
                if ($tgl === '' || $tgl < $range['start'] || $tgl >= $range['endExclusive']) {
                    continue;
                }
            }

            $result[] = $payment;
        }

        return $result;
    }

    private function matchesKeuanganStatusFilter(string $status, string $filter): bool
    {
        return match ($filter) {
            'terverifikasi' => $status === 'terverifikasi',
            'menunggu'      => $status === 'menunggu',
            default         => in_array($status, ['terverifikasi', 'menunggu'], true),
        };
    }

    /**
     * @param list<array<string, mixed>> $payments
     */
    private function sumPaymentNominal(array $payments): float
    {
        $sum = 0.0;
        foreach ($payments as $payment) {
            $sum += (float) ($payment['nominal'] ?? 0);
        }

        return $sum;
    }

    private function db(): BaseConnection
    {
        return \Config\Database::connect();
    }
}
