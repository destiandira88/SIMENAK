<?php

/**
 * @var string                       $nama
 * @var list<array<string, mixed>>   $cards
 * @var list<array<string, mixed>>   $recentOrders
 * @var bool                         $canCreateOrder
 * @var bool                         $isKerjasama
 */
$canCreateOrder = $canCreateOrder ?? true;
$isKerjasama    = $isKerjasama ?? false;
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Beranda<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Beranda Pelanggan<?= $this->endSection() ?>

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
    <a href="<?= $canCreateOrder ? site_url('katalog') : '#' ?>"
        class="btn-primary inline-flex items-center justify-center px-4 py-2.5 text-sm text-white shrink-0 <?= $canCreateOrder ? '' : 'opacity-50 pointer-events-none cursor-not-allowed' ?>">
        + Buat Pesanan Baru
    </a>
</div>

<?php if ($isKerjasama): ?>
    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-900">
        <p class="font-semibold">Akun Kerja Sama Perusahaan aktif.</p>
        <p class="mt-1 text-emerald-800">Pesanan mengikuti skema nota tagihan (pelunasan setelah barang diterima). Order &gt; Rp 5 juta tetap wajib DP 50%.</p>
    </div>
<?php endif; ?>

<?= view('dashboard/_partials/summary_cards', [
    'cards'     => $cards,
    'gridClass' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5',
]) ?>

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
