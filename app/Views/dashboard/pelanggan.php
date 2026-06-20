<?php

/**
 * @var string                       $nama
 * @var list<array<string, mixed>>   $cards
 * @var list<array<string, mixed>>   $recentOrders
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Dashboard Pelanggan<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h2 class="text-2xl font-extrabold text-[#051747]">
            Halo, <?= esc($nama) ?> 👋
        </h2>
        <p class="mt-1 text-sm text-slate-500">
            Ringkasan pesanan cetak Anda di SIMENAK Z'Plack
        </p>
    </div>
    <a href="<?= site_url('katalog') ?>" class="btn-primary inline-flex items-center justify-center px-4 py-2.5 text-sm text-white shrink-0">
        + Buat Pesanan Baru
    </a>
</div>

<?= view('dashboard/_partials/summary_cards', ['cards' => $cards]) ?>

<?= view('dashboard/_partials/recent_orders_table', [
    'orders'            => $recentOrders,
    'variant'           => 'pelanggan',
    'sectionTitle'      => 'Pesanan Terbaru',
    'searchId'          => 'pelangganDashboardSearch',
    'tbodyId'           => 'pelangganDashboardBody',
    'searchPlaceholder' => 'Cari kode atau produk...',
]) ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.adminDataTableConfig = {
        searchId: 'pelangganDashboardSearch',
        filterId: '',
        tbodyId: 'pelangganDashboardBody',
        emptyFilterRowId: 'emptyFilterRow',
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<?= $this->endSection() ?>
