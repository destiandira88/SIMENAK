<?php

/**
 * @var string                       $nama
 * @var list<array<string, mixed>>   $cards
 * @var list<array<string, mixed>>   $recentOrders
 * @var string                       $byDateJson
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Beranda<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Beranda Produksi<?= $this->endSection() ?>

<?= $this->section('banner_title') ?>Selamat Datang, Produksi 👋<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>Antrian desain dan cetak hari ini.<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?= view('dashboard/_partials/summary_cards', ['cards' => $cards]) ?>

<?= view('dashboard/_partials/produksi_kalender', ['byDateJson' => $byDateJson ?? '{}']) ?>

<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4">
    <h3 class="text-base font-bold text-[#051747]">Antrian Pengerjaan</h3>
    <label for="produksiDashboardSearch" class="sr-only">Cari pesanan</label>
    <div class="search-control w-full sm:w-auto">
        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
        </svg>
        <input
            id="produksiDashboardSearch"
            type="search"
            placeholder="Cari kode, pelanggan, produk..."
            autocomplete="off">
    </div>
</div>

<div class="admin-data-table-wrap">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold">No</th>
                    <th class="px-4 py-3 text-left font-semibold">Kode Pesanan</th>
                    <th class="px-4 py-3 text-left font-semibold">Pelanggan</th>
                    <th class="px-4 py-3 text-left font-semibold">Produk</th>
                    <th class="px-4 py-3 text-left font-semibold">Deadline</th>
                    <th class="px-4 py-3 text-left font-semibold">Sisa Kuota</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-left font-semibold w-12"></th>
                </tr>
            </thead>
            <tbody id="produksiDashboardBody">
                <?php if ($recentOrders === []): ?>
                    <tr id="emptyDataRow">
                        <td colspan="8" class="py-16 text-center">
                            <p class="text-sm font-medium text-slate-500">Tidak ada pesanan dalam antrian 🎉</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php helper('deadline'); ?>
                    <?php foreach ($recentOrders as $index => $order): ?>
                        <?php
                        $kodeOrder     = (string) ($order['kode_order'] ?? '');
                        $namaPelanggan = (string) ($order['nama_pelanggan'] ?? '-');
                        $noTelp        = trim((string) ($order['no_telp'] ?? ''));
                        $namaProduk    = !empty($order['is_custom'])
                            ? 'Pesanan Custom'
                            : (string) ($order['nama_produk'] ?? '-');
                        $status        = (string) ($order['status'] ?? '');
                        $sisaKuota     = (int) ($order['sisa_kuota'] ?? 0);
                        $kuotaRevisi   = (int) ($order['kuota_revisi'] ?? 0);
                        $idOrder       = (int) ($order['id_order'] ?? 0);
                        $deadlineRaw   = trim((string) ($order['deadline_produksi'] ?? ''));
                        $tsDeadline    = $deadlineRaw !== '' ? strtotime($deadlineRaw) : 0;
                        $searchText    = mb_strtolower(trim($kodeOrder . ' ' . $namaPelanggan . ' ' . $noTelp . ' ' . $namaProduk . ' ' . $status));
                        $kuotaBadge    = $sisaKuota > 0
                            ? 'bg-green-100 text-green-800'
                            : 'bg-red-100 text-red-800';
                        $kuotaLabel    = $sisaKuota > 0
                            ? 'Sisa ' . $sisaKuota . ' Revisi'
                            : 'Kuota Habis';
                        $lastRevisi    = $order['last_revisi'] ?? null;
                        $bisaUpload    = canProduksiUploadDraft($order, is_array($lastRevisi) ? $lastRevisi : null);
                        ?>
                        <tr
                            class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF] transition-colors"
                            data-search="<?= esc($searchText) ?>"
                            data-status="<?= esc($status) ?>">
                            <td class="row-num px-4 py-3.5 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3.5 font-mono text-sm font-semibold text-[#051747]">
                                <?= esc($kodeOrder ?: '-') ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <?= view('partials/pelanggan_kontak_cell', [
                                    'nama'   => $namaPelanggan,
                                    'noTelp' => $noTelp,
                                ]) ?>
                            </td>
                            <td class="px-4 py-3.5"><?= esc($namaProduk) ?></td>
                            <td class="px-4 py-3.5 text-slate-600 whitespace-nowrap">
                                <?= $deadlineRaw !== '' ? esc(formatTanggalId($deadlineRaw)) : '—' ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc($kuotaBadge) ?>">
                                    <?= esc($sisaKuota . '/' . $kuotaRevisi) ?> · <?= esc($kuotaLabel) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc(getStatusBadgeClass($status)) ?>">
                                    <?= esc(getOrderStatusLabel($order)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <?= view('partials/produksi_order_action_menu', [
                                    'idOrder'    => $idOrder,
                                    'kodeOrder'  => $kodeOrder,
                                    'bisaUpload' => $bisaUpload,
                                    'status'     => $status,
                                ]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="produksiEmptyFilter" class="hidden">
                        <td colspan="8" class="py-12 text-center">
                            <p class="text-sm font-medium text-slate-500">Tidak ada pesanan yang cocok dengan pencarian.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($recentOrders !== []): ?>
        <?= view('partials/admin_data_table_footer', [
            'entriesId'     => 'produksiEntriesSelect',
            'entriesInfoId' => 'produksiEntriesInfo',
            'paginationId'  => 'produksiTablePagination',
            'prevPageId'    => 'produksiPrevPageBtn',
            'nextPageId'    => 'produksiNextPageBtn',
            'pageInfoId'    => 'produksiPageInfo',
        ]) ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.adminDataTableConfig = {
        searchId: 'produksiDashboardSearch',
        tbodyId: 'produksiDashboardBody',
        emptyFilterRowId: 'produksiEmptyFilter',
        entriesId: 'produksiEntriesSelect',
        entriesInfoId: 'produksiEntriesInfo',
        paginationId: 'produksiTablePagination',
        prevPageId: 'produksiPrevPageBtn',
        nextPageId: 'produksiNextPageBtn',
        pageInfoId: 'produksiPageInfo',
        rowSelector: 'tr.data-table-row',
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<?= $this->endSection() ?>