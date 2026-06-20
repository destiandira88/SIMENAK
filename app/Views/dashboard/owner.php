<?php

/**
 * @var string                       $nama
 * @var list<array<string, mixed>>   $cards
 * @var list<array<string, mixed>>   $recentOrders
 * @var list<string>                 $chartLabels
 * @var list<int>                    $chartValues
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Dashboard Owner<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-6">
    <h2 class="text-2xl font-extrabold text-[#051747]">
        Selamat Datang, Owner 👋
    </h2>
    <p class="mt-1 text-sm text-slate-500">
        Ringkasan performa bisnis Z'Plack · <?= esc(date('d F Y')) ?>
    </p>
</div>

<?= view('dashboard/_partials/summary_cards', ['cards' => $cards]) ?>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-6">
    <h3 class="text-base font-bold text-[#051747] mb-4">Pesanan 7 Hari Terakhir</h3>
    <canvas id="ordersChart" height="100"></canvas>
</div>

<?= view('dashboard/_partials/recent_orders_table', [
    'orders'            => $recentOrders,
    'variant'           => 'owner',
    'sectionTitle'      => 'Pesanan Terbaru',
    'searchId'          => 'ownerDashboardSearch',
    'tbodyId'           => 'ownerDashboardBody',
    'searchPlaceholder' => 'Cari kode, pelanggan, produk...',
    'entriesId'         => 'ownerEntriesSelect',
    'entriesInfoId'     => 'ownerEntriesInfo',
    'paginationId'      => 'ownerTablePagination',
    'prevPageId'        => 'ownerPrevPageBtn',
    'nextPageId'        => 'ownerNextPageBtn',
    'pageInfoId'        => 'ownerPageInfo',
]) ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    window.adminDataTableConfig = {
        searchId: 'ownerDashboardSearch',
        tbodyId: 'ownerDashboardBody',
        emptyFilterRowId: 'emptyFilterRow',
        entriesId: 'ownerEntriesSelect',
        entriesInfoId: 'ownerEntriesInfo',
        paginationId: 'ownerTablePagination',
        prevPageId: 'ownerPrevPageBtn',
        nextPageId: 'ownerNextPageBtn',
        pageInfoId: 'ownerPageInfo',
        enableSort: true,
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<script>
    const ordersChartEl = document.getElementById('ordersChart');
    if (ordersChartEl) {
        const ctx = ordersChartEl.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels ?? []) ?>,
                datasets: [{
                    label: 'Jumlah Pesanan',
                    data: <?= json_encode($chartValues ?? []) ?>,
                    backgroundColor: '#2E5CE6',
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
</script>
<?= $this->endSection() ?>
