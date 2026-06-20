<?php

/**
 * @var array<string, mixed> $report
 * @var int                  $filterMonth
 * @var int                  $filterYear
 */
$period          = $report['period'] ?? [];
$growthOrders    = $report['growthOrders'] ?? null;
$growthRevenue   = $report['growthRevenue'] ?? null;
$avgSelesaiHari  = $report['avgSelesaiHari'] ?? null;
$rekapKategori   = $report['rekapKategori'] ?? [];
$topProduk       = $report['topProduk'] ?? [];
$segmenPelanggan = $report['segmenPelanggan'] ?? [];
$segmenPemesanan = $report['segmenPemesanan'] ?? [];
$chartLabels     = $report['chartLabels'] ?? [];
$chartValues     = $report['chartValues'] ?? [];
$chartCounts     = $report['chartCounts'] ?? [];

$bulanOptions = [
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

$currentYear = (int) date('Y');
$tahunOptions = range($currentYear, $currentYear - 5);

$growthOrdersPct = $growthOrders === null
    ? null
    : (($growthOrders > 0 ? '+' : '') . number_format($growthOrders, 1, ',', '.') . '%');

$growthOrdersSuffix = $growthOrders === null
    ? null
    : 'vs ' . ($period['prevLabel'] ?? 'bulan lalu');

$growthOrdersColor = '#051747';
if ($growthOrders !== null) {
    if ($growthOrders > 0) {
        $growthOrdersColor = '#10B981';
    } elseif ($growthOrders < 0) {
        $growthOrdersColor = '#EF4444';
    }
}

$cards = [
    [
        'label'   => 'Pesanan Selesai',
        'value'   => (int) ($report['totalPesanan'] ?? 0),
        'icon'    => 'clipboard',
        'color'   => '#2E5CE6',
        'tooltip' => 'Berdasarkan status pesanan selesai pada periode yang dipilih.',
    ],
    [
        'label'    => 'Total Pendapatan',
        'value'    => 'Rp ' . number_format((float) ($report['totalPendapatan'] ?? 0), 0, ',', '.'),
        'icon'     => 'cash',
        'color'    => '#051747',
        'isText'   => true,
        'subLabel' => (string) ($period['label'] ?? ''),
        'tooltip'  => 'Total pembayaran terverifikasi (DP dan pelunasan) pada periode yang dipilih.',
    ],
    [
        'label'          => 'Pertumbuhan Pesanan',
        'value'          => $growthOrdersPct ?? '—',
        'valueHighlight' => $growthOrdersPct,
        'valueSuffix'    => $growthOrdersSuffix,
        'icon'           => 'chart',
        'color'          => '#051747',
        'valueColor'     => $growthOrdersColor,
        'isText'         => true,
        'tooltip'        => 'Menghitung pertumbuhan pesanan selesai dibandingkan dengan periode sebelumnya.',
    ],
    [
        'label'   => 'Rata-rata Selesai',
        'value'   => $avgSelesaiHari !== null ? number_format((float) $avgSelesaiHari, 1, ',', '.') . ' hari' : '—',
        'icon'    => 'clock',
        'color'   => '#8B5CF6',
        'isText'  => true,
        'tooltip' => 'Rata-rata waktu dari pembuatan pesanan hingga pelunasan terverifikasi (selesai) sesuai periode yang dipilih.',
    ],
    [
        'label'   => 'Pesanan Dibatalkan',
        'value'   => (int) ($report['pesananDibatalkan'] ?? 0),
        'icon'    => 'x-circle',
        'color'   => '#EF4444',
        'tooltip' => 'Jumlah pesanan yang dibatalkan pada periode yang dipilih.',
    ],
];
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Laporan<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Laporan Owner<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-6">
    <h2 class="text-2xl font-extrabold text-[#051747]">Laporan Owner</h2>
    <p class="mt-1 text-sm text-slate-500">
        Ringkasan performa bisnis Z'Plack <?= esc((string) ($period['label'] ?? '-')) ?>
    </p>
</div>

<form method="get" action="<?= esc(site_url('laporan')) ?>" class="flex flex-wrap gap-3 items-end mb-6">
    <div>
        <label for="bulan" class="block text-xs font-semibold text-slate-500 mb-1.5">Bulan</label>
        <select id="bulan" name="bulan" class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm min-w-[140px] focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
            <?php foreach ($bulanOptions as $num => $nama): ?>
                <option value="<?= esc((string) $num) ?>" <?= $filterMonth === $num ? 'selected' : '' ?>>
                    <?= esc($nama) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="tahun" class="block text-xs font-semibold text-slate-500 mb-1.5">Tahun</label>
        <select id="tahun" name="tahun" class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm min-w-[100px] focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
            <?php foreach ($tahunOptions as $thn): ?>
                <option value="<?= esc((string) $thn) ?>" <?= $filterYear === $thn ? 'selected' : '' ?>>
                    <?= esc((string) $thn) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="bg-[#051747] text-white rounded-full text-sm font-bold px-5 py-2.5 hover:bg-[#2E5CE6] transition-colors">
        Tampilkan
    </button>
    <a href="<?= esc(site_url('laporan/export?' . http_build_query(['bulan' => $filterMonth, 'tahun' => $filterYear]))) ?>"
        class="inline-flex items-center justify-center border-2 border-[#051747] text-[#051747] rounded-full text-sm font-bold px-5 py-2.5 hover:bg-[#051747] hover:text-white transition-colors">
        Ekspor Excel
    </a>
</form>

<?= view('dashboard/_partials/summary_cards', [
    'cards'     => $cards,
    'gridClass' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5',
]) ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <?= view('laporan/_partials/segment_donut', [
        'title'   => 'Segmen Pelanggan',
        'chartId' => 'segmenPelangganChart',
        'segment' => $segmenPelanggan,
    ]) ?>
    <?= view('laporan/_partials/segment_donut', [
        'title'   => 'Segmen Pemesanan',
        'chartId' => 'segmenPemesananChart',
        'segment' => $segmenPemesanan,
    ]) ?>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-6">
    <h3 class="text-base font-bold text-[#051747] mb-1">
        Pendapatan Harian Bulan <?= esc((string) ($period['label'] ?? '')) ?>
        <span class="font-normal text-slate-400 text-sm">· berdasarkan tgl verifikasi pembayaran</span>
    </h3>
    <p class="text-xs text-slate-500 mt-0.5 mb-4">Nilai per hari dari pesanan yang berstatus selesai dalam periode ini.</p>
    <canvas id="laporanRevenueChart" height="90"></canvas>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-[#051747]">Rekap Per Kategori Bulan <?= esc((string) ($period['label'] ?? '')) ?></h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Berdasarkan <strong>Pesanan Selesai</strong>
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#051747] text-white text-xs uppercase">
                        <th class="px-4 py-3 text-left font-semibold">Kategori</th>
                        <th class="px-4 py-3 text-left font-semibold">Jumlah</th>
                        <th class="px-4 py-3 text-left font-semibold">Pendapatan</th>
                        <th class="px-4 py-3 text-left font-semibold">Rata-rata/Pesanan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rekapKategori === []): ?>
                        <tr>
                            <td colspan="4" class="py-12 text-center text-slate-500 text-sm">
                                Tidak ada data kategori pada periode ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rekapKategori as $row): ?>
                            <tr class="border-b border-slate-100 hover:bg-[#F8FAFF]">
                                <td class="px-4 py-3 font-medium text-[#051747]"><?= esc((string) ($row['label'] ?? '-')) ?></td>
                                <td class="px-4 py-3 text-slate-600"><?= esc((string) ($row['jumlah'] ?? 0)) ?></td>
                                <td class="px-4 py-3 font-semibold text-[#051747]">
                                    Rp <?= esc(number_format((float) ($row['pendapatan'] ?? 0), 0, ',', '.')) ?>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    Rp <?= esc(number_format((float) ($row['rata_rata'] ?? 0), 0, ',', '.')) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-base font-bold text-[#051747] mb-4">Top 5 Katalog Terlaris Bulan <?= esc((string) ($period['label'] ?? '')) ?></h3>
        <?php if ($topProduk === []): ?>
            <p class="text-sm py-8 text-center text-slate-400">Belum ada data produk pada periode ini.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($topProduk as $index => $produk): ?>
                    <div>
                        <div class="flex items-start justify-between gap-3 mb-1.5">
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-[#051747]">
                                    <span class="text-[#2E5CE6] mr-1"><?= esc((string) ($index + 1)) ?>.</span>
                                    <?= esc((string) ($produk['nama_produk'] ?? '-')) ?>
                                </p>
                                <p class="text-xs mt-0.5 text-slate-400">
                                    <?= esc((string) ($produk['jumlah'] ?? 0)) ?> pesanan ·
                                    Rp <?= esc(number_format((float) ($produk['pendapatan'] ?? 0), 0, ',', '.')) ?>
                                </p>
                            </div>
                        </div>
                        <div class="h-2 rounded-full overflow-hidden bg-slate-100">
                            <div class="h-full bg-[#2E5CE6] rounded-full transition-all" style="width: <?= esc((string) ($produk['progress'] ?? 0)) ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<p class="text-xs text-slate-400 italic">
    *Pesanan selesai dihitung dari <strong>tanggal selesai</strong> (konfirmasi terima / verifikasi pelunasan), bukan tanggal pesanan dibuat.
    Total pendapatan dan grafik harian menggunakan pembayaran <strong>terverifikasi</strong> berdasarkan <code class="text-[11px]">tgl_verifikasi</code>.
    Rekap kategori dan produk terlaris memakai <code class="text-[11px]">total_harga</code> pesanan selesai dalam periode yang sama.
</p>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function initSegmentDonut(canvasId, labels, counts, colors) {
        const el = document.getElementById(canvasId);
        if (!el || !labels.length) {
            return;
        }

        const total = counts.reduce((sum, val) => sum + val, 0);
        if (total <= 0) {
            return;
        }

        new Chart(el.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#FFFFFF',
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.parsed || 0;
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : '0';
                                return ctx.label + ': ' + val + ' pesanan (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    initSegmentDonut(
        'segmenPelangganChart',
        <?= json_encode($segmenPelanggan['chartLabels'] ?? []) ?>,
        <?= json_encode($segmenPelanggan['chartCounts'] ?? []) ?>,
        <?= json_encode($segmenPelanggan['chartColors'] ?? []) ?>
    );
    initSegmentDonut(
        'segmenPemesananChart',
        <?= json_encode($segmenPemesanan['chartLabels'] ?? []) ?>,
        <?= json_encode($segmenPemesanan['chartCounts'] ?? []) ?>,
        <?= json_encode($segmenPemesanan['chartColors'] ?? []) ?>
    );

    const laporanChartEl = document.getElementById('laporanRevenueChart');
    if (laporanChartEl) {
        const chartMonth = <?= (int) $filterMonth ?>;
        const chartYear = <?= (int) $filterYear ?>;
        const bulanNama = <?= json_encode(array_values($bulanOptions)) ?>;
        const chartOrderCounts = <?= json_encode($chartCounts) ?>;
        const ctx = laporanChartEl.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: <?= json_encode($chartValues) ?>,
                    borderColor: '#2E5CE6',
                    backgroundColor: 'rgba(46, 92, 230, 0.08)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(items) {
                                if (!items.length) return '';
                                const day = parseInt(items[0].label, 10);
                                const bulan = bulanNama[chartMonth - 1] || '';
                                return day + ' ' + bulan + ' ' + chartYear;
                            },
                            label: function(ctx) {
                                const val = ctx.parsed.y || 0;
                                const count = chartOrderCounts[ctx.dataIndex] || 0;
                                return [
                                    'Rp ' + val.toLocaleString('id-ID'),
                                    count + ' pembayaran terverifikasi',
                                ];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return 'Rp ' + (value / 1000000) + 'jt';
                                if (value >= 1000) return 'Rp ' + (value / 1000) + 'rb';
                                return 'Rp ' + value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Tanggal',
                            color: '#83A2CD',
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    }
</script>
<?= $this->endSection() ?>