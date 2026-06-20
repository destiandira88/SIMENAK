<?php

/**
 * @var string                       $nama
 * @var list<array<string, mixed>>   $cards
 * @var list<array<string, mixed>>   $recentOrders
 */
$pendingCustom = (int) ($cards[2]['value'] ?? 0);
$pendingKirim  = (int) ($cards[3]['value'] ?? 0);
$pendingVerif  = (int) ($pendingVerifikasiPerusahaan ?? 0);
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Dashboard Admin<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-6">
    <h2 class="text-2xl font-extrabold text-[#051747]">
        Selamat Datang, Admin 👋
    </h2>
    <p class="mt-1 text-sm text-slate-500">
        Pantau seluruh aktivitas pesanan.
    </p>
</div>

<?= view('dashboard/_partials/summary_cards', ['cards' => $cards]) ?>

<?php if ($pendingCustom > 0 || $pendingKirim > 0 || $pendingVerif > 0): ?>
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
        <h3 class="font-semibold text-amber-800 mb-3">⚡ Perlu Tindakan</h3>
        <div class="space-y-3">
            <?php if ($pendingVerif > 0): ?>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-amber-900">
                        <span class="mr-1">🏢</span>
                        <?= esc((string) $pendingVerif) ?> pengajuan verifikasi perusahaan menunggu tinjauan
                    </p>
                    <a href="<?= site_url('verifikasi-perusahaan') ?>" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-amber-800 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-amber-900">
                        Tinjau →
                    </a>
                </div>
            <?php endif; ?>
            <?php if ($pendingCustom > 0): ?>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-amber-900">
                        <span class="mr-1">⭐</span>
                        <?= esc((string) $pendingCustom) ?> pesanan custom menunggu konfirmasi harga
                    </p>
                    <a href="<?= site_url('list-pemesanan?tab=menunggu-harga') ?>" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-amber-800 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-amber-900">
                        Tinjau →
                    </a>
                </div>
            <?php endif; ?>
            <?php if ($pendingKirim > 0): ?>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-amber-900">
                        <span class="mr-1">🚚</span>
                        <?= esc((string) $pendingKirim) ?> pesanan siap untuk dikirim
                    </p>
                    <a href="<?= site_url('pengiriman') ?>" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-amber-800 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-amber-900">
                        Kelola →
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?= view('dashboard/_partials/recent_orders_table', [
    'orders'            => $recentOrders,
    'variant'           => 'admin',
    'sectionTitle'      => 'Pesanan Terbaru',
    'searchId'          => 'adminDashboardSearch',
    'tbodyId'           => 'adminDashboardBody',
    'searchPlaceholder' => 'Cari kode, pelanggan, produk...',
    'showDateFilter'    => true,
    'dateFromId'        => 'adminDashboardDateFrom',
    'dateToId'          => 'adminDashboardDateTo',
]) ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.adminDataTableConfig = {
        searchId: 'adminDashboardSearch',
        filterId: '',
        dateFromId: 'adminDashboardDateFrom',
        dateToId: 'adminDashboardDateTo',
        tbodyId: 'adminDashboardBody',
        emptyFilterRowId: 'emptyFilterRow',
        enableSort: true,
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<?= $this->endSection() ?>
