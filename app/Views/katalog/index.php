<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Kelola Katalog') ?><?= $this->endSection() ?>
<?php $readOnly = (bool) ($readOnly ?? false); ?>
<?= $this->section('page_title') ?><?= esc($readOnly ? 'Katalog Produk' : 'Kelola Katalog') ?><?= $this->endSection() ?>

<?= $this->section('banner_title') ?>Katalog Produk dan Layanan<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>
<?= $readOnly
    ? 'Pantau seluruh produk dan template pesanan yang tersedia.'
    : 'Kelola seluruh produk dan layanan cetak yang tersedia' ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .filter-select,
    .search-control {
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        color: #4A5568;
        background-color: #fff;
        transition: border-color .2s, box-shadow .2s;
    }

    .filter-select {
        padding: 8px 32px 8px 12px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%238896A5' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 14px;
        appearance: none;
        min-width: 130px;
    }

    .search-control {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        min-width: 200px;
        max-width: 280px;
    }

    .search-control:focus-within {
        border-color: #2E5CE6;
        box-shadow: 0 0 0 3px rgba(46, 92, 230, .1);
    }

    .search-control input {
        min-width: 0;
        flex: 1;
        border: 0;
        background: transparent;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        color: #4A5568;
        outline: none;
    }

    .search-control input::placeholder {
        color: #8896A5;
    }

    .filter-select:focus {
        border-color: #2E5CE6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(46, 92, 230, .1);
    }

    .entries-select {
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        padding: 6px 28px 6px 10px;
        color: #4A5568;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%238896A5' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") no-repeat right 8px center;
        background-size: 14px;
        appearance: none;
    }

    .entries-select:focus {
        border-color: #2E5CE6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(46, 92, 230, .1);
    }

    .status-badge-inactive {
        background: #F1F5F9;
        color: #64748B;
    }

    .sortable-th {
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
    }

    .sortable-th:hover {
        background: rgba(255, 255, 255, 0.08);
    }

    .sort-icon {
        display: inline-block;
        margin-left: 4px;
        opacity: 0.45;
        font-size: 10px;
    }

    .sortable-th.is-sorted .sort-icon {
        opacity: 1;
    }

    .action-menu-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        border: 1.5px solid #E2E8F0;
        background: #fff;
        color: #4A5568;
        transition: background .2s, border-color .2s, color .2s;
    }

    .action-menu-btn:hover,
    .action-menu-btn.is-open {
        background: #F8FAFF;
        border-color: #2E5CE6;
        color: #051747;
    }

    .action-dropdown {
        position: absolute;
        right: 0;
        top: calc(100% + 6px);
        z-index: 30;
        min-width: 168px;
        padding: 6px;
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        box-shadow: 0 12px 40px rgba(15, 23, 43, .12);
    }

    .action-dropdown a,
    .action-dropdown button {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 500;
        color: #4A5568;
        text-align: left;
        transition: background .15s, color .15s;
    }

    .action-dropdown a:hover,
    .action-dropdown button:hover {
        background: #F8FAFF;
        color: #051747;
    }

    .action-dropdown .action-danger:hover {
        background: #FEF2F2;
        color: #DC2626;
    }

    #katalogConfirmModal {
        opacity: 0;
        transition: opacity .25s ease;
    }

    #katalogConfirmModal.is-open {
        opacity: 1;
    }

    #katalogConfirmModal .katalog-confirm-panel {
        transform: scale(.95);
        opacity: 0;
        transition: transform .25s ease, opacity .25s ease;
    }

    #katalogConfirmModal.is-open .katalog-confirm-panel {
        transform: scale(1);
        opacity: 1;
    }

    #katalogConfirmModal .katalog-confirm-card:hover {
        transform: none;
    }

    .btn-katalog-confirm-cancel {
        background: #fff;
        color: #64748B;
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        font-weight: 600;
        transition: background-color .2s ease, border-color .2s ease, color .2s ease;
    }

    .btn-katalog-confirm-cancel:hover {
        background: #F8FAFC;
        border-color: #CBD5E1;
        color: #475569;
    }

    .btn-katalog-confirm-submit {
        background: #EF4444;
        color: #fff;
        border-radius: 14px;
        font-weight: 700;
        transition: background-color .2s ease;
    }

    .btn-katalog-confirm-submit:hover {
        background: #DC2626;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
/**
 * @var list<array<string, mixed>> $katalog
 * @var string                     $title
 */
$kategoriBadges = [
    'desain_grafis' => ['label' => 'Desain Grafis', 'class' => 'bg-purple-100 text-purple-800'],
    'cetak_digital' => ['label' => 'Cetak Digital', 'class' => 'bg-blue-100 text-blue-800'],
    'cetak_offset'  => ['label' => 'Cetak Offset', 'class' => 'bg-orange-100 text-orange-800'],
    'media_promosi' => ['label' => 'Media Promosi', 'class' => 'bg-green-100 text-green-800'],
];
?>

<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-end mb-4">
    <?php if (!$readOnly): ?>
        <a href="<?= site_url('katalog/tambah') ?>" class="btn-primary inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-sm text-white shrink-0">
            <?= view('partials/ui_svg_icon', ['icon' => 'plus', 'class' => 'h-4 w-4 shrink-0']) ?>
            Tambah Produk
        </a>
    <?php endif; ?>
</div>

<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4">
    <label for="kelolaSearchInput" class="sr-only">Cari produk</label>
    <div class="search-control">
        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
        </svg>
        <input
            id="kelolaSearchInput"
            type="search"
            placeholder="Cari nama produk..."
            autocomplete="off">
    </div>

    <div class="flex flex-wrap items-center justify-end gap-2">
        <select id="filterKategori" class="filter-select" aria-label="Filter kategori">
            <option value="all">Semua Kategori</option>
            <?php foreach ($kategoriBadges as $key => $badge): ?>
                <option value="<?= esc($key) ?>"><?= esc($badge['label']) ?></option>
            <?php endforeach; ?>
        </select>

        <select id="filterForm" class="filter-select" aria-label="Filter form fields">
            <option value="all">Semua Form</option>
            <option value="umum">Umum</option>
            <option value="custom">Ada Field</option>
        </select>

        <select id="filterStatus" class="filter-select" aria-label="Filter status">
            <option value="all">Semua Status</option>
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
        </select>
    </div>
</div>

<div class="admin-data-table-wrap">
    <div class="table-responsive">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold">No</th>
                    <th class="px-4 py-3 text-left font-semibold w-20">Gambar</th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="nama">
                        Nama Produk<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="kategori">
                        Kategori<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="harga">
                        Harga Dasar<span class="sort-icon">↕</span>
                    </th>
                    <th class="px-4 py-3 text-left font-semibold">Min Order</th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="kuota">
                        Kuota Revisi<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="form">
                        Form Fields<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="status">
                        Status<span class="sort-icon">↕</span>
                    </th>
                    <th class="px-4 py-3 text-left font-semibold w-12"></th>
                </tr>
            </thead>
            <tbody id="katalogTableBody">
                <?php if ($katalog === []): ?>
                    <tr id="emptyDataRow">
                        <td colspan="10" class="py-16 text-center">
                            <div class="text-4xl mb-3">📋</div>
                            <p class="text-sm font-medium text-slate-500">Belum ada produk katalog</p>
                            <p class="text-xs text-slate-400 mt-1">
                                <?= $readOnly ? 'Belum ada produk yang terdaftar.' : 'Tambahkan produk pertama Anda.' ?>
                            </p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($katalog as $index => $k): ?>
                        <?php
                        $kategoriKey   = (string) ($k['kategori'] ?? '');
                        $badge         = $kategoriBadges[$kategoriKey] ?? ['label' => str_replace('_', ' ', $kategoriKey), 'class' => 'bg-slate-100 text-slate-600'];
                        $formCount     = (int) ($k['form_count'] ?? 0);
                        $isActive      = (int) ($k['is_active'] ?? 0) === 1;
                        $hargaDasar    = (float) ($k['harga_dasar'] ?? 0);
                        $kuotaRevisi   = (int) ($k['kuota_revisi_default'] ?? 0);
                        $namaProduk    = (string) ($k['nama_produk'] ?? '-');
                        $searchText    = mb_strtolower(trim($namaProduk . ' ' . $kategoriKey . ' ' . $badge['label']));
                        $idKatalog     = (int) ($k['id_katalog'] ?? 0);
                        ?>
                        <tr
                            class="katalog-row border-b border-slate-100 hover:bg-[#F8FAFF] transition-colors"
                            data-search="<?= esc($searchText) ?>"
                            data-nama="<?= esc(mb_strtolower($namaProduk)) ?>"
                            data-kategori="<?= esc($kategoriKey) ?>"
                            data-form="<?= esc((string) $formCount) ?>"
                            data-status="<?= $isActive ? '1' : '0' ?>"
                            data-harga="<?= esc((string) $hargaDasar) ?>"
                            data-kuota="<?= esc((string) $kuotaRevisi) ?>">
                            <td class="row-num px-4 py-3.5 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3.5">
                                <?php if (!empty($k['gambar'])): ?>
                                    <?php $gambarUrl = base_url('uploads/katalog/' . $k['gambar']); ?>
                                    <button
                                        type="button"
                                        class="group relative block rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/40"
                                        data-katalog-zoom="<?= esc($gambarUrl) ?>"
                                        data-katalog-zoom-alt="<?= esc($namaProduk) ?>"
                                        aria-label="Perbesar foto <?= esc($namaProduk) ?>">
                                        <img
                                            src="<?= esc($gambarUrl) ?>"
                                            alt="<?= esc($namaProduk) ?>"
                                            class="w-14 h-14 rounded-xl object-cover border border-slate-100 bg-slate-50 cursor-zoom-in transition-opacity group-hover:opacity-90">
                                    </button>
                                <?php else: ?>
                                    <div class="w-14 h-14 rounded-xl border border-slate-100 bg-slate-50 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3.5 font-semibold text-[#051747]">
                                <?= esc($namaProduk) ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc($badge['class']) ?>">
                                    <?= esc($badge['label']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-medium text-[#051747]">
                                Rp <?= esc(number_format($hargaDasar, 0, ',', '.')) ?>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">
                                <?= esc((string) ($k['min_order'] ?? 0)) ?> <?= esc((string) ($k['satuan'] ?? '')) ?>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">
                                <?= esc((string) $kuotaRevisi) ?>x
                            </td>
                            <td class="px-4 py-3.5">
                                <?php if ($formCount > 0): ?>
                                    <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-800">
                                        <?= esc((string) $formCount) ?> field
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">
                                        Umum
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <?php if ($isActive): ?>
                                    <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                        ● Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold status-badge-inactive">
                                        ● Nonaktif
                                    </span>
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
                                        <?php if ($readOnly): ?>
                                            <a href="<?= site_url('katalog/detail/' . $idKatalog) ?>" role="menuitem">
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Lihat Detail
                                            </a>
                                            <a href="<?= site_url('form-template/' . $idKatalog) ?>" role="menuitem">
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Lihat Form
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= site_url('katalog/edit/' . $idKatalog) ?>" role="menuitem">
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Ubah
                                            </a>
                                            <a href="<?= site_url('form-template/' . $idKatalog) ?>" role="menuitem">
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Form
                                            </a>
                                            <button
                                                type="button"
                                                class="<?= $isActive ? 'action-danger' : '' ?>"
                                                role="menuitem"
                                                data-open-katalog-confirm
                                                data-confirm-type="<?= $isActive ? 'nonaktif' : 'aktif' ?>"
                                                data-confirm-id="<?= $idKatalog ?>"
                                                data-confirm-name="<?= esc($namaProduk) ?>">
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                                <?= $isActive ? 'Nonaktifkan' : 'Aktifkan' ?>
                                            </button>
                                            <button
                                                type="button"
                                                class="action-danger"
                                                role="menuitem"
                                                data-open-katalog-confirm
                                                data-confirm-type="hapus"
                                                data-confirm-id="<?= $idKatalog ?>"
                                                data-confirm-name="<?= esc($namaProduk) ?>">
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Hapus
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="emptyFilterRow" class="hidden">
                        <td colspan="10" class="py-12 text-center">
                            <p class="text-sm font-medium text-slate-500">Tidak ada produk yang cocok dengan filter.</p>
                            <p class="text-xs text-slate-400 mt-1">Coba ubah kata kunci atau filter.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?= view('partials/admin_data_table_footer') ?>
</div>

<form id="katalogConfirmForm" action="" method="post" class="hidden">
    <?= csrf_field() ?>
</form>

<div id="katalogConfirmModal" class="fixed inset-0 z-[80] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="katalogConfirmModalTitle">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-katalog-confirm></div>
    <div class="katalog-confirm-panel katalog-confirm-card relative w-full max-w-md bg-white border border-[#E2E8F0] rounded-[20px] shadow-lg p-6 md:p-8 z-10">
        <div class="text-center">
            <div id="katalogConfirmIconWrap" class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-red-50 border border-red-100">
                <svg id="katalogConfirmIcon" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h4 id="katalogConfirmModalTitle" class="text-xl md:text-2xl font-extrabold text-[#051747] leading-tight"></h4>
            <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                Apakah Anda yakin ingin <span id="katalogConfirmVerb"></span> produk <span id="katalogConfirmName" class="font-semibold text-[#051747]"></span> ini?
            </p>
            <p id="katalogConfirmNote" class="mt-2 text-xs text-slate-400 hidden"></p>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="button" data-close-katalog-confirm class="btn-katalog-confirm-cancel flex-1 h-11 text-sm">
                Batal
            </button>
            <button type="button" id="katalogConfirmSubmitBtn" class="btn-katalog-confirm-submit flex-1 h-11 text-sm"></button>
        </div>
    </div>
</div>

<?= $this->include('partials/katalog_gambar_zoom') ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function() {
        const searchInput = document.getElementById('kelolaSearchInput');
        const filterKategori = document.getElementById('filterKategori');
        const filterForm = document.getElementById('filterForm');
        const filterStatus = document.getElementById('filterStatus');
        const entriesSelect = document.getElementById('entriesSelect');
        const entriesInfo = document.getElementById('entriesInfo');
        const tbody = document.getElementById('katalogTableBody');
        const emptyFilterRow = document.getElementById('emptyFilterRow');
        const sortableHeaders = document.querySelectorAll('.sortable-th');
        const tablePagination = document.getElementById('tablePagination');
        const prevPageBtn = document.getElementById('prevPageBtn');
        const nextPageBtn = document.getElementById('nextPageBtn');
        const pageInfo = document.getElementById('pageInfo');

        let sortColumn = null;
        let sortDir = 'asc';
        let currentPage = 1;

        function closeAllActionMenus(exceptBtn) {
            document.querySelectorAll('[data-action-toggle]').forEach((btn) => {
                if (btn === exceptBtn) {
                    return;
                }
                btn.classList.remove('is-open');
                btn.setAttribute('aria-expanded', 'false');
                btn.closest('.action-menu')?.querySelector('.action-dropdown')?.classList.add('hidden');
            });
        }

        document.querySelectorAll('[data-action-toggle]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const menu = btn.closest('.action-menu');
                const dropdown = menu?.querySelector('.action-dropdown');
                const isOpen = !dropdown?.classList.contains('hidden');

                closeAllActionMenus();

                if (!isOpen && dropdown) {
                    dropdown.classList.remove('hidden');
                    btn.classList.add('is-open');
                    btn.setAttribute('aria-expanded', 'true');
                }
            });
        });

        document.addEventListener('click', () => closeAllActionMenus());

        function getRows() {
            return tbody ? [...tbody.querySelectorAll('tr.katalog-row')] : [];
        }

        function rowMatches(row) {
            const q = (searchInput?.value || '').trim().toLowerCase();
            const kategori = filterKategori?.value || 'all';
            const form = filterForm?.value || 'all';
            const status = filterStatus?.value || 'all';
            const formCount = parseInt(row.dataset.form || '0', 10);

            const matchSearch = q === '' || (row.dataset.search || '').includes(q);
            const matchKategori = kategori === 'all' || row.dataset.kategori === kategori;
            const matchForm = form === 'all' ||
                (form === 'umum' && formCount === 0) ||
                (form === 'custom' && formCount > 0);
            const matchStatus = status === 'all' || row.dataset.status === status;

            return matchSearch && matchKategori && matchForm && matchStatus;
        }

        function compareRows(a, b) {
            let va;
            let vb;

            switch (sortColumn) {
                case 'nama':
                    va = a.dataset.nama || '';
                    vb = b.dataset.nama || '';
                    break;
                case 'kategori':
                    va = a.dataset.kategori || '';
                    vb = b.dataset.kategori || '';
                    break;
                case 'harga':
                    va = parseFloat(a.dataset.harga || '0');
                    vb = parseFloat(b.dataset.harga || '0');
                    break;
                case 'kuota':
                    va = parseInt(a.dataset.kuota || '0', 10);
                    vb = parseInt(b.dataset.kuota || '0', 10);
                    break;
                case 'form':
                    va = parseInt(a.dataset.form || '0', 10);
                    vb = parseInt(b.dataset.form || '0', 10);
                    break;
                case 'status':
                    va = parseInt(a.dataset.status || '0', 10);
                    vb = parseInt(b.dataset.status || '0', 10);
                    break;
                default:
                    return 0;
            }

            if (va < vb) return sortDir === 'asc' ? -1 : 1;
            if (va > vb) return sortDir === 'asc' ? 1 : -1;
            return 0;
        }

        function updateSortIcons() {
            sortableHeaders.forEach((th) => {
                const col = th.dataset.sort;
                const icon = th.querySelector('.sort-icon');
                th.classList.toggle('is-sorted', col === sortColumn);

                if (!icon) return;
                icon.textContent = col !== sortColumn ? '↕' : (sortDir === 'asc' ? '↑' : '↓');
            });
        }

        function applyTableState() {
            const rows = getRows();
            if (!rows.length || !tbody) return;

            if (sortColumn) {
                rows.sort(compareRows);
                rows.forEach((row) => tbody.insertBefore(row, emptyFilterRow || null));
            }

            const matchedRows = rows.filter(rowMatches);
            const pageSize = parseInt(entriesSelect?.value || '10', 10);
            const totalMatched = matchedRows.length;
            const totalPages = Math.max(1, Math.ceil(totalMatched / pageSize));

            if (currentPage > totalPages) {
                currentPage = totalPages;
            }

            const startIndex = (currentPage - 1) * pageSize;
            const endIndex = startIndex + pageSize;

            rows.forEach((row) => {
                row.style.display = 'none';
            });

            matchedRows.forEach((row, index) => {
                row.style.display = (index >= startIndex && index < endIndex) ? '' : 'none';
            });

            if (emptyFilterRow) {
                emptyFilterRow.classList.toggle('hidden', totalMatched > 0);
            }

            let visibleNum = startIndex + 1;
            matchedRows.slice(startIndex, endIndex).forEach((row) => {
                const cell = row.querySelector('.row-num');
                if (cell) cell.textContent = String(visibleNum++);
            });

            if (entriesInfo) {
                if (totalMatched === 0) {
                    entriesInfo.textContent = 'Menampilkan 0 data';
                } else {
                    const from = startIndex + 1;
                    const to = Math.min(endIndex, totalMatched);
                    entriesInfo.textContent = `Menampilkan ${from} sampai ${to} dari ${totalMatched} data`;
                }
            }

            if (tablePagination) {
                const showPagination = totalMatched > pageSize;
                tablePagination.classList.toggle('hidden', !showPagination);
                tablePagination.classList.toggle('flex', showPagination);
            }

            if (pageInfo) {
                pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
            }

            if (prevPageBtn) {
                prevPageBtn.disabled = currentPage <= 1;
            }

            if (nextPageBtn) {
                nextPageBtn.disabled = currentPage >= totalPages;
            }

            updateSortIcons();
        }

        sortableHeaders.forEach((th) => {
            th.addEventListener('click', () => {
                const col = th.dataset.sort;
                if (sortColumn === col) {
                    sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    sortColumn = col;
                    sortDir = 'asc';
                }
                currentPage = 1;
                applyTableState();
            });
        });

        [searchInput, filterKategori, filterForm, filterStatus].forEach((el) => {
            if (!el) return;
            el.addEventListener('input', () => {
                currentPage = 1;
                applyTableState();
            });
            el.addEventListener('change', () => {
                currentPage = 1;
                applyTableState();
            });
        });

        if (entriesSelect) {
            entriesSelect.addEventListener('change', () => {
                currentPage = 1;
                applyTableState();
            });
        }

        if (searchInput) {
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    currentPage = 1;
                    applyTableState();
                }
            });
        }

        if (prevPageBtn) {
            prevPageBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    applyTableState();
                }
            });
        }

        if (nextPageBtn) {
            nextPageBtn.addEventListener('click', () => {
                currentPage++;
                applyTableState();
            });
        }

        applyTableState();

        const confirmModal = document.getElementById('katalogConfirmModal');
        const confirmForm = document.getElementById('katalogConfirmForm');
        const confirmSubmitBtn = document.getElementById('katalogConfirmSubmitBtn');
        const confirmTitleEl = document.getElementById('katalogConfirmModalTitle');
        const confirmVerbEl = document.getElementById('katalogConfirmVerb');
        const confirmNameEl = document.getElementById('katalogConfirmName');
        const confirmNoteEl = document.getElementById('katalogConfirmNote');
        const confirmIconWrap = document.getElementById('katalogConfirmIconWrap');
        const confirmIconEl = document.getElementById('katalogConfirmIcon');
        let confirmCloseTimer = null;

        const confirmConfig = {
            hapus: {
                title: 'Hapus Produk?',
                verb: 'hapus',
                submit: 'Ya, Hapus',
                url: '<?= site_url('katalog/hapus/') ?>',
                note: 'Produk dengan riwayat pesanan akan dinonaktifkan.',
                iconWrapClass: 'bg-red-50 border-red-100',
                iconClass: 'text-red-500',
                iconPath: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
            },
            nonaktif: {
                title: 'Nonaktifkan Produk?',
                verb: 'nonaktifkan',
                submit: 'Ya, Nonaktifkan',
                url: '<?= site_url('katalog/toggle/') ?>',
                iconWrapClass: 'bg-amber-50 border-amber-100',
                iconClass: 'text-amber-500',
                iconPath: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636',
            },
            aktif: {
                title: 'Aktifkan Produk?',
                verb: 'aktifkan',
                submit: 'Ya, Aktifkan',
                url: '<?= site_url('katalog/toggle/') ?>',
                iconWrapClass: 'bg-emerald-50 border-emerald-100',
                iconClass: 'text-emerald-500',
                iconPath: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            },
        };

        const closeConfirmModal = () => {
            if (!confirmModal) return;
            confirmModal.classList.remove('is-open');
            confirmCloseTimer = setTimeout(() => {
                confirmModal.classList.add('hidden');
                confirmModal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
                confirmCloseTimer = null;
            }, 250);
        };

        const openConfirmModal = (type, id, name) => {
            if (!confirmModal || !confirmForm) return;

            const config = confirmConfig[type];
            if (!config) return;

            if (confirmCloseTimer) {
                clearTimeout(confirmCloseTimer);
                confirmCloseTimer = null;
            }

            confirmForm.action = config.url + id;

            if (confirmTitleEl) {
                confirmTitleEl.textContent = config.title;
            }

            if (confirmVerbEl) {
                confirmVerbEl.textContent = config.verb;
            }

            if (confirmNameEl) {
                confirmNameEl.textContent = name;
            }

            if (confirmNoteEl) {
                if (config.note) {
                    confirmNoteEl.textContent = config.note;
                    confirmNoteEl.classList.remove('hidden');
                } else {
                    confirmNoteEl.textContent = '';
                    confirmNoteEl.classList.add('hidden');
                }
            }

            if (confirmSubmitBtn) {
                confirmSubmitBtn.textContent = config.submit;
            }

            if (confirmIconWrap) {
                confirmIconWrap.className = 'mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full border ' + config.iconWrapClass;
            }

            if (confirmIconEl) {
                confirmIconEl.className = 'h-7 w-7 ' + config.iconClass;
                confirmIconEl.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="' + config.iconPath + '" />';
            }

            closeAllActionMenus();
            confirmModal.classList.remove('hidden');
            confirmModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');

            requestAnimationFrame(() => {
                confirmModal.classList.add('is-open');
            });
        };

        document.querySelectorAll('[data-open-katalog-confirm]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                openConfirmModal(
                    btn.dataset.confirmType || '',
                    btn.dataset.confirmId || '',
                    btn.dataset.confirmName || ''
                );
            });
        });

        document.querySelectorAll('[data-close-katalog-confirm]').forEach((btn) => {
            btn.addEventListener('click', closeConfirmModal);
        });

        if (confirmSubmitBtn && confirmForm) {
            confirmSubmitBtn.addEventListener('click', () => {
                confirmForm.submit();
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && confirmModal?.classList.contains('is-open')) {
                closeConfirmModal();
            }
        });
    })();
</script>
<?= $this->endSection() ?>