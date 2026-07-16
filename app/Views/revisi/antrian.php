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

<?= $this->section('banner_title') ?><?= esc($page_title) ?><?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>
<?= $readOnly
    ? 'Pantau seluruh pesanan dalam alur desain dan produksi.'
    : ($showAll ? 'Semua pesanan dalam alur desain & produksi' : 'Pesanan yang perlu draft atau revisi desain') ?>
<?= $this->endSection() ?>

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

    .sortable-th {
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
    }

    .sortable-th:hover {
        background: rgba(255, 255, 255, .08);
    }

    .sort-icon {
        margin-left: 4px;
        font-size: 11px;
        opacity: .65;
    }

    .sortable-th.is-sorted .sort-icon {
        opacity: 1;
    }

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
    .btn-laporan-filter.is-open,
    .btn-laporan-filter.is-active {
        border-color: #CBD5E1;
        background: #F8FAFC;
        color: #051747;
    }

    .laporan-admin-filter-panel {
        position: absolute;
        right: 0;
        top: calc(100% + 0.5rem);
        z-index: 40;
        width: 18rem;
        padding: 1rem;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        background: #fff;
        box-shadow: 0 12px 40px rgba(15, 23, 43, 0.12);
    }

    .laporan-admin-filter-panel .filter-field + .filter-field {
        margin-top: 1rem;
    }

    .laporan-admin-filter-panel .filter-field label {
        display: block;
        margin-bottom: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748B;
    }

    .laporan-admin-filter-panel .filter-field input {
        width: 100%;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        color: #334155;
        background: #fff;
    }

    .laporan-admin-filter-panel .filter-field input:focus {
        border-color: #2E5CE6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(46, 92, 230, 0.1);
    }

    .produksi-status-badge-btn {
        cursor: pointer;
        border: 1.5px solid transparent;
        transition: box-shadow .15s ease, border-color .15s ease, opacity .15s ease;
    }

    .produksi-status-badge-btn--editable {
        border-style: dashed;
        border-color: rgba(91, 33, 182, 0.45);
        padding-right: 0.5rem;
    }

    .produksi-status-badge-btn--editable:hover:not(:disabled):not(.is-loading) {
        box-shadow: 0 0 0 2px rgba(91, 33, 182, 0.2);
        border-color: rgba(91, 33, 182, 0.55);
    }

    .produksi-status-badge-chevron {
        flex-shrink: 0;
        opacity: 0.7;
        transition: transform .15s ease, opacity .15s ease;
    }

    .produksi-status-badge-btn--editable:hover:not(:disabled):not(.is-loading) .produksi-status-badge-chevron,
    .produksi-status-badge-btn--editable[aria-expanded="true"] .produksi-status-badge-chevron {
        opacity: 1;
    }

    .produksi-status-badge-btn--editable[aria-expanded="true"] .produksi-status-badge-chevron {
        transform: rotate(180deg);
    }

    .produksi-status-badge-btn.is-loading {
        cursor: wait;
        opacity: 0.75;
    }

    .produksi-status-quick-menu {
        position: fixed;
        z-index: 70;
        display: none;
        min-width: 11rem;
        padding: 6px;
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        box-shadow: 0 12px 40px rgba(15, 23, 43, 0.15);
    }

    .produksi-status-quick-menu.is-open {
        display: block;
    }

    .produksi-status-quick-option {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 0.5rem;
        padding: 8px 10px;
        border: none;
        border-radius: 8px;
        background: transparent;
        cursor: pointer;
        transition: background .15s ease;
    }

    .produksi-status-quick-option:hover {
        background: #F8FAFF;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

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
        <div class="relative w-full sm:w-auto">
            <button
                type="button"
                id="antrianFilterToggle"
                class="btn-laporan-filter w-full sm:w-auto"
                aria-expanded="false"
                aria-controls="antrianFilterPanel"
                aria-haspopup="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4 shrink-0" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/>
                </svg>
                Filter
            </button>
            <div id="antrianFilterPanel" class="laporan-admin-filter-panel hidden">
                <div class="filter-field">
                    <label for="antrianFilterKodeDraft">Kode Pesanan</label>
                    <input
                        type="search"
                        id="antrianFilterKodeDraft"
                        placeholder="Contoh: ORD-2026-001"
                        autocomplete="off"
                        class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
                </div>
                <div class="filter-field">
                    <label for="antrianFilterDeadlineFromDraft">Deadline Dari</label>
                    <input
                        type="date"
                        id="antrianFilterDeadlineFromDraft"
                        class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
                </div>
                <div class="filter-field">
                    <label for="antrianFilterDeadlineToDraft">Deadline Sampai</label>
                    <input
                        type="date"
                        id="antrianFilterDeadlineToDraft"
                        class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
                </div>
                <button
                    type="button"
                    id="antrianFilterApply"
                    class="mt-4 w-full bg-[#051747] text-white rounded-[14px] text-sm font-bold px-4 py-2.5 hover:bg-[#2E5CE6] transition-colors">
                    Terapkan Filter
                </button>
            </div>
        </div>
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

<div class="admin-data-table-wrap">
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="antrianTable">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold">No</th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="kode">
                        Kode Pesanan<span class="sort-icon">↕</span>
                    </th>
                    <th class="px-4 py-3 text-left font-semibold">Pelanggan</th>
                    <th class="px-4 py-3 text-left font-semibold">Jenis Pelanggan</th>
                    <th class="px-4 py-3 text-left font-semibold">Produk</th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="deadline">
                        Deadline<span class="sort-icon">↕</span>
                    </th>
                    <th class="px-4 py-3 text-left font-semibold">
                        Status
                        <?php if ($showAll && ! $readOnly): ?>
                            <span class="block mt-0.5 text-[9px] font-normal normal-case tracking-normal text-white/55">
                                Proses Cetak → klik badge
                            </span>
                        <?php endif; ?>
                    </th>
                    <th class="px-4 py-3 text-left font-semibold">Kuota</th>
                    <th class="px-4 py-3 text-left font-semibold">Catatan Revisi</th>
                    <th class="px-4 py-3 text-left font-semibold w-12"></th>
                </tr>
            </thead>
            <tbody id="antrianDesainBody">
                <?php if ($orders === []): ?>
                    <tr>
                        <td colspan="10" class="py-16 text-center text-slate-500 text-sm">
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
                        $jenisPelanggan = (string) ($a['jenis_pelanggan'] ?? 'perseorangan');
                        $isKerjasama   = $jenisPelanggan === 'perusahaan';
                        $jenisPelangganLabel = $isKerjasama ? 'Kerjasama' : 'Perseorangan';
                        $namaProduk    = (int) ($a['is_custom'] ?? 0) === 1
                            ? 'Pesanan Custom'
                            : (string) ($a['nama_produk'] ?? '-');
                        $searchText    = mb_strtolower(trim($kodeOrder . ' ' . $namaPelanggan . ' ' . $noTelp . ' ' . $jenisPelangganLabel . ' ' . $namaProduk));
                        $deadline      = (string) ($a['deadline_produksi'] ?? '');
                        $tsDeadline    = $deadline !== '' ? strtotime($deadline) : 0;
                        $daysLeft      = $tsDeadline > 0 ? (int) floor(($tsDeadline - time()) / 86400) : 999;
                        $deadlineUrgent = $daysLeft <= 3 && $tsDeadline > 0;
                        $kuotaClass    = $sisaKuota <= 1 ? 'text-red-600 font-bold' : 'text-emerald-600 font-bold';
                        $bisaUpload    = canProduksiUploadDraft($a, is_array($lastRevis) ? $lastRevis : null);
                        ?>
                        <tr class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF] transition-colors"
                            data-status="<?= esc($status) ?>"
                            data-search="<?= esc($searchText) ?>"
                            data-kode="<?= esc(mb_strtolower($kodeOrder)) ?>"
                            data-deadline="<?= esc((string) $tsDeadline) ?>"
                            data-tanggal="<?= esc((string) $tsDeadline) ?>">
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
                            <td class="px-4 py-3.5">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold <?= $isKerjasama ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' ?>">
                                    <?= esc($jenisPelangganLabel) ?>
                                </span>
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
                            <?= view('partials/produksi_status_badge_cell', [
                                'status'   => $status,
                                'order'    => $a,
                                'idOrder'  => $idOrder,
                                'showAll'  => $showAll,
                                'readOnly' => $readOnly,
                            ]) ?>
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
                                    'idOrder'          => $idOrder,
                                    'kodeOrder'        => $kodeOrder,
                                    'bisaUpload'       => $bisaUpload,
                                    'status'           => $status,
                                    'readOnly'         => $readOnly,
                                    'hideCetakSelesai' => $showAll && ! $readOnly,
                                ]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="antrianEmptyFilter" class="hidden">
                        <td colspan="10" class="py-12 text-center text-slate-500 text-sm">
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

