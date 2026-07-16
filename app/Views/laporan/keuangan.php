<?php

/**
 * @var array<string, mixed> $report
 * @var array<string, string> $filters
 * @var bool                 $readOnly
 */
helper('notification');

$range           = $report['range'] ?? [];
$summaryCards    = $report['summaryCards'] ?? [];
$rekapJenis      = $report['rekapJenis']['rows'] ?? [];
$rekapTotals     = $report['rekapJenis']['totals'] ?? [];
$daftarTransaksi = $report['daftarTransaksi'] ?? [];
$periodSubLabel  = (string) ($range['labelShort'] ?? $range['label'] ?? '-');
$viewerRole      = $readOnly ? 'owner' : 'keuangan';

$exportQuery = http_build_query([
    'dari'   => $filters['dari'] ?? '',
    'sampai' => $filters['sampai'] ?? '',
    'jenis'  => $filters['jenis'] ?? 'semua',
    'status' => $filters['status'] ?? 'semua',
]);

$cards = [
    [
        'label'    => 'Total Pemasukan',
        'value'    => 'Rp ' . number_format((float) ($summaryCards['totalPemasukan'] ?? 0), 0, ',', '.'),
        'subLabel' => $periodSubLabel,
        'icon'     => 'cash',
        'color'    => '#051747',
        'isText'   => true,
        'tooltip'  => 'Total nominal pembayaran terverifikasi sesuai periode yang dipilih.',
    ],
    [
        'label'    => 'Pemasukan DP',
        'value'    => 'Rp ' . number_format((float) ($summaryCards['pemasukanDp'] ?? 0), 0, ',', '.'),
        'subLabel' => $periodSubLabel,
        'icon'     => 'wallet',
        'color'    => '#2E5CE6',
        'isText'   => true,
        'tooltip'  => 'Total DP terverifikasi sesuai periode yang dipilih.',
    ],
    [
        'label'    => 'Pemasukan Pelunasan',
        'value'    => 'Rp ' . number_format((float) ($summaryCards['pemasukanPelunasan'] ?? 0), 0, ',', '.'),
        'subLabel' => $periodSubLabel,
        'icon'     => 'check-circle',
        'color'    => '#10B981',
        'isText'   => true,
        'tooltip'  => 'Total pelunasan terverifikasi sesuai periode yang dipilih.',
    ],
    [
        'label'    => 'Menunggu Verifikasi',
        'value'    => (int) ($summaryCards['menungguVerifikasi'] ?? 0),
        'subLabel' => (int) ($summaryCards['menungguDp'] ?? 0) . ' DP · ' . (int) ($summaryCards['menungguPelunasan'] ?? 0) . ' Pelunasan',
        'icon'     => 'clock',
        'color'    => '#F59E0B',
        'tooltip'  => 'Jumlah seluruh pembayaran yang masih menunggu verifikasi.',
    ],
];
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Laporan Transaksi<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Laporan Transaksi<?= $this->endSection() ?>

<?= $this->section('banner_title') ?>Laporan Transaksi<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>Rekap pemasukan terverifikasi periode <?= esc((string) ($range['label'] ?? '-')) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<form method="get" action="<?= esc(site_url('laporan-keuangan')) ?>" class="flex flex-wrap gap-3 items-end mb-6">
    <div>
        <label for="dari" class="block text-xs font-semibold text-slate-500 mb-1.5">Dari</label>
        <input type="date" id="dari" name="dari" value="<?= esc((string) ($filters['dari'] ?? '')) ?>"
            class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
    </div>
    <div>
        <label for="sampai" class="block text-xs font-semibold text-slate-500 mb-1.5">Sampai</label>
        <input type="date" id="sampai" name="sampai" value="<?= esc((string) ($filters['sampai'] ?? '')) ?>"
            class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
    </div>
    <div>
        <label for="jenis" class="block text-xs font-semibold text-slate-500 mb-1.5">Jenis</label>
        <select id="jenis" name="jenis" class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm min-w-[130px] focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
            <option value="semua" <?= ($filters['jenis'] ?? '') === 'semua' ? 'selected' : '' ?>>Semua</option>
            <option value="dp" <?= ($filters['jenis'] ?? '') === 'dp' ? 'selected' : '' ?>>DP</option>
            <option value="pelunasan" <?= ($filters['jenis'] ?? '') === 'pelunasan' ? 'selected' : '' ?>>Pelunasan</option>
        </select>
    </div>
    <div>
        <label for="status" class="block text-xs font-semibold text-slate-500 mb-1.5">Status</label>
        <select id="status" name="status" class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm min-w-[140px] focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
            <option value="semua" <?= ($filters['status'] ?? '') === 'semua' ? 'selected' : '' ?>>Semua</option>
            <option value="terverifikasi" <?= ($filters['status'] ?? '') === 'terverifikasi' ? 'selected' : '' ?>>Terverifikasi</option>
            <option value="menunggu" <?= ($filters['status'] ?? '') === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
        </select>
    </div>
    <button type="submit" class="bg-[#051747] text-white rounded-full text-sm font-bold px-5 py-2.5 hover:bg-[#2E5CE6] transition-colors">
        Terapkan Filter
    </button>
    <a href="<?= esc(site_url('laporan-keuangan/export?' . $exportQuery)) ?>"
        class="inline-flex items-center justify-center border-2 border-[#051747] text-[#051747] rounded-full text-sm font-bold px-5 py-2.5 hover:bg-[#051747] hover:text-white transition-colors">
        Ekspor Excel
    </a>
