<?php
/**
 * @var string                     $title
 * @var string                     $page_title
 * @var list<array<string, mixed>> $orders
 * @var string                     $role
 * @var bool                       $readOnly
 */
$orders   = $orders ?? [];
$readOnly = (bool) ($readOnly ?? false);
$role     = (string) ($role ?? '');
helper('deadline');
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Manajemen Produksi') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title ?? 'Manajemen Produksi') ?><?= $this->endSection() ?>
<?= $this->section('banner_title') ?>Manajemen Produksi<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>Daftar pesanan yang sedang dalam tahap cetak.<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<style>
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
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4">
    <p class="text-sm text-slate-500">
        Menampilkan <strong class="text-[#051747]"><?= esc((string) count($orders)) ?></strong> pesanan tahap cetak
    </p>
    <div class="relative w-full sm:w-auto">
        <button
            type="button"
            id="mpFilterToggle"
            class="btn-laporan-filter w-full sm:w-auto"
            aria-expanded="false"
            aria-controls="mpFilterPanel"
            aria-haspopup="true">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M6 10h12M9 16h6" />
            </svg>
            Filter
        </button>
        <div id="mpFilterPanel" class="laporan-admin-filter-panel hidden" role="dialog" aria-label="Filter manajemen produksi">
            <div class="filter-field">
                <label for="mpFilterKodeDraft">Kode Pesanan</label>
                <input type="text" id="mpFilterKodeDraft" placeholder="ORD-..." autocomplete="off">
            </div>
            <div class="filter-field">
                <label for="mpFilterDeadlineFromDraft">Deadline dari</label>
                <input type="date" id="mpFilterDeadlineFromDraft">
            </div>
            <div class="filter-field">
                <label for="mpFilterDeadlineToDraft">Deadline sampai</label>
                <input type="date" id="mpFilterDeadlineToDraft">
            </div>
            <div class="mt-4 flex gap-2 justify-end">
                <button type="button" id="mpFilterReset" class="px-3 py-1.5 text-xs font-semibold text-slate-600 rounded-lg border border-slate-200 hover:bg-slate-50">
                    Reset
                </button>
                <button type="button" id="mpFilterApply" class="px-3 py-1.5 text-xs font-bold text-white rounded-lg bg-[#051747] hover:bg-[#2E5CE6]">
                    Terapkan
                </button>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="admin-data-table w-full text-sm" id="mpTable">
            <thead class="bg-[#051747] text-white">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold w-14">No</th>
                    <th class="px-4 py-3 text-left font-semibold sortable-th" data-sort="kode">
                        Kode Pesanan <span class="sort-icon">↕</span>
                    </th>
                    <th class="px-4 py-3 text-left font-semibold">Pelanggan</th>
                    <th class="px-4 py-3 text-left font-semibold">Jenis Pelanggan</th>
                    <th class="px-4 py-3 text-left font-semibold">Produk</th>
                    <th class="px-4 py-3 text-left font-semibold sortable-th" data-sort="deadline">
                        Deadline <span class="sort-icon">↕</span>
                    </th>
                    <th class="px-4 py-3 text-left font-semibold w-12"></th>
                </tr>
            </thead>
            <tbody id="mpTableBody">
                <?php if ($orders === []): ?>
                    <tr>
                        <td colspan="7" class="py-16 text-center text-slate-500 text-sm">
                            Tidak ada pesanan dalam tahap cetak.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $index => $a): ?>
                        <?php
                        $idOrder       = (int) ($a['id_order'] ?? 0);
                        $kodeOrder     = (string) ($a['kode_order'] ?? '');
                        $namaPelanggan = (string) ($a['nama_pelanggan'] ?? '-');
                        $noTelp        = trim((string) ($a['no_telp'] ?? ''));
                        $jenisPelanggan = (string) ($a['jenis_pelanggan'] ?? 'perseorangan');
                        $isKerjasama   = $jenisPelanggan === 'perusahaan';
                        $jenisLabel    = $isKerjasama ? 'Kerjasama' : 'Perseorangan';
                        $namaProduk    = (int) ($a['is_custom'] ?? 0) === 1
                            ? 'Pesanan Custom'
                            : (string) ($a['nama_produk'] ?? '-');
                        $searchText    = mb_strtolower(trim($kodeOrder . ' ' . $namaPelanggan . ' ' . $noTelp . ' ' . $jenisLabel . ' ' . $namaProduk));
                        $deadline      = (string) ($a['deadline_produksi'] ?? '');
                        $tsDeadline    = $deadline !== '' ? strtotime($deadline) : 0;
                        $daysLeft      = $tsDeadline > 0 ? (int) floor(($tsDeadline - time()) / 86400) : 999;
                        $deadlineUrgent = $daysLeft <= 3 && $tsDeadline > 0;
                        $monitorUrl    = site_url('monitoring-produksi/' . $kodeOrder);
                        ?>
                        <tr class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF] transition-colors"
                            data-search="<?= esc($searchText) ?>"
                            data-kode="<?= esc(mb_strtolower($kodeOrder)) ?>"
                            data-deadline="<?= esc((string) $tsDeadline) ?>"
                            data-tanggal="<?= esc((string) $tsDeadline) ?>">
                            <td class="row-num px-4 py-3.5 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3.5 font-mono font-semibold text-[#051747] whitespace-nowrap">
                                <?= esc($kodeOrder) ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <?= view('partials/pelanggan_kontak_cell', [
                                    'nama'   => $namaPelanggan,
                                    'noTelp' => $noTelp,
                                ]) ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold <?= $isKerjasama ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' ?>">
                                    <?= esc($jenisLabel) ?>
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
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="action-menu relative inline-block">
                                    <button
                                        type="button"
                                        class="action-menu-btn"
                                        aria-label="Menu aksi"
                                        aria-expanded="false"
                                        data-action-toggle>
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle cx="12" cy="5" r="1.75" />
                                            <circle cx="12" cy="12" r="1.75" />
                                            <circle cx="12" cy="19" r="1.75" />
                                        </svg>
                                    </button>
                                    <div class="action-dropdown hidden" role="menu">
                                        <a href="<?= esc($monitorUrl) ?>" role="menuitem">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                            </svg>
                                            Monitoring Produksi
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="mpEmptyFilter" class="hidden">
                        <td colspan="7" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada pesanan yang cocok dengan filter.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($orders !== []): ?>
        <?= view('partials/admin_data_table_footer', [
            'entriesId'     => 'mpEntriesSelect',
            'entriesInfoId' => 'mpEntriesInfo',
            'paginationId'  => 'mpTablePagination',
            'prevPageId'    => 'mpPrevPageBtn',
            'nextPageId'    => 'mpNextPageBtn',
            'pageInfoId'    => 'mpPageInfo',
        ]) ?>
    <?php endif; ?>