<input type="hidden" id="antrianKodeFilter" value="">
<input type="hidden" id="antrianDeadlineFrom" value="">
<input type="hidden" id="antrianDeadlineTo" value="">

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const antrianEnableStatusQuick = <?= ($showAll && ! $readOnly) ? 'true' : 'false' ?>;
    const antrianProduksiStatusUrl = <?= json_encode(site_url('produksi/update-status'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    let antrianStatusFilter = '';
    let antrianTableApi = null;

    function antrianSyncFilterActiveState() {
        const toggle = document.getElementById('antrianFilterToggle');
        if (!toggle) {
            return;
        }

        const kodeVal = (document.getElementById('antrianKodeFilter')?.value || '').trim();
        const fromVal = document.getElementById('antrianDeadlineFrom')?.value || '';
        const toVal = document.getElementById('antrianDeadlineTo')?.value || '';
        const hasFilter = kodeVal !== '' || fromVal !== '' || toVal !== '';

        toggle.classList.toggle('is-active', hasFilter);
    }

    function antrianSyncFilterDraftFromActive() {
        const kodeDraft = document.getElementById('antrianFilterKodeDraft');
        const fromDraft = document.getElementById('antrianFilterDeadlineFromDraft');
        const toDraft = document.getElementById('antrianFilterDeadlineToDraft');
        const kodeActive = document.getElementById('antrianKodeFilter');
        const fromActive = document.getElementById('antrianDeadlineFrom');
        const toActive = document.getElementById('antrianDeadlineTo');

        if (kodeDraft && kodeActive) {
            kodeDraft.value = kodeActive.value;
        }
        if (fromDraft && fromActive) {
            fromDraft.value = fromActive.value;
        }
        if (toDraft && toActive) {
            toDraft.value = toActive.value;
        }
    }

    function antrianApplyPanelFilter() {
        const kodeDraft = document.getElementById('antrianFilterKodeDraft');
        const fromDraft = document.getElementById('antrianFilterDeadlineFromDraft');
        const toDraft = document.getElementById('antrianFilterDeadlineToDraft');
        const kodeActive = document.getElementById('antrianKodeFilter');
        const fromActive = document.getElementById('antrianDeadlineFrom');
        const toActive = document.getElementById('antrianDeadlineTo');

        if (kodeActive && kodeDraft) {
            kodeActive.value = kodeDraft.value.trim();
        }
        if (fromActive && fromDraft) {
            fromActive.value = fromDraft.value;
        }
        if (toActive && toDraft) {
            toActive.value = toDraft.value;
        }

        antrianSyncFilterActiveState();

        const panel = document.getElementById('antrianFilterPanel');
        const toggle = document.getElementById('antrianFilterToggle');
        if (panel) {
            panel.classList.add('hidden');
        }
        if (toggle) {
            toggle.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        }

        if (antrianTableApi) {
            antrianTableApi.setPage(1);
            antrianTableApi.applyTableState();
        }
    }

    let antrianStatusQuickOpenMenu = null;
    let antrianStatusQuickOpenBtn = null;

    function antrianStatusQuickCloseMenu() {
        if (antrianStatusQuickOpenMenu) {
            antrianStatusQuickOpenMenu.classList.remove('is-open');
        }
        if (antrianStatusQuickOpenBtn) {
            antrianStatusQuickOpenBtn.setAttribute('aria-expanded', 'false');
        }
        antrianStatusQuickOpenMenu = null;
        antrianStatusQuickOpenBtn = null;
    }

    function antrianPositionStatusQuickMenu(menu, trigger) {
        menu.classList.add('is-open');
        const rect = trigger.getBoundingClientRect();
        const margin = 8;
        let left = rect.left;
        let top = rect.bottom + 6;
        const width = menu.offsetWidth;
        const height = menu.offsetHeight;

        if (left + width > window.innerWidth - margin) {
            left = Math.max(margin, window.innerWidth - width - margin);
        }
        if (left < margin) {
            left = margin;
        }
        if (top + height > window.innerHeight - margin) {
            top = Math.max(margin, rect.top - height - 6);
        }

        menu.style.left = left + 'px';
        menu.style.top = top + 'px';
    }

    function antrianSyncCsrfFromHtml(html) {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const token = doc.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) {
                meta.setAttribute('content', token);
            }
        }
    }

    function antrianExtractFlashFromHtml(html) {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const flashes = [];

        doc.querySelectorAll('[data-flash-toast]').forEach(function (el) {
            const msgEl = el.querySelector('.flash-toast-message');
            const message = (msgEl?.textContent || '').trim();
            if (message === '') {
                return;
            }

            let type = 'info';
            if (msgEl?.classList.contains('text-emerald-900')) {
                type = 'success';
            } else if (msgEl?.classList.contains('text-[#991B1B]')) {
                type = 'error';
            } else if (msgEl?.classList.contains('text-amber-900')) {
                type = 'warning';
            } else if (msgEl?.classList.contains('text-sky-900')) {
                type = 'info';
            }

            flashes.push({ type: type, message: message });
        });

        return flashes;
    }

    function antrianShowClientToast(type, message) {
        const styles = {
            success: {
                bg: 'bg-emerald-50',
                border: 'border-emerald-200/80',
                text: 'text-emerald-900',
                iconBg: 'bg-emerald-500',
                bar: 'bg-emerald-500',
                iconSvg: '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>',
            },
            error: {
                bg: 'bg-[#FEE2E2]',
                border: 'border-[#FECACA]',
                text: 'text-[#991B1B]',
                iconBg: 'bg-red-500',
                bar: 'bg-red-500',
                iconSvg: '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>',
            },
        };
        const style = styles[type] || styles.error;
        let stack = document.getElementById('flash-toast-stack');

        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'flash-toast-stack';
            stack.setAttribute('aria-live', 'polite');
            stack.setAttribute('aria-atomic', 'true');
            stack.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:99999;display:flex;flex-direction:column;gap:0.75rem;width:min(100vw - 2rem,420px);pointer-events:none;';
            document.body.appendChild(stack);
        }

        const toast = document.createElement('div');
        toast.className = 'flash-toast border ' + style.border + ' ' + style.bg;
        toast.setAttribute('data-flash-toast', '');
        toast.setAttribute('role', 'alert');
        toast.innerHTML =
            '<div class="flash-toast-body">'
            + '<div class="flash-toast-icon ' + style.iconBg + '">'
            + '<svg viewBox="0 0 24 24" aria-hidden="true">' + style.iconSvg + '</svg>'
            + '</div>'
            + '<p class="flash-toast-message ' + style.text + '">' + message.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</p>'
            + '<button type="button" class="flash-toast-close" data-flash-toast-close aria-label="Tutup notifikasi">'
            + '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">'
            + '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>'
            + '</svg></button></div>'
            + '<div class="flash-toast-track"><span class="flash-toast-progress ' + style.bar + '" data-flash-toast-progress></span></div>';

        stack.appendChild(toast);

        if (typeof initFlashToast === 'function') {
            initFlashToast(toast);
        } else {
            window.setTimeout(function () {
                toast.remove();
            }, 5000);
        }
    }

    function antrianReplaceBadgeWithFinishing(wrap) {
        const span = document.createElement('span');
        span.className = 'inline-flex px-3 py-1 rounded-full text-[11px] font-semibold bg-[#CFFAFE] text-[#164E63]';
        span.textContent = 'Finishing';
        wrap.replaceWith(span);
    }

    function antrianSetStatusBadgeLoading(btn, loading) {
        const spinner = btn.querySelector('.produksi-status-badge-spinner');
        btn.classList.toggle('is-loading', loading);
        btn.disabled = loading;
        if (spinner) {
            spinner.classList.toggle('hidden', !loading);
        }
    }

    async function antrianSubmitFinishingStatus(btn) {
        const wrap = btn.closest('.produksi-status-quick');
        const row = btn.closest('tr.data-table-row');
        const menu = wrap?.querySelector('.produksi-status-quick-menu');
        const idOrder = btn.dataset.idOrder || '';

        if (!wrap || !row || idOrder === '') {
            return;
        }

        antrianStatusQuickCloseMenu();
        antrianSetStatusBadgeLoading(btn, true);

        const csrfField = document.querySelector('meta[name="csrf-field"]')?.getAttribute('content') || 'csrf_test_name';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const formData = new FormData();
        formData.append(csrfField, csrfToken);
        formData.append('id_order', idOrder);
        formData.append('new_status', 'finishing');

        try {
            const response = await fetch(antrianProduksiStatusUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const html = await response.text();
            antrianSyncCsrfFromHtml(html);
            const flashes = antrianExtractFlashFromHtml(html);
            const errorFlash = flashes.find(function (f) { return f.type === 'error'; });
            const successFlash = flashes.find(function (f) { return f.type === 'success'; });

            if (errorFlash) {
                antrianSetStatusBadgeLoading(btn, false);
                antrianShowClientToast('error', errorFlash.message);
                return;
            }

            row.dataset.status = 'finishing';
            antrianReplaceBadgeWithFinishing(wrap);
            if (menu) {
                menu.remove();
            }

            if (successFlash) {
                antrianShowClientToast('success', successFlash.message);
            }

            if (antrianTableApi) {
                antrianTableApi.applyTableState();
            }
        } catch (err) {
            antrianSetStatusBadgeLoading(btn, false);
            antrianShowClientToast('error', 'Gagal memperbarui status pesanan. Periksa koneksi lalu coba lagi.');
        }
    }

    function antrianInitStatusQuickTransition() {
        document.querySelectorAll('[data-status-quick-trigger]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (btn.disabled || btn.classList.contains('is-loading')) {
                    return;
                }

                const menu = btn.closest('.produksi-status-quick')?.querySelector('.produksi-status-quick-menu');
                if (!menu) {
                    return;
                }

                if (antrianStatusQuickOpenBtn === btn && menu.classList.contains('is-open')) {
                    antrianStatusQuickCloseMenu();
                    return;
                }

                antrianStatusQuickCloseMenu();
                antrianPositionStatusQuickMenu(menu, btn);
                btn.setAttribute('aria-expanded', 'true');
                antrianStatusQuickOpenMenu = menu;
                antrianStatusQuickOpenBtn = btn;
            });
        });

        document.querySelectorAll('.produksi-status-quick-option[data-status-target="finishing"]').forEach(function (option) {
            option.addEventListener('click', function (e) {
                e.stopPropagation();
                const wrap = option.closest('.produksi-status-quick');
                const btn = wrap?.querySelector('[data-status-quick-trigger]');
                if (btn) {
                    antrianSubmitFinishingStatus(btn);
                }
            });
        });

        document.querySelectorAll('.produksi-status-quick-menu').forEach(function (menu) {
            menu.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        });
    }

    window.adminDataTableConfig = {
        searchId: 'antrianDesainSearch',
        kodeFilterId: 'antrianKodeFilter',
        dateFromId: 'antrianDeadlineFrom',
        dateToId: 'antrianDeadlineTo',
        tbodyId: 'antrianDesainBody',
        emptyFilterRowId: 'antrianEmptyFilter',
        entriesId: 'antrianEntriesSelect',
        entriesInfoId: 'antrianEntriesInfo',
        paginationId: 'antrianTablePagination',
        prevPageId: 'antrianPrevPageBtn',
        nextPageId: 'antrianNextPageBtn',
        pageInfoId: 'antrianPageInfo',
        rowSelector: 'tr.data-table-row',
        enableSort: true,
        getTabFilter: function (row) {
            return antrianStatusFilter === '' || row.dataset.status === antrianStatusFilter;
        },
        onReady: function (api) {
            antrianTableApi = api;

            document.addEventListener('click', function () {
                antrianStatusQuickCloseMenu();
            });

            window.addEventListener('resize', function () {
                antrianStatusQuickCloseMenu();
            });
            window.addEventListener('scroll', function () {
                antrianStatusQuickCloseMenu();
            }, true);

            antrianSyncFilterActiveState();

            if (antrianEnableStatusQuick) {
                antrianInitStatusQuickTransition();
            }

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
<script>
    (() => {
        const toggle = document.getElementById('antrianFilterToggle');
        const panel = document.getElementById('antrianFilterPanel');
        const applyBtn = document.getElementById('antrianFilterApply');
        if (!toggle || !panel) {
            return;
        }

        const setOpen = (open) => {
            panel.classList.toggle('hidden', !open);
            toggle.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) {
                antrianSyncFilterDraftFromActive();
            }
        };

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            setOpen(panel.classList.contains('hidden'));
        });

        panel.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        if (applyBtn) {
            applyBtn.addEventListener('click', () => {
                antrianApplyPanelFilter();
            });
        }

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
<?= view('partials/admin_data_table_scripts') ?>
<?= $this->endSection() ?>
