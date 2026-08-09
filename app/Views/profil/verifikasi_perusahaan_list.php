<?php
/**
 * @var string                       $title
 * @var string                       $page_title
 * @var list<array<string, mixed>>   $pengajuan
 * @var string                       $filter
 * @var array<string, int>           $counts
 */
$tabs = [
    'verified' => 'Disetujui (' . ($counts['verified'] ?? 0) . ')',
    'rejected' => 'Ditolak (' . ($counts['rejected'] ?? 0) . ')',
];
$colspan = 8;
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Verifikasi Perusahaan') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title ?? 'Verifikasi Perusahaan') ?><?= $this->endSection() ?>

<?= $this->section('banner_title') ?>Verifikasi Perusahaan<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>Arsip penetapan dan pencabutan kerja sama perusahaan<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="flex flex-wrap gap-2 mb-6">
    <?php foreach ($tabs as $tabKey => $tabLabel): ?>
        <a href="<?= esc(site_url('verifikasi-perusahaan?status=' . $tabKey)) ?>"
            class="inline-flex px-4 py-2 rounded-full text-xs font-bold uppercase transition-colors <?= $filter === $tabKey
                ? 'bg-[#051747] text-white'
                : 'bg-white border border-slate-200 text-slate-600 hover:border-[#2E5CE6] hover:text-[#051747]' ?>">
            <?= esc($tabLabel) ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end mb-4">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:flex-wrap">
        <div class="flex items-center gap-2">
            <label for="verifikasiPerusahaanDateFrom" class="text-xs font-medium text-slate-500 whitespace-nowrap">Dari</label>
            <input id="verifikasiPerusahaanDateFrom" type="date" class="date-filter-input" aria-label="Filter tanggal mulai">
            <label for="verifikasiPerusahaanDateTo" class="text-xs font-medium text-slate-500 whitespace-nowrap">Sampai</label>
            <input id="verifikasiPerusahaanDateTo" type="date" class="date-filter-input" aria-label="Filter tanggal akhir">
        </div>
        <label for="verifikasiPerusahaanSearch" class="sr-only">Cari pengajuan verifikasi</label>
        <div class="search-control w-full sm:w-auto">
            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
            </svg>
            <input
                id="verifikasiPerusahaanSearch"
                type="search"
                placeholder="Cari pelanggan, perusahaan, NPWP..."
                autocomplete="off">
        </div>
    </div>
</div>

<div class="admin-data-table-wrap">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold w-12">No</th>
                    <th class="px-4 py-3 text-left font-semibold">Pelanggan</th>
                    <th class="px-4 py-3 text-left font-semibold">Perusahaan</th>
                    <th class="px-4 py-3 text-left font-semibold">NPWP</th>
                    <th class="px-4 py-3 text-left font-semibold">Dokumen</th>
                    <th class="px-4 py-3 text-left font-semibold">Dicatat</th>
                    <th class="px-4 py-3 text-left font-semibold">Diproses</th>
                    <th class="px-4 py-3 text-left font-semibold">Catatan</th>
                </tr>
            </thead>
            <tbody id="verifikasiPerusahaanBody">
                <?php if ($pengajuan === []): ?>
                    <tr>
                        <td colspan="<?= (int) $colspan ?>" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada data pada filter ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pengajuan as $index => $row): ?>
                        <?php
                        $docNpwp    = (string) ($row['dokumen_npwp'] ?? '');
                        $docKtp     = (string) ($row['dokumen_ktp_pic'] ?? '');
                        $docMou     = (string) ($row['dokumen_mou'] ?? '');
                        $statusRow  = (string) ($row['status'] ?? '');
                        $tglTs      = !empty($row['tgl_pengajuan']) ? (int) strtotime((string) $row['tgl_pengajuan']) : 0;
                        $searchBlob = strtolower(implode(' ', array_filter([
                            (string) ($row['nama_pelanggan'] ?? ''),
                            (string) ($row['email_pelanggan'] ?? ''),
                            (string) ($row['no_telp'] ?? ''),
                            (string) ($row['nama_perusahaan'] ?? ''),
                            (string) ($row['no_npwp'] ?? ''),
                            (string) ($row['nama_admin'] ?? ''),
                            (string) ($row['catatan_admin'] ?? ''),
                        ])));
                        ?>
                        <tr class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF] align-top"
                            data-search="<?= esc($searchBlob) ?>"
                            data-tanggal="<?= esc((string) $tglTs) ?>"
                            data-status="<?= esc($statusRow) ?>">
                            <td class="row-num px-4 py-3 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3">
                                <?= view('partials/pelanggan_kontak_cell', [
                                    'nama'   => (string) ($row['nama_pelanggan'] ?? '-'),
                                    'noTelp' => (string) ($row['no_telp'] ?? ''),
                                ]) ?>
                                <p class="text-xs text-slate-400 mt-1"><?= esc((string) ($row['email_pelanggan'] ?? '')) ?></p>
                            </td>
                            <td class="px-4 py-3 font-semibold text-[#051747]">
                                <?= esc((string) ($row['nama_perusahaan'] ?? '-')) ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <?= esc((string) ($row['no_npwp'] ?? '-')) ?>
                                <?php if (!empty($row['jabatan_pic'])): ?>
                                    <p class="text-xs text-slate-400 mt-1">PIC: <?= esc((string) $row['jabatan_pic']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($row['wa_perusahaan'])): ?>
                                    <p class="text-xs text-slate-400">WA: <?= esc((string) $row['wa_perusahaan']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 space-y-1">
                                <?php if ($docNpwp !== ''): ?>
                                    <a href="<?= esc(base_url('uploads/dokumen_verifikasi/' . $docNpwp)) ?>"
                                        target="_blank" rel="noopener noreferrer"
                                        class="block text-xs text-[#2E5CE6] hover:underline">NPWP</a>
                                <?php endif; ?>
                                <?php if ($docKtp !== ''): ?>
                                    <a href="<?= esc(base_url('uploads/dokumen_verifikasi/' . $docKtp)) ?>"
                                        target="_blank" rel="noopener noreferrer"
                                        class="block text-xs text-[#2E5CE6] hover:underline">KTP PIC</a>
                                <?php endif; ?>
                                <?php if ($docMou !== ''): ?>
                                    <a href="<?= esc(base_url('uploads/dokumen_verifikasi/' . $docMou)) ?>"
                                        target="_blank" rel="noopener noreferrer"
                                        class="block text-xs text-[#2E5CE6] hover:underline">MOU (PDF)</a>
                                <?php endif; ?>
                                <?php if (!empty($row['alamat_kantor'])): ?>
                                    <p class="text-[10px] text-slate-400 mt-1 leading-snug"><?= esc((string) $row['alamat_kantor']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                <?= !empty($row['tgl_pengajuan'])
                                    ? esc(date('d M Y H:i', strtotime((string) $row['tgl_pengajuan'])))
                                    : '-' ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <?php if (!empty($row['tgl_verifikasi'])): ?>
                                    <p><?= esc(date('d M Y H:i', strtotime((string) $row['tgl_verifikasi']))) ?></p>
                                    <p class="text-xs text-slate-400">oleh <?= esc((string) ($row['nama_admin'] ?? 'Admin')) ?></p>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                                <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold <?= $statusRow === 'verified'
                                    ? 'bg-emerald-100 text-emerald-800'
                                    : 'bg-red-100 text-red-800' ?>">
                                    <?= $statusRow === 'verified' ? 'Ditetapkan' : 'Dicabut' ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 text-xs">
                                <?= esc((string) ($row['catatan_admin'] ?? '-')) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="verifikasiPerusahaanEmptyFilter" class="hidden">
                        <td colspan="<?= (int) $colspan ?>" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada data yang cocok dengan pencarian atau filter tanggal.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pengajuan !== []): ?>
        <?= view('partials/admin_data_table_footer', [
            'entriesId'     => 'verifikasiPerusahaanEntries',
            'entriesInfoId' => 'verifikasiPerusahaanEntriesInfo',
            'paginationId'  => 'verifikasiPerusahaanPagination',
            'prevPageId'    => 'verifikasiPerusahaanPrevPage',
            'nextPageId'    => 'verifikasiPerusahaanNextPage',
            'pageInfoId'    => 'verifikasiPerusahaanPageInfo',
        ]) ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if ($pengajuan !== []): ?>
<script>
    window.adminDataTableConfig = {
        searchId: 'verifikasiPerusahaanSearch',
        dateFromId: 'verifikasiPerusahaanDateFrom',
        dateToId: 'verifikasiPerusahaanDateTo',
        tbodyId: 'verifikasiPerusahaanBody',
        emptyFilterRowId: 'verifikasiPerusahaanEmptyFilter',
        entriesId: 'verifikasiPerusahaanEntries',
        entriesInfoId: 'verifikasiPerusahaanEntriesInfo',
        paginationId: 'verifikasiPerusahaanPagination',
        prevPageId: 'verifikasiPerusahaanPrevPage',
        nextPageId: 'verifikasiPerusahaanNextPage',
        pageInfoId: 'verifikasiPerusahaanPageInfo',
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<?php endif; ?>
<?= $this->endSection() ?>