</form>

<?= view('dashboard/_partials/summary_cards', ['cards' => $cards]) ?>

<div class="admin-data-table-wrap mb-6 mt-6">
    <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-base font-bold text-[#051747]">Rekap Per Jenis Pembayaran</h3>
        <p class="text-xs text-slate-500 mt-0.5">
            Rekap transaksi terverifikasi terverifikasi pada periode terpilih. Rata-rata = total nominal ÷ jumlah transaksi.
        </p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold w-12">No</th>
                    <th class="px-4 py-3 text-left font-semibold">Jenis</th>
                    <th class="px-4 py-3 text-left font-semibold">Jumlah</th>
                    <th class="px-4 py-3 text-left font-semibold">Total Nominal</th>
                    <th class="px-4 py-3 text-left font-semibold">Rata-rata/Transaksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rekapJenis === []): ?>
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada pemasukan terverifikasi pada periode ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rekapJenis as $index => $row): ?>
                        <tr class="border-b border-slate-100 hover:bg-[#F8FAFF]">
                            <td class="px-4 py-3 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3 font-medium text-[#051747]"><?= esc((string) ($row['label'] ?? '-')) ?></td>
                            <td class="px-4 py-3 text-slate-600"><?= esc((string) ($row['jumlah'] ?? 0)) ?></td>
                            <td class="px-4 py-3 font-semibold text-[#051747]">
                                Rp <?= esc(number_format((float) ($row['nominal'] ?? 0), 0, ',', '.')) ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                Rp <?= esc(number_format((float) ($row['rata_rata'] ?? 0), 0, ',', '.')) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="border-t-2 border-[#051747] bg-[#F8FAFF] font-extrabold text-[#051747]">
                        <td class="px-4 py-3"></td>
                        <td class="px-4 py-3">Total</td>
                        <td class="px-4 py-3"><?= esc((string) ($rekapTotals['jumlah'] ?? 0)) ?></td>
                        <td class="px-4 py-3">
                            Rp <?= esc(number_format((float) ($rekapTotals['nominal'] ?? 0), 0, ',', '.')) ?>
                        </td>
                        <td class="px-4 py-3">
                            Rp <?= esc(number_format((float) ($rekapTotals['rata_rata'] ?? 0), 0, ',', '.')) ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="admin-data-table-wrap">
    <div class="px-6 py-4 border-b border-slate-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-base font-bold text-[#051747]">Daftar Transaksi</h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Data ditampilkan berdasarkan tanggal verifikasi atau tanggal unggah sesuai status transaksi.
            </p>
        </div>
        <label for="laporanKeuanganSearch" class="sr-only">Cari transaksi</label>
        <div class="search-control w-full sm:w-auto">
            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
            </svg>
            <input id="laporanKeuanganSearch" type="search" placeholder="Cari kode bayar, order, pelanggan..." autocomplete="off">
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="laporanKeuanganTable">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold">No</th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="kodebayar">
                        Kode Bayar<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="kodeorder">
                        Kode Pesanan<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="pelanggan">
                        Pelanggan<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="jenis">
                        Jenis<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="nominal">
                        Nominal<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="status">
                        Status<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="upload">
                        Tgl Unggah<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="verifikasi">
                        Tgl Verifikasi<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="verifikator">
                        Diverifikasi Oleh<span class="sort-icon">↕</span>
                    </th>
                </tr>
            </thead>
            <tbody id="laporanKeuanganBody">
                <?php if ($daftarTransaksi === []): ?>
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada transaksi yang cocok dengan filter.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php
                    $laporanModel = model(\App\Models\LaporanModel::class);
                    foreach ($daftarTransaksi as $index => $row):
                        $jenis   = (string) ($row['jenis'] ?? '');
                        $status  = (string) ($row['status'] ?? '');
                        $jenisLabel = $laporanModel->paymentJenisLabel($jenis);
                        $nominal = (float) ($row['nominal'] ?? 0);
                        $tglUpload = trim((string) ($row['tgl_upload'] ?? ''));
                        $tglVerif  = trim((string) ($row['tgl_verifikasi'] ?? ''));
                        $tsUpload  = $tglUpload !== '' ? strtotime($tglUpload) : 0;
                        $tsVerif   = $tglVerif !== '' ? strtotime($tglVerif) : 0;
                        $searchBlob = strtolower(implode(' ', [
                            (string) ($row['kode_payment'] ?? ''),
                            (string) ($row['kode_order'] ?? ''),
                            (string) ($row['nama_pelanggan'] ?? ''),
                            (string) ($row['email_verifikator'] ?? ''),
                            $jenisLabel,
                            $status,
                        ]));
                        $emailVerifikator = trim((string) ($row['email_verifikator'] ?? ''));
                    ?>
                        <tr
                            class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF]"
                            data-search="<?= esc($searchBlob) ?>"
                            data-kodebayar="<?= esc(mb_strtolower((string) ($row['kode_payment'] ?? ''))) ?>"
                            data-kodeorder="<?= esc(mb_strtolower((string) ($row['kode_order'] ?? ''))) ?>"
                            data-pelanggan="<?= esc(mb_strtolower((string) ($row['nama_pelanggan'] ?? ''))) ?>"
                            data-jenis="<?= esc(mb_strtolower($jenisLabel)) ?>"
                            data-nominal="<?= esc((string) $nominal) ?>"
                            data-status="<?= esc(mb_strtolower($status)) ?>"
                            data-upload="<?= esc((string) $tsUpload) ?>"
                            data-verifikasi="<?= esc((string) $tsVerif) ?>"
                            data-verifikator="<?= esc(mb_strtolower($emailVerifikator !== '' ? $emailVerifikator : '—')) ?>">
                            <td class="row-num px-4 py-3 text-slate-400"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600"><?= esc((string) ($row['kode_payment'] ?? '-')) ?></td>
                            <td class="px-4 py-3 font-semibold text-[#051747]">
                                <a href="<?= esc(site_url('order/detail/' . ($row['kode_order'] ?? ''))) ?>" class="hover:text-[#2E5CE6]">
                                    <?= esc((string) ($row['kode_order'] ?? '-')) ?>
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-600"><?= esc((string) ($row['nama_pelanggan'] ?? '-')) ?></td>
                            <td class="px-4 py-3">
                                <?php if ($jenis === 'dp'): ?>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">DP</span>
                                <?php else: ?>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">Pelunasan</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 font-semibold text-[#051747]">
                                Rp <?= esc(number_format($nominal, 0, ',', '.')) ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold <?= esc(getPaymentRiwayatStatusBadgeClass($row)) ?>">
                                    <?= esc(getPaymentRiwayatStatusLabel($row, $viewerRole)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                <?= $tglUpload !== '' ? esc(date('d M Y H:i', $tsUpload)) : '—' ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                <?= $tglVerif !== '' ? esc(date('d M Y H:i', $tsVerif)) : '—' ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <?= $emailVerifikator !== '' ? esc($emailVerifikator) : '—' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="laporanKeuanganEmptyFilter" class="hidden">
                        <td colspan="10" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada transaksi yang cocok dengan pencarian.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($daftarTransaksi !== []): ?>
        <?= view('partials/admin_data_table_footer', [
            'entriesId'     => 'laporanKeuanganEntries',
            'entriesInfoId' => 'laporanKeuanganEntriesInfo',
            'paginationId'  => 'laporanKeuanganPagination',
            'prevPageId'    => 'laporanKeuanganPrev',
            'nextPageId'    => 'laporanKeuanganNext',
            'pageInfoId'    => 'laporanKeuanganPageInfo',
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
    Pemasukan dihitung dari pembayaran yang telah terverifikasi pada periode yang dipilih.
</p>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.adminDataTableConfig = {
        searchId: 'laporanKeuanganSearch',
        tbodyId: 'laporanKeuanganBody',
        emptyFilterRowId: 'laporanKeuanganEmptyFilter',
        entriesId: 'laporanKeuanganEntries',
        entriesInfoId: 'laporanKeuanganEntriesInfo',
        paginationId: 'laporanKeuanganPagination',
        prevPageId: 'laporanKeuanganPrev',
        nextPageId: 'laporanKeuanganNext',
        pageInfoId: 'laporanKeuanganPageInfo',
        rowSelector: 'tr.data-table-row',
        enableSort: true,
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<?= $this->endSection() ?>