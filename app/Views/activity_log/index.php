<?php

/**
 * @var string                       $title
 * @var string                       $page_title
 * @var list<array<string, mixed>>   $logs
 * @var array<string, string>        $filters
 * @var array<string, string>        $modulOptions
 * @var array<string, string>        $aksiLabels
 * @var string                       $retentionFrom
 * @var string                       $retentionTo
 */
$filterDari   = (string) ($filters['dari'] ?? '');
$filterSampai = (string) ($filters['sampai'] ?? '');
$filterModul  = (string) ($filters['modul'] ?? '');
$filterCari   = (string) ($filters['cari'] ?? '');
$retentionFrom = (string) ($retentionFrom ?? date('Y-m-01'));
$retentionTo   = (string) ($retentionTo ?? date('Y-m-d'));
$retentionFromLabel = date('d M Y', strtotime($retentionFrom));
$retentionToLabel   = date('d M Y', strtotime($retentionTo));
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title) ?><?= $this->endSection() ?>

<?= $this->section('banner_title') ?>Riwayat Aktivitas<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>Log internal sistem · hanya dapat diakses Owner<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- <p class="text-xs text-slate-500 mb-4">
    Retensi otomatis: menampilkan aktivitas sejak
    <span class="font-semibold text-slate-700"><?= esc($retentionFromLabel) ?></span>
    sampai
    <span class="font-semibold text-slate-700"><?= esc($retentionToLabel) ?></span>.
    Data lebih lama dihapus otomatis (awal bulan berjalan, atau 7 hari ke belakang saat ganti bulan).
</p> -->

<p class="text-xs text-slate-500 mb-4">
    Retensi otomatis: menampilkan aktivitas 7 hari terakhir (<?= esc($retentionFromLabel) ?>–<?= esc($retentionToLabel) ?>).
    Data yang lebih lama akan dihapus secara otomatis.
</p>

<form method="get" action="<?= esc(site_url('riwayat-aktivitas')) ?>" class="flex gap-3 mb-5 flex-wrap items-end">
    <select name="modul" onchange="this.form.submit()"
        class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
        <?php foreach ($modulOptions as $value => $label): ?>
            <option value="<?= esc($value) ?>" <?= $filterModul === $value ? 'selected' : '' ?>>
                <?= esc($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="list-pemesanan-date-range">
        <label for="activityDateFrom" class="list-pemesanan-date-label">Dari</label>
        <input
            id="activityDateFrom"
            name="dari"
            type="date"
            value="<?= esc($filterDari) ?>"
            min="<?= esc($retentionFrom) ?>"
            max="<?= esc($retentionTo) ?>"
            onchange="this.form.submit()"
            class="list-pemesanan-date-input"
            aria-label="Filter tanggal mulai">
        <span class="list-pemesanan-date-sep" aria-hidden="true">-</span>
        <label for="activityDateTo" class="list-pemesanan-date-label">Sampai</label>
        <input
            id="activityDateTo"
            name="sampai"
            type="date"
            value="<?= esc($filterSampai) ?>"
            min="<?= esc($retentionFrom) ?>"
            max="<?= esc($retentionTo) ?>"
            onchange="this.form.submit()"
            class="list-pemesanan-date-input"
            aria-label="Filter tanggal akhir">
    </div>
    <input type="text" name="cari" placeholder="Cari keterangan atau nama pengguna..."
        value="<?= esc($filterCari) ?>"
        class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm flex-1 min-w-[220px] focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
    <button type="submit"
        class="bg-[#051747] text-white rounded-full text-sm font-bold px-4 py-2 hover:bg-[#2E5CE6] transition-colors">
        Cari
    </button>
</form>

<?php if (empty($logs)): ?>
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-8 text-center">
        <p class="text-slate-500 text-sm">Belum ada aktivitas tercatat<?= ($filterDari !== '' || $filterSampai !== '' || $filterModul !== '' || $filterCari !== '') ? ' sesuai filter' : '' ?>.</p>
    </div>
<?php else: ?>
    <div class="admin-data-table-wrap">
        <div class="table-responsive">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#051747] text-white text-xs uppercase">
                        <th class="px-4 py-3 text-left font-semibold w-12">No</th>
                        <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="waktu">
                            Waktu<span class="sort-icon">↕</span>
                        </th>
                        <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="nama">
                            Nama<span class="sort-icon">↕</span>
                        </th>
                        <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="role">
                            Role<span class="sort-icon">↕</span>
                        </th>
                        <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="aksi">
                            Aksi<span class="sort-icon">↕</span>
                        </th>
                        <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="modul">
                            Modul<span class="sort-icon">↕</span>
                        </th>
                        <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="keterangan">
                            Keterangan<span class="sort-icon">↕</span>
                        </th>
                    </tr>
                </thead>
                <tbody id="activityLogTableBody">
                    <?php foreach ($logs as $log): ?>
                        <?php
                        $createdAt = (string) ($log['created_at'] ?? '');
                        $ts        = $createdAt !== '' ? strtotime($createdAt) : false;
                        $aksi      = (string) ($log['aksi'] ?? '');
                        $role      = (string) ($log['role'] ?? '');
                        $modul     = (string) ($log['modul'] ?? '');
                        ?>
                        <tr
                            class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF]"
                            data-waktu="<?= esc($ts !== false ? (string) $ts : '0') ?>"
                            data-nama="<?= esc(strtolower((string) ($log['nama_user'] ?? ''))) ?>"
                            data-role="<?= esc(strtolower($role)) ?>"
                            data-aksi="<?= esc(strtolower($aksi)) ?>"
                            data-modul="<?= esc(strtolower($modul)) ?>"
                            data-keterangan="<?= esc(strtolower((string) ($log['keterangan'] ?? ''))) ?>">
                            <td class="px-4 py-3 text-slate-500 row-num">0</td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                <?= $ts !== false ? esc(date('d M Y H:i', $ts)) : '-' ?>
                            </td>
                            <td class="px-4 py-3 font-medium text-[#051747]">
                                <?= esc((string) ($log['nama_user'] ?? '-')) ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <?= esc(getActivityLogRoleLabel($role)) ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold <?= esc(getActivityLogAksiBadgeClass($aksi)) ?>">
                                    <?= esc($aksiLabels[$aksi] ?? ucfirst($aksi)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 capitalize">
                                <?= esc($modulOptions[$modul] ?? $modul) ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <?= esc((string) ($log['keterangan'] ?? '')) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?= view('partials/admin_data_table_footer', [
            'entriesId'     => 'activityEntriesSelect',
            'entriesInfoId' => 'activityEntriesInfo',
            'paginationId'  => 'activityPagination',
            'prevPageId'    => 'activityPrevPageBtn',
            'nextPageId'    => 'activityNextPageBtn',
            'pageInfoId'    => 'activityPageInfo',
        ]) ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.adminDataTableConfig = {
        tbodyId: 'activityLogTableBody',
        rowSelector: 'tr.data-table-row',
        enableSort: true,
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<?= $this->endSection() ?>