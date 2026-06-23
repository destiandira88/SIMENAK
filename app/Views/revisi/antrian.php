<?php
/**
 * @var string                       $title
 * @var string                       $page_title
 * @var list<array<string, mixed>>   $orders
 * @var bool                         $showAll
 * @var array<string, int>           $statusCounts
 * @var bool                         $readOnly
 */
$showAll  = $showAll ?? false;
$readOnly = (bool) ($readOnly ?? false);
$orders   = $orders ?? [];
$statusCounts = $statusCounts ?? [];
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<style>
    .pemesanan-tab {
        padding: 10px 4px;
        margin-right: 24px;
        font-size: 14px;
        font-weight: 600;
        color: #64748B;
        border-bottom: 2px solid transparent;
        transition: color .2s, border-color .2s;
        white-space: nowrap;
    }

    .pemesanan-tab:hover {
        color: #051747;
    }

    .pemesanan-tab.is-active {
        color: #2E5CE6;
        border-bottom-color: #2E5CE6;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h2 class="text-2xl font-extrabold text-[#051747]"><?= esc($page_title) ?></h2>
    <p class="mt-1 text-sm text-slate-500">
        <?= $readOnly
            ? 'Pantau seluruh pesanan dalam alur desain dan produksi.'
            : ($showAll ? 'Semua pesanan dalam alur desain & produksi' : 'Pesanan yang perlu draft atau revisi desain') ?>
    </p>
</div>

<?php
$countAll = count($orders);
$tabDefs  = $showAll
    ? [
        ''              => 'Semua',
        'terverifikasi' => 'Terverifikasi',
        'proses_desain' => 'Proses Desain',
        'proses_revisi' => 'Proses Revisi',
        'proses_cetak'  => 'Proses Cetak',
        'finishing'     => 'Finishing',
    ]
    : [
        ''              => 'Semua',
        'terverifikasi' => 'Terverifikasi',
        'proses_desain' => 'Proses Desain',
        'proses_revisi' => 'Proses Revisi',
    ];
?>

<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4 border-b border-slate-200">
    <div class="flex flex-wrap items-end gap-0 overflow-x-auto" role="tablist" aria-label="Filter status desain">
        <?php foreach ($tabDefs as $key => $label): ?>
            <?php $count = $key === '' ? $countAll : ($statusCounts[$key] ?? 0); ?>
            <button
                type="button"
                class="pemesanan-tab <?= $key === '' ? 'is-active' : '' ?>"
                data-status-filter="<?= esc($key) ?>"
                role="tab"
                aria-selected="<?= $key === '' ? 'true' : 'false' ?>">
                <?= esc($label) ?> (<?= esc((string) $count) ?>)
            </button>
        <?php endforeach; ?>
    </div>

    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:flex-wrap sm:justify-end mb-3 sm:mb-4">
        <label for="antrianDesainSearch" class="sr-only">Cari pesanan</label>
        <div class="search-control w-full sm:w-auto">
            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
            </svg>
            <input
                id="antrianDesainSearch"
                type="search"
                placeholder="Cari kode, pelanggan, produk..."
                autocomplete="off">
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="antrianTable">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold">No</th>
                    <th class="px-4 py-3 text-left font-semibold">Kode Pesanan</th>
                    <th class="px-4 py-3 text-left font-semibold">Pelanggan</th>
                    <th class="px-4 py-3 text-left font-semibold">Produk</th>
                    <th class="px-4 py-3 text-left font-semibold">Deadline</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-left font-semibold">Kuota</th>
                    <th class="px-4 py-3 text-left font-semibold">Catatan Revisi</th>
                    <th class="px-4 py-3 text-left font-semibold w-12"></th>
                </tr>
            </thead>
            <tbody id="antrianDesainBody">
                <?php if ($orders === []): ?>
                    <tr>
                        <td colspan="9" class="py-16 text-center text-slate-500 text-sm">
                            Tidak ada pesanan dalam antrian 🎉
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $index => $a): ?>
                        <?php
                        $idOrder       = (int) ($a['id_order'] ?? 0);
                        $kodeOrder     = (string) ($a['kode_order'] ?? '');
                        $sisaKuota     = (int) ($a['sisa_kuota'] ?? 0);
                        $kuotaRevisi   = (int) ($a['kuota_revisi'] ?? 0);
                        $status        = (string) ($a['status'] ?? '');
                        $lastRevis     = $a['last_revisi'] ?? null;
                        $catatanRevisi = is_array($lastRevis) ? (string) ($lastRevis['catatan_revisi'] ?? '') : '';
                        $namaPelanggan = (string) ($a['nama_pelanggan'] ?? '-');
                        $noTelp        = trim((string) ($a['no_telp'] ?? ''));
                        $namaProduk    = (int) ($a['is_custom'] ?? 0) === 1
                            ? 'Pesanan Custom'
                            : (string) ($a['nama_produk'] ?? '-');
                        $searchText    = mb_strtolower(trim($kodeOrder . ' ' . $namaPelanggan . ' ' . $noTelp . ' ' . $namaProduk));
                        $deadline      = (string) ($a['deadline'] ?? '');
                        $tsDeadline    = $deadline !== '' ? strtotime($deadline) : 0;
                        $daysLeft      = $tsDeadline > 0 ? (int) floor(($tsDeadline - time()) / 86400) : 999;
                        $deadlineUrgent = $daysLeft <= 3 && $tsDeadline > 0;
                        $kuotaClass    = $sisaKuota <= 1 ? 'text-red-600 font-bold' : 'text-emerald-600 font-bold';
                        $bisaUpload    = canProduksiUploadDraft($a, is_array($lastRevis) ? $lastRevis : null);
                        ?>
                        <tr class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF] transition-colors"
                            data-status="<?= esc($status) ?>"
                            data-search="<?= esc($searchText) ?>">
                            <td class="row-num px-4 py-3.5 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3.5 font-mono font-semibold text-[#051747] whitespace-nowrap">
                                <?php if ($readOnly && $kodeOrder !== ''): ?>
                                    <a href="<?= esc(site_url('order/detail/' . $kodeOrder)) ?>"
                                        class="hover:text-[#2E5CE6] hover:underline">
                                        <?= esc($kodeOrder) ?>
                                    </a>
                                <?php else: ?>
                                    <?= esc($kodeOrder) ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <?= view('partials/pelanggan_kontak_cell', [
                                    'nama'   => $namaPelanggan,
                                    'noTelp' => $noTelp,
                                ]) ?>
                            </td>
                            <td class="px-4 py-3.5"><?= esc($namaProduk) ?></td>
                            <td class="px-4 py-3.5 whitespace-nowrap <?= $deadlineUrgent ? 'text-red-600 font-semibold' : 'text-slate-600' ?>">
                                <?php if ($tsDeadline > 0): ?>
                                    <?= esc(date('d M Y', $tsDeadline)) ?>
                                    <?php if ($deadlineUrgent): ?>
                                        <span class="ml-1" title="Deadline dekat">⚠️</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc(getStatusBadgeClass($status)) ?>">
                                    <?= esc(getOrderStatusLabel($a)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3.5 <?= esc($kuotaClass) ?>">
                                <?= esc((string) $sisaKuota) ?>/<?= esc((string) $kuotaRevisi) ?>
                            </td>
                            <td class="px-4 py-3.5 max-w-[12rem]">
                                <?php if ($catatanRevisi !== ''): ?>
                                    <span class="text-xs text-amber-700 truncate block" title="<?= esc($catatanRevisi) ?>">
                                        <?= esc($catatanRevisi) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <?= view('partials/produksi_order_action_menu', [
                                    'idOrder'    => $idOrder,
                                    'kodeOrder'  => $kodeOrder,
                                    'bisaUpload' => $bisaUpload,
                                    'status'     => $status,
                                    'readOnly'   => $readOnly,
                                ]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="antrianEmptyFilter" class="hidden">
                        <td colspan="9" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada pesanan yang cocok dengan filter atau pencarian.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($orders !== []): ?>
        <?= view('partials/admin_data_table_footer', [
            'entriesId'     => 'antrianEntriesSelect',
            'entriesInfoId' => 'antrianEntriesInfo',
            'paginationId'  => 'antrianTablePagination',
            'prevPageId'    => 'antrianPrevPageBtn',
            'nextPageId'    => 'antrianNextPageBtn',
            'pageInfoId'    => 'antrianPageInfo',
        ]) ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let antrianStatusFilter = '';

    window.adminDataTableConfig = {
        searchId: 'antrianDesainSearch',
        tbodyId: 'antrianDesainBody',
        emptyFilterRowId: 'antrianEmptyFilter',
        entriesId: 'antrianEntriesSelect',
        entriesInfoId: 'antrianEntriesInfo',
        paginationId: 'antrianTablePagination',
        prevPageId: 'antrianPrevPageBtn',
        nextPageId: 'antrianNextPageBtn',
        pageInfoId: 'antrianPageInfo',
        rowSelector: 'tr.data-table-row',
        getTabFilter: function (row) {
            return antrianStatusFilter === '' || row.dataset.status === antrianStatusFilter;
        },
        onReady: function (api) {
            document.querySelectorAll('.pemesanan-tab').forEach(function (tab) {
                tab.addEventListener('click', function () {
                    antrianStatusFilter = this.dataset.statusFilter || '';

                    document.querySelectorAll('.pemesanan-tab').forEach(function (t) {
                        const active = t === tab;
                        t.classList.toggle('is-active', active);
                        t.setAttribute('aria-selected', active ? 'true' : 'false');
                    });

                    api.setPage(1);
                    api.applyTableState();
                });
            });
        },
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<?= $this->endSection() ?>
