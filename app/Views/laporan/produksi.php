<?php

/**
 * @var array<string, mixed> $report
 * @var array<string, string> $filters
 * @var bool                 $readOnly
 */
helper('notification');
helper('deadline');

$range         = $report['range'] ?? [];
$snapshot      = $report['snapshot'] ?? [];
$aktivitas     = $report['aktivitas'] ?? [];
$segmenTahap   = $report['segmenTahap'] ?? [];
$daftarPesanan = $report['daftarPesanan'] ?? [];
$periodSubLabel = (string) ($range['labelShort'] ?? $range['label'] ?? '-');
$laporanModel   = model(\App\Models\LaporanModel::class);

$exportQuery = http_build_query([
    'dari'     => $filters['dari'] ?? '',
    'sampai'   => $filters['sampai'] ?? '',
    'kategori' => $filters['kategori'] ?? 'semua',
]);

$kategoriOptions = [
    'semua'         => 'Semua',
    'cetak_offset'  => 'Cetak Offset',
    'cetak_digital' => 'Cetak Digital',
    'desain_grafis' => 'Desain Grafis',
    'media_promosi' => 'Media Promosi',
];

$snapshotCards = [
    [
        'label'    => 'Antrian Desain Aktif',
        'value'    => (int) ($snapshot['pesananAktif'] ?? 0),
        'subLabel' => 'Ringkasan hari ini',
        'icon'     => 'clipboard',
        'color'    => '#051747',
        'tooltip'  => 'Total pesanan dalam antrian desain (terverifikasi hingga finishing).',
    ],
    [
        'label'    => 'Tahap Desain',
        'value'    => (int) ($snapshot['tahapDesain'] ?? 0),
        'subLabel' => 'Ringkasan hari ini',
        'icon'     => 'edit',
        'color'    => '#8B5CF6',
        'tooltip'  => 'Pesanan yang berada pada tahap verifikasi, desain, atau revisi.',
    ],
    [
        'label'    => 'Tahap Cetak & Finishing',
        'value'    => (int) ($snapshot['tahapCetakFinish'] ?? 0),
        'subLabel' => 'Ringkasan hari ini',
        'icon'     => 'printer',
        'color'    => '#10B981',
        'tooltip'  => 'Pesanan yang berada pada tahap cetak/finishing.',
    ],
    [
        'label'    => 'Melewati Deadline',
        'value'    => (int) ($snapshot['melewatiDeadline'] ?? 0),
        'subLabel' => 'Ringkasan hari ini',
        'icon'     => 'clock',
        'color'    => '#EF4444',
        'tooltip'  => 'Pesanan aktif yang sudah lewat deadline.',
    ],
];

$aktivitasCards = [
    [
        'label'    => 'Total Draft Diupload',
        'value'    => (int) ($aktivitas['totalDraft'] ?? 0),
        'subLabel' => $periodSubLabel,
        'icon'     => 'layers',
        'color'    => '#051747',
        'tooltip'  => 'Total draft desain yang diunggah pada periode yang dipilih',
    ],
    [
        'label'    => 'ACC',
        'value'    => (int) ($aktivitas['acc'] ?? 0),
        'subLabel' => $periodSubLabel,
        'icon'     => 'check',
        'color'    => '#10B981',
        'tooltip'  => 'Jumlah draft yang di-ACC pelanggan.',
    ],
    [
        'label'    => 'Ditolak',
        'value'    => (int) ($aktivitas['ditolak'] ?? 0),
        'subLabel' => $periodSubLabel,
        'icon'     => 'x-circle',
        'color'    => '#EF4444',
        'tooltip'  => 'Jumlah draft ditolak pelanggan & memerlukan revisi.',
    ],
    [
        'label'    => 'Diajukan Revisi',
        'value'    => (int) ($aktivitas['diajukanRevisi'] ?? 0),
        'subLabel' => $periodSubLabel,
        'icon'     => 'edit',
        'color'    => '#F59E0B',
        'tooltip'  => 'Jumlah draft yang diajukan untuk revisi.',
    ],
    [
        'label'    => 'Menunggu Respon',
        'value'    => (int) ($aktivitas['menungguRespon'] ?? $aktivitas['uploaded'] ?? 0),
        'subLabel' => $periodSubLabel,
        'icon'     => 'clock',
        'color'    => '#6366F1',
        'tooltip'  => 'Draft yang telah diunggah, belum direspons pelanggan.',
    ],
    [
        'label'    => 'Rata-rata Penolakan/Pesanan',
        'value'    => ($aktivitas['rataRevisi'] ?? null) !== null
            ? number_format((float) $aktivitas['rataRevisi'], 1, ',', '.')
            : '—',
        'subLabel' => $periodSubLabel,
        'icon'     => 'chart',
        'color'    => '#2E5CE6',
        'isText'   => true,
        'tooltip'  => 'Rata-rata jumlah revisi/pesanan pada periode yang dipilih.',
    ],
];
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Laporan Desain<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Laporan Desain<?= $this->endSection() ?>