</div>

<input type="hidden" id="mpKodeFilter" value="">
<input type="hidden" id="mpDeadlineFrom" value="">
<input type="hidden" id="mpDeadlineTo" value="">

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let mpTableApi = null;

    function mpSyncFilterActiveState() {
        const toggle = document.getElementById('mpFilterToggle');
        if (!toggle) return;
        const kodeVal = (document.getElementById('mpKodeFilter')?.value || '').trim();
        const fromVal = document.getElementById('mpDeadlineFrom')?.value || '';
        const toVal = document.getElementById('mpDeadlineTo')?.value || '';
        toggle.classList.toggle('is-active', kodeVal !== '' || fromVal !== '' || toVal !== '');
    }

    function mpSyncFilterDraftFromActive() {
        const kodeDraft = document.getElementById('mpFilterKodeDraft');
        const fromDraft = document.getElementById('mpFilterDeadlineFromDraft');
        const toDraft = document.getElementById('mpFilterDeadlineToDraft');
        const kodeActive = document.getElementById('mpKodeFilter');
        const fromActive = document.getElementById('mpDeadlineFrom');
        const toActive = document.getElementById('mpDeadlineTo');
        if (kodeDraft && kodeActive) kodeDraft.value = kodeActive.value;
        if (fromDraft && fromActive) fromDraft.value = fromActive.value;
        if (toDraft && toActive) toDraft.value = toActive.value;
    }

    function mpApplyFilterFromDraft() {
        const kodeDraft = document.getElementById('mpFilterKodeDraft');
        const fromDraft = document.getElementById('mpFilterDeadlineFromDraft');
        const toDraft = document.getElementById('mpFilterDeadlineToDraft');
        const kodeActive = document.getElementById('mpKodeFilter');
        const fromActive = document.getElementById('mpDeadlineFrom');
        const toActive = document.getElementById('mpDeadlineTo');
        if (kodeActive && kodeDraft) kodeActive.value = kodeDraft.value.trim();
        if (fromActive && fromDraft) fromActive.value = fromDraft.value;
        if (toActive && toDraft) toActive.value = toDraft.value;
        mpSyncFilterActiveState();
        const panel = document.getElementById('mpFilterPanel');
        const toggle = document.getElementById('mpFilterToggle');
        if (panel) panel.classList.add('hidden');
        if (toggle) {
            toggle.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        }
        if (mpTableApi) {
            mpTableApi.setPage(1);
            mpTableApi.applyTableState();
        }
    }

    window.adminDataTableConfig = {
        kodeFilterId: 'mpKodeFilter',
        dateFromId: 'mpDeadlineFrom',
        dateToId: 'mpDeadlineTo',
        tbodyId: 'mpTableBody',
        emptyFilterRowId: 'mpEmptyFilter',
        entriesId: 'mpEntriesSelect',
        entriesInfoId: 'mpEntriesInfo',
        paginationId: 'mpTablePagination',
        prevPageId: 'mpPrevPageBtn',
        nextPageId: 'mpNextPageBtn',
        pageInfoId: 'mpPageInfo',
        rowSelector: 'tr.data-table-row',
        enableSort: true,
        onReady: function (api) {
            mpTableApi = api;
            mpSyncFilterActiveState();
        },
    };
</script>
<script>
    (() => {
        const toggle = document.getElementById('mpFilterToggle');
        const panel = document.getElementById('mpFilterPanel');
        const applyBtn = document.getElementById('mpFilterApply');
        const resetBtn = document.getElementById('mpFilterReset');
        if (!toggle || !panel) return;

        const setOpen = (open) => {
            panel.classList.toggle('hidden', !open);
            toggle.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) mpSyncFilterDraftFromActive();
        };

        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            setOpen(panel.classList.contains('hidden'));
        });
        document.addEventListener('click', (e) => {
            if (!panel.classList.contains('hidden') && !panel.contains(e.target) && !toggle.contains(e.target)) {
                setOpen(false);
            }
        });
        applyBtn?.addEventListener('click', () => mpApplyFilterFromDraft());
        resetBtn?.addEventListener('click', () => {
            const kodeDraft = document.getElementById('mpFilterKodeDraft');
            const fromDraft = document.getElementById('mpFilterDeadlineFromDraft');
            const toDraft = document.getElementById('mpFilterDeadlineToDraft');
            if (kodeDraft) kodeDraft.value = '';
            if (fromDraft) fromDraft.value = '';
            if (toDraft) toDraft.value = '';
            mpApplyFilterFromDraft();
        });
    })();
</script>
<?= view('partials/admin_data_table_scripts') ?>
<?= $this->endSection() ?>
