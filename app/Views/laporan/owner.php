<?php

/**
 * @var array<string, mixed> $report
 * @var array<string, string> $filters
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
$chartLabelFull  = $report['chartLabelFull'] ?? [];

$exportQuery = http_build_query([
    'dari'   => $filters['dari'] ?? '',
    'sampai' => $filters['sampai'] ?? '',
]);

$growthOrdersPct = $growthOrders === null
    ? null
    : (($growthOrders > 0 ? '+' : '') . number_format($growthOrders, 1, ',', '.') . '%');

$growthOrdersSuffix = $growthOrders === null
    ? null
    : 'vs ' . ($period['prevLabel'] ?? 'periode sebelumnya');

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
        'label'   => 'Total Pesanan',
        'value'   => (int) ($report['totalPesanan'] ?? 0),
        'icon'    => 'clipboard',
        'color'   => '#051747',
        'tooltip' => 'Jumlah pesanan baru yang dibuat berdasarkan tanggal pesan pada periode yang dipilih.',
    ],
    [
        'label'   => 'Pesanan Selesai',
        'value'   => (int) ($report['pesananSelesai'] ?? 0),
        'icon'    => 'check',
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
        'value'          => $growthOrdersPct ?? '-',
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
        'value'   => $avgSelesaiHari !== null ? number_format((float) $avgSelesaiHari, 1, ',', '.') . ' hari' : '-',
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
<?= $this->section('page_title') ?>Laporan Pemilik<?= $this->endSection() ?>

<?= $this->section('banner_title') ?>Laporan Pemilik<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>Ringkasan performa bisnis Z'Plack <?= esc((string) ($period['label'] ?? '-')) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .btn-laporan-filter {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 14px;
        border: 1px solid #E2E8F0;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #4A5568;
        background: #fff;
        transition: border-color .2s, background-color .2s, color .2s;
    }

    .btn-laporan-filter:hover,
    .btn-laporan-filter.is-open {
        border-color: #CBD5E1;
        background: #F8FAFC;
        color: #051747;
    }

    .laporan-admin-filter-panel {
        position: absolute;
        left: 0;
        top: calc(100% + 0.5rem);
        z-index: 40;
        width: 18rem;
        padding: 1rem;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        background: #fff;
        box-shadow: 0 12px 40px rgba(15, 23, 43, 0.12);
    }

    .laporan-admin-filter-panel .filter-field+.filter-field {
        margin-top: 1rem;
    }

    .laporan-admin-filter-panel .filter-field label {
        display: block;
        margin-bottom: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748B;
    }

    .laporan-admin-filter-panel .filter-field input,
    .laporan-admin-filter-panel .filter-field select {
        width: 100%;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<form method="get" action="<?= esc(site_url('laporan')) ?>" class="flex items-center justify-end gap-3 mb-6">
    <div class="relative">
        <button
            type="button"
            id="laporanOwnerFilterToggle"
            class="btn-laporan-filter"
            aria-expanded="false"
            aria-controls="laporanOwnerFilterPanel"
            aria-haspopup="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4 shrink-0" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
            </svg>
            Filter
        </button>
        <div id="laporanOwnerFilterPanel" class="laporan-admin-filter-panel hidden">
            <div class="filter-field">
                <label for="dari">Dari</label>
                <input type="date" id="dari" name="dari" value="<?= esc((string) ($filters['dari'] ?? '')) ?>"
                    class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
            </div>
            <div class="filter-field">
                <label for="sampai">Sampai</label>
                <input type="date" id="sampai" name="sampai" value="<?= esc((string) ($filters['sampai'] ?? '')) ?>"
                    class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
            </div>
            <button type="submit" class="mt-4 w-full bg-[#051747] text-white rounded-[14px] text-sm font-bold px-4 py-2.5 hover:bg-[#2E5CE6] transition-colors">
                Terapkan Filter
            </button>
        </div>
    </div>
    <?= view('laporan/_partials/export_dropdown', [
        'dropdownId' => 'laporanOwnerExport',
        'csvUrl'     => site_url('laporan/export?' . $exportQuery),
        'pdfUrl'     => site_url('laporan/export-pdf?' . $exportQuery),
    ]) ?>
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
        Pendapatan Harian <?= esc((string) ($period['label'] ?? '')) ?>
        <span class="font-normal text-slate-400 text-sm">· berdasarkan tgl verifikasi pembayaran</span>
    </h3>
    <p class="text-xs text-slate-500 mt-0.5 mb-4">Nilai per hari dari pesanan yang berstatus selesai dalam periode ini.</p>
    <canvas id="laporanRevenueChart" height="90"></canvas>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
    <div class="admin-data-table-wrap">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-[#051747]">Rekap Per Kategori <?= esc((string) ($period['label'] ?? '')) ?></h3>
            <p class="text-xs text-slate-500 mt-0.5">
                (dihitung dari pesanan selesai)
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#051747] text-white text-xs uppercase">
                        <th class="px-4 py-3 text-left font-semibold w-12">No</th>
                        <th class="px-4 py-3 text-left font-semibold">Kategori</th>
                        <th class="px-4 py-3 text-left font-semibold">Jumlah</th>
                        <th class="px-4 py-3 text-left font-semibold">Pendapatan</th>
                        <th class="px-4 py-3 text-left font-semibold">Rata-rata/Pesanan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rekapKategori === []): ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500 text-sm">
                                Tidak ada data kategori pada periode ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rekapKategori as $index => $row): ?>
                            <tr class="border-b border-slate-100 hover:bg-[#F8FAFF]">
                                <td class="px-4 py-3 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
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
        <h3 class="text-base font-bold text-[#051747] mb-4">Top 5 Katalog Terlaris <?= esc((string) ($period['label'] ?? '')) ?></h3>
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
    *Pesanan selesai dihitung berdasarkan tanggal selesai.
    Pendapatan dan grafik harian menggunakan tanggal verifikasi pembayaran,
    sedangkan rekap kategori dan produk terlaris dihitung dari total nilai pesanan selesai pada periode yang dipilih.
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
        const chartLabelFull = <?= json_encode($chartLabelFull) ?>;
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
                                if (!items.length) {
                                    return '';
                                }
                                return chartLabelFull[items[0].dataIndex] || items[0].label || '';
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
<script>
    (() => {
        const toggle = document.getElementById('laporanOwnerFilterToggle');
        const panel = document.getElementById('laporanOwnerFilterPanel');
        if (!toggle || !panel) {
            return;
        }

        const setOpen = (open) => {
            panel.classList.toggle('hidden', !open);
            toggle.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        };

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            setOpen(panel.classList.contains('hidden'));
        });

        document.addEventListener('click', (event) => {
            if (panel.classList.contains('hidden')) {
                return;
            }
            if (!panel.contains(event.target) && !toggle.contains(event.target)) {
                setOpen(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !panel.classList.contains('hidden')) {
                setOpen(false);
            }
        });
    })();
</script>
<?= $this->endSection() ?>