<?= $this->section('banner_title') ?>Laporan Desain<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>Ringkasan antrian desain hari ini dan aktivitas revisi pada periode <?= esc((string) ($range['label'] ?? '-')) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<style>
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
    .btn-laporan-filter.is-open {
        border-color: #CBD5E1;
        background: #F8FAFC;
        color: #051747;
    }

    .laporan-admin-filter-panel {
        position: absolute;
        left: 0;
        top: calc(100% + 0.5rem);
        z-index: 40;
        width: 18rem;
        padding: 1rem;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        background: #fff;
        box-shadow: 0 12px 40px rgba(15, 23, 43, 0.12);
    }

    .laporan-admin-filter-panel .filter-field+.filter-field {
        margin-top: 1rem;
    }

    .laporan-admin-filter-panel .filter-field label {
        display: block;
        margin-bottom: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748B;
    }

    .laporan-admin-filter-panel .filter-field input,
    .laporan-admin-filter-panel .filter-field select {
        width: 100%;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-2">
    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Antrian Desain Saat Ini</h3>
    <p class="text-xs text-slate-400 mt-0.5">Tidak dipengaruhi filter tanggal (mengikuti filter kategori)</p>
</div>

<?= view('dashboard/_partials/summary_cards', ['cards' => $snapshotCards]) ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 mt-6">
    <?= view('laporan/_partials/segment_donut', [
        'title'       => 'Distribusi Tahap Desain',
        'chartId'     => 'segmenTahapProduksiChart',
        'segment'     => $segmenTahap,
        'subtitle'    => 'Ringkasan antrian desain hari ini berdasarkan status pesanan aktif.',
        'showPercent' => false,
        'showRevenue' => false,
    ]) ?>
    <?= view('laporan/_partials/antrian_insight_panel', [
        'snapshot'    => $snapshot,
        'segmenTahap' => $segmenTahap,
    ]) ?>
</div>

<form method="get" action="<?= esc(site_url('laporan-produksi')) ?>" class="flex items-center justify-end gap-3 mb-6">
    <div class="relative">
        <button
            type="button"
            id="laporanProduksiFilterToggle"
            class="btn-laporan-filter"
            aria-expanded="false"
            aria-controls="laporanProduksiFilterPanel"
            aria-haspopup="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4 shrink-0" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
            </svg>
            Filter
        </button>
        <div id="laporanProduksiFilterPanel" class="laporan-admin-filter-panel hidden">
            <div class="filter-field">
                <label for="dari">Dari</label>
                <input type="date" id="dari" name="dari" value="<?= esc((string) ($filters['dari'] ?? '')) ?>"
                    class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
            </div>
            <div class="filter-field">
                <label for="sampai">Sampai</label>
                <input type="date" id="sampai" name="sampai" value="<?= esc((string) ($filters['sampai'] ?? '')) ?>"
                    class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
            </div>
            <div class="filter-field">
                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori" class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm min-w-0 focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
                    <?php foreach ($kategoriOptions as $val => $label): ?>
                        <option value="<?= esc($val) ?>" <?= ($filters['kategori'] ?? '') === $val ? 'selected' : '' ?>>
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="mt-4 w-full bg-[#051747] text-white rounded-[14px] text-sm font-bold px-4 py-2.5 hover:bg-[#2E5CE6] transition-colors">
                Terapkan Filter
            </button>
        </div>
    </div>
    <a href="<?= esc(site_url('laporan-produksi/export?' . $exportQuery)) ?>"
        class="btn-laporan-export inline-flex items-center gap-2 rounded-[14px] border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:border-slate-300 hover:bg-slate-50"
        title="Ekspor Excel"
        aria-label="Ekspor Excel">
        Ekspor
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4 shrink-0" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M7 10l5 5m0 0l5-5m-5 5V4" />
        </svg>
    </a>
</form>

<div class="mb-2">
    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Aktivitas Revisi dalam Periode</h3>
    <p class="text-xs text-slate-400 mt-0.5">Mengikuti filter tanggal dan kategori</p>
</div>

<?= view('dashboard/_partials/summary_cards', [
    'cards'     => $aktivitasCards,
    'gridClass' => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6',
]) ?>

<div class="admin-data-table-wrap mt-6">
    <div class="px-6 py-4 border-b border-slate-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-base font-bold text-[#051747]">Daftar Pesanan dengan Aktivitas Revisi</h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Riwayat draft/revisi periode <?= esc((string) ($range['label'] ?? '-')) ?> (mencakup pesanan yang sudah melewati tahap desain).
            </p>
        </div>
        <label for="laporanProduksiSearch" class="sr-only">Cari pesanan</label>
        <div class="search-control w-full sm:w-auto">
            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
            </svg>
            <input id="laporanProduksiSearch" type="search" placeholder="Cari kode order, pelanggan, produk..." autocomplete="off">
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="laporanProduksiTable">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold">No</th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="kode">
                        Kode Pesanan<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="pelanggan">
                        Pelanggan<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="produk">
                        Produk<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="kategori">
                        Kategori<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="deadline">
                        Deadline<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="status">
                        Status<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="kuota">
                        Kuota Revisi<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="penolakan">
                        Penolakan<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="versi">
                        Versi Terakhir<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="versidipilih">
                        Versi Dipilih<span class="sort-icon">↕</span>
                    </th>
                </tr>
            </thead>
            <tbody id="laporanProduksiBody">
                <?php if ($daftarPesanan === []): ?>
                    <tr>
                        <td colspan="11" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada pesanan dengan aktivitas revisi pada periode ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarPesanan as $index => $row): ?>
                        <?php
                        $status       = (string) ($row['status'] ?? '');
                        $deadlineRaw  = trim((string) ($row['deadline'] ?? ''));
                        $tsDeadline   = $deadlineRaw !== '' ? strtotime($deadlineRaw) : 0;
                        $isTelat      = !empty($row['is_telat']);
                        $kategoriKey   = (string) ($row['kategori'] ?? '');
                        $kategoriLabel = $laporanModel->kategoriLabel($kategoriKey);
                        $namaProduk    = trim((string) ($row['nama_produk'] ?? ''));
                        if ($namaProduk === '') {
                            $namaProduk = '—';
                        }
                        $sisaKuota     = (int) ($row['sisa_kuota'] ?? 0);
                        $kuotaRevisi   = (int) ($row['kuota_revisi'] ?? 0);
                        $jumlahTolak   = (int) ($row['jumlah_revisi_periode'] ?? 0);
                        $versiTerakhir = (int) ($row['versi_terakhir'] ?? 0);
                        $versiDipilih  = (int) ($row['versi_dipilih'] ?? 0);
                        $searchBlob = strtolower(implode(' ', [
                            (string) ($row['kode_order'] ?? ''),
                            (string) ($row['nama_pelanggan'] ?? ''),
                            $namaProduk,
                            $kategoriLabel,
                            $status,
                        ]));
                        ?>
                        <tr
                            class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF]"
                            data-search="<?= esc($searchBlob) ?>"
                            data-kode="<?= esc(mb_strtolower((string) ($row['kode_order'] ?? ''))) ?>"
                            data-pelanggan="<?= esc(mb_strtolower((string) ($row['nama_pelanggan'] ?? ''))) ?>"
                            data-produk="<?= esc(mb_strtolower($namaProduk)) ?>"
                            data-kategori="<?= esc(mb_strtolower($kategoriLabel)) ?>"
                            data-deadline="<?= esc((string) $tsDeadline) ?>"
                            data-status="<?= esc(mb_strtolower($status)) ?>"
                            data-kuota="<?= esc((string) $sisaKuota) ?>"
                            data-penolakan="<?= esc((string) $jumlahTolak) ?>"
                            data-versi="<?= esc((string) $versiTerakhir) ?>"
                            data-versidipilih="<?= esc((string) $versiDipilih) ?>">
                            <td class="row-num px-4 py-3 text-slate-400"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3 font-semibold text-[#051747]">
                                <a href="<?= esc(site_url('order/detail/' . ($row['kode_order'] ?? ''))) ?>" class="hover:text-[#2E5CE6]">
                                    <?= esc((string) ($row['kode_order'] ?? '-')) ?>
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-600"><?= esc((string) ($row['nama_pelanggan'] ?? '-')) ?></td>
                            <td class="px-4 py-3 text-slate-600"><?= esc($namaProduk) ?></td>
                            <td class="px-4 py-3 text-slate-600"><?= esc($kategoriLabel) ?></td>
                            <td class="px-4 py-3 whitespace-nowrap <?= $isTelat ? 'text-red-600 font-semibold' : 'text-slate-600' ?>">
                                <?php if ($deadlineRaw !== ''): ?>
                                    <?= esc(formatTanggalId($deadlineRaw)) ?>
                                    <?php if ($isTelat): ?>
                                        <span class="ml-1 text-[10px] uppercase font-bold text-red-500">Telat</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc(getStatusBadgeClass($status)) ?>">
                                    <?= esc(getOrderStatusLabel($row, 'owner')) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600"><?= esc($sisaKuota . '/' . $kuotaRevisi) ?></td>
                            <td class="px-4 py-3 font-semibold text-[#051747]"><?= esc((string) $jumlahTolak) ?></td>
                            <td class="px-4 py-3 text-slate-600">
                                <?= $versiTerakhir > 0 ? 'v' . esc((string) $versiTerakhir) : '—' ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <?php if ($versiDipilih > 0): ?>
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                        v<?= esc((string) $versiDipilih) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="laporanProduksiEmptyFilter" class="hidden">
                        <td colspan="11" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada pesanan yang cocok dengan pencarian.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($daftarPesanan !== []): ?>
        <?= view('partials/admin_data_table_footer', [
            'entriesId'     => 'laporanProduksiEntries',
            'entriesInfoId' => 'laporanProduksiEntriesInfo',
            'paginationId'  => 'laporanProduksiPagination',
            'prevPageId'    => 'laporanProduksiPrev',
            'nextPageId'    => 'laporanProduksiNext',
            'pageInfoId'    => 'laporanProduksiPageInfo',
            'entryOptions'  => [
                ['value' => '10', 'label' => '10', 'selected' => true],
                ['value' => '25', 'label' => '25'],
                ['value' => '50', 'label' => '50'],
                ['value' => '0', 'label' => 'Semua'],
            ],
        ]) ?>
    <?php endif; ?>
</div>

<p class="text-xs text-slate-400 italic mt-4">
    Hanya mencakup pesanan aktif dari tahap terverifikasi s.d finishing. Pesanan selesai/dikirim tidak dihitung.
    Menampilkan jumlah draft yang ditolak pelanggan pada periode yang dipilih.
</p>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function initSegmentDonut(canvasId, labels, counts, colors) {
        const el = document.getElementById(canvasId);
        if (!el || !labels.length) {
            return;
        }

        const total = counts.reduce((sum, val) => sum + val, 0);
        if (total <= 0) {
            return;
        }

        new Chart(el.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#FFFFFF',
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.parsed || 0;
                                return ctx.label + ': ' + val + ' pesanan';
                            }
                        }
                    }
                }
            }
        });
    }

    initSegmentDonut(
        'segmenTahapProduksiChart',
        <?= json_encode($segmenTahap['chartLabels'] ?? []) ?>,
        <?= json_encode($segmenTahap['chartCounts'] ?? []) ?>,
        <?= json_encode($segmenTahap['chartColors'] ?? []) ?>
    );

    window.adminDataTableConfig = {
        searchId: 'laporanProduksiSearch',
        tbodyId: 'laporanProduksiBody',
        emptyFilterRowId: 'laporanProduksiEmptyFilter',
        entriesId: 'laporanProduksiEntries',
        entriesInfoId: 'laporanProduksiEntriesInfo',
        paginationId: 'laporanProduksiPagination',
        prevPageId: 'laporanProduksiPrev',
        nextPageId: 'laporanProduksiNext',
        pageInfoId: 'laporanProduksiPageInfo',
        rowSelector: 'tr.data-table-row',
        enableSort: true,
    };
</script>

<script>
    (() => {
        const toggle = document.getElementById('laporanProduksiFilterToggle');
        const panel = document.getElementById('laporanProduksiFilterPanel');
        if (!toggle || !panel) {
            return;
        }

        const setOpen = (open) => {
            panel.classList.toggle('hidden', !open);
            toggle.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        };

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            setOpen(panel.classList.contains('hidden'));
        });

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
</script><?= view('partials/admin_data_table_scripts') ?>
<?= $this->endSection() ?>