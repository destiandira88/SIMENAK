<?php

    /**
     * @var array<string, mixed> $report
     * @var array<string, string> $filters
     * @var bool                 $readOnly
     */
    helper('notification');
    helper('deadline');

    $range           = $report['range'] ?? [];
    $rekapKategori   = $report['rekapKategori']['rows'] ?? [];
    $rekapTotals     = $report['rekapKategori']['totals'] ?? [];
    $daftarPesanan   = $report['daftarPesanan'] ?? [];
    $potensiAktif    = (float) ($report['potensiAktif'] ?? 0);
    $periodSubLabel  = (string) ($range['labelShort'] ?? $range['label'] ?? '-');

    $exportQuery = http_build_query([
        'dari'            => $filters['dari'] ?? '',
        'sampai'          => $filters['sampai'] ?? '',
        'status'          => $filters['status'] ?? 'semua',
        'kategori'        => $filters['kategori'] ?? 'semua',
        'jenis_pelanggan' => $filters['jenis_pelanggan'] ?? 'semua',
        'tipe_pesanan'    => $filters['tipe_pesanan'] ?? 'semua',
    ]);

    $summaryCards    = $report['summaryCards'] ?? [];

    $cards = [
        [
            'label'    => 'Total Pesanan',
            'value'    => (int) ($summaryCards['totalPesanan'] ?? 0),
            'subLabel' => $periodSubLabel,
            'icon'     => 'clipboard',
            'color'    => '#051747',
            'tooltip'  => 'Pesanan aktif, proses, dan dibatalkan dihitung berdasarkan tanggal dibuat. Pesanan selesai dihitung berdasarkan tanggal selesai.',
        ],
        [
            'label'    => 'Pesanan Masuk',
            'value'    => (int) ($summaryCards['pesananMasuk'] ?? 0),
            'subLabel' => $periodSubLabel,
            'icon'     => 'calendar',
            'color'    => '#2E5CE6',
            'tooltip'  => 'Jumlah pesanan baru yang dibuat berdasarkan periode yang dipilih.',
        ],
        [
            'label'    => 'Pesanan Selesai',
            'value'    => (int) ($summaryCards['pesananSelesai'] ?? 0),
            'subLabel' => $periodSubLabel,
            'icon'     => 'check',
            'color'    => '#10B981',
            'tooltip'  => 'Jumlah pesanan yang dibatalkan pada periode yang dipilih.',
        ],
        [
            'label'    => 'Pesanan Dibatalkan',
            'value'    => (int) ($summaryCards['pesananDibatalkan'] ?? 0),
            'subLabel' => $periodSubLabel,
            'icon'     => 'x-circle',
            'color'    => '#EF4444',
            'tooltip'  => 'Pesanan dibatalkan yang tanggal dibuatnya berada dalam periode filter.',
        ],
    ];

    $statusOptions = [
        'semua'      => 'Semua',
        'selesai'    => 'Selesai',
        'proses'     => 'Proses',
        'dibatalkan' => 'Dibatalkan',
    ];

    $kategoriOptions = [
        'semua'         => 'Semua',
        'cetak_offset'  => 'Cetak Offset',
        'cetak_digital' => 'Cetak Digital',
        'desain_grafis' => 'Desain Grafis',
        'media_promosi' => 'Media Promosi',
    ];

    $jenisPelangganOptions = [
        'semua'        => 'Semua',
        'perusahaan'   => 'Perusahaan',
        'perseorangan' => 'Perseorangan',
    ];

    $tipePesananOptions = [
        'semua'   => 'Semua',
        'standar' => 'Standar (Katalog)',
        'custom'  => 'Custom',
    ];
    ?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Laporan Admin<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Laporan Admin<?= $this->endSection() ?>

<?= $this->section('banner_title') ?>Laporan Admin<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>Ringkasan Pesanan periode <?= esc((string) ($range['label'] ?? '-')) ?><?= $this->endSection() ?>

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

    .laporan-admin-filter-panel .filter-field input,
    .laporan-admin-filter-panel .filter-field select {
        width: 100%;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<form method="get" action="<?= esc(site_url('laporan-admin')) ?>" class="flex items-center justify-end gap-3 mb-6">
    <div class="relative">
        <button
            type="button"
            id="laporanAdminFilterToggle"
            class="btn-laporan-filter"
            aria-expanded="false"
            aria-controls="laporanAdminFilterPanel"
            aria-haspopup="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4 shrink-0" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/>
            </svg>
            Filter
        </button>
        <div id="laporanAdminFilterPanel" class="laporan-admin-filter-panel hidden">
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
                <label for="status">Status</label>
                <select id="status" name="status" class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm min-w-0 focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
                    <?php foreach ($statusOptions as $val => $label): ?>
                        <option value="<?= esc($val) ?>" <?= ($filters['status'] ?? '') === $val ? 'selected' : '' ?>>
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
            <div class="filter-field">
                <label for="jenis_pelanggan">Jenis Pelanggan</label>
                <select id="jenis_pelanggan" name="jenis_pelanggan" class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm min-w-0 focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
                    <?php foreach ($jenisPelangganOptions as $val => $label): ?>
                        <option value="<?= esc($val) ?>" <?= ($filters['jenis_pelanggan'] ?? '') === $val ? 'selected' : '' ?>>
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-field">
                <label for="tipe_pesanan">Tipe Pesanan</label>
                <select id="tipe_pesanan" name="tipe_pesanan" class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm min-w-0 focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
                    <?php foreach ($tipePesananOptions as $val => $label): ?>
                        <option value="<?= esc($val) ?>" <?= ($filters['tipe_pesanan'] ?? '') === $val ? 'selected' : '' ?>>
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
    <a href="<?= esc(site_url('laporan-admin/export?' . $exportQuery)) ?>"
        class="btn-laporan-export inline-flex items-center gap-2 rounded-[14px] border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:border-slate-300 hover:bg-slate-50"
        title="Ekspor Excel"
        aria-label="Ekspor Excel">
        Ekspor
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4 shrink-0" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M7 10l5 5m0 0l5-5m-5 5V4"/>
        </svg>
    </a>
</form>

<?= view('dashboard/_partials/summary_cards', ['cards' => $cards]) ?>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden mb-6 mt-6">
    <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-base font-bold text-[#051747]">Rekap Per Kategori</h3>
        <p class="text-xs text-slate-500 mt-0.5">
            Berdasarkan <strong>Pesanan Selesai</strong>
        </p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold w-12">No</th>
                    <th class="px-4 py-3 text-left font-semibold">Kategori</th>
                    <th class="px-4 py-3 text-left font-semibold">Jumlah</th>
                    <th class="px-4 py-3 text-left font-semibold">Pendapatan</th>
                    <th class="px-4 py-3 text-left font-semibold">Rata-rata/Pesanan</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rekapKategori === []): ?>
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada data kategori pada periode ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rekapKategori as $index => $row): ?>
                        <tr class="border-b border-slate-100 hover:bg-[#F8FAFF]">
                            <td class="px-4 py-3 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3 font-medium text-[#051747]"><?= esc((string) ($row['label'] ?? '-')) ?></td>
                            <td class="px-4 py-3 text-slate-600"><?= esc((string) ($row['jumlah'] ?? 0)) ?></td>
                            <td class="px-4 py-3 font-semibold text-[#051747]">
                                Rp <?= esc(number_format((float) ($row['pendapatan'] ?? 0), 0, ',', '.')) ?>
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
                            Rp <?= esc(number_format((float) ($rekapTotals['pendapatan'] ?? 0), 0, ',', '.')) ?>
                        </td>
                        <td class="px-4 py-3">
                            Rp <?= esc(number_format((float) ($rekapTotals['rata_rata'] ?? 0), 0, ',', '.')) ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="px-6 py-3 border-t border-slate-100 bg-[#F8FAFF]">
        <p class="text-xs text-slate-600">
            <span class="font-semibold text-[#051747]">Estimasi total pendapatan pesanan aktif:</span>
            Rp <?= esc(number_format($potensiAktif, 0, ',', '.')) ?>
        </p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-base font-bold text-[#051747]">Daftar Pesanan</h3>
            <p class="text-xs text-slate-500 mt-0.5">Sesuai filter status & periode di atas.</p>
        </div>
        <label for="laporanAdminSearch" class="sr-only">Cari pesanan</label>
        <div class="search-control w-full sm:w-auto">
            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
            </svg>
            <input id="laporanAdminSearch" type="search" placeholder="Cari kode, pelanggan, produk..." autocomplete="off">
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="laporanAdminTable">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold">No</th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="kode">
                        Kode Pesanan<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="pelanggan">
                        Pelanggan<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="jenis">
                        Jenis<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="tipe">
                        Tipe<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="produk">
                        Produk<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="kategori">
                        Kategori<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="pesan">
                        Tgl Pesan<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="deadline">
                        Deadline<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="total">
                        Total<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="status">
                        Status<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="metode">
                        Metode<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="selesai">
                        Tgl Selesai<span class="sort-icon">↕</span>
                    </th>
                </tr>
            </thead>
            <tbody id="laporanAdminBody">
                <?php if ($daftarPesanan === []): ?>
                    <tr>
                        <td colspan="13" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada pesanan yang cocok dengan filter.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($daftarPesanan as $index => $row): ?>
                        <?php
                        $status      = (string) ($row['status'] ?? '');
                        $tglSelesai  = $row['tgl_selesai'] ?? null;
                        $isSelesai   = $status === 'selesai' && $tglSelesai;
                        $tglText     = $isSelesai ? date('d M Y', strtotime((string) $tglSelesai)) : '—';
                        $tsSelesai   = $isSelesai ? strtotime((string) $tglSelesai) : 0;
                        $createdAt   = (string) ($row['created_at'] ?? '');
                        $tsPesan     = $createdAt !== '' ? strtotime($createdAt) : 0;
                        $tglPesan    = $tsPesan > 0 ? date('d M Y', $tsPesan) : '—';
                        $deadlineRaw = trim((string) ($row['deadline'] ?? ''));
                        $tsDeadline  = $deadlineRaw !== '' ? strtotime($deadlineRaw) : 0;
                        $deadlineOverdue = !$isSelesai
                            && $tsDeadline > 0
                            && $tsDeadline < strtotime(date('Y-m-d'));
                        $kategoriKey   = (string) ($row['kategori'] ?? '');
                        $kategoriLabel = model(\App\Models\LaporanModel::class)->kategoriLabel($kategoriKey);
                        $laporanModel  = model(\App\Models\LaporanModel::class);
                        $jenisKey      = (string) ($row['jenis_pelanggan'] ?? '');
                        $jenisLabel    = $laporanModel->jenisPelangganLabel($jenisKey);
                        $tipeLabel     = $laporanModel->tipePesananLabel((int) ($row['is_custom'] ?? 0));
                        $metodeKey     = (string) ($row['metode_pengiriman'] ?? '');
                        $metodeLabel   = $laporanModel->metodePengirimanLabel($metodeKey);
                        $namaProduk = trim((string) ($row['nama_produk'] ?? ''));
                        if ($namaProduk === '') {
                            $namaProduk = '—';
                        }
                        $totalHarga = (float) ($row['total_harga'] ?? 0);
                        $searchBlob = strtolower(implode(' ', [
                            (string) ($row['kode_order'] ?? ''),
                            (string) ($row['nama_pelanggan'] ?? ''),
                            $jenisLabel,
                            $tipeLabel,
                            $namaProduk,
                            $kategoriLabel,
                            $metodeLabel,
                            $status,
                        ]));
                        ?>
                        <tr
                            class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF]"
                            data-search="<?= esc($searchBlob) ?>"
                            data-kode="<?= esc(mb_strtolower((string) ($row['kode_order'] ?? ''))) ?>"
                            data-pelanggan="<?= esc(mb_strtolower((string) ($row['nama_pelanggan'] ?? ''))) ?>"
                            data-jenis="<?= esc(mb_strtolower($jenisLabel)) ?>"
                            data-tipe="<?= esc(mb_strtolower($tipeLabel)) ?>"
                            data-produk="<?= esc(mb_strtolower($namaProduk)) ?>"
                            data-kategori="<?= esc(mb_strtolower($kategoriLabel)) ?>"
                            data-pesan="<?= esc((string) $tsPesan) ?>"
                            data-deadline="<?= esc((string) $tsDeadline) ?>"
                            data-total="<?= esc((string) $totalHarga) ?>"
                            data-status="<?= esc(mb_strtolower($status)) ?>"
                            data-metode="<?= esc(mb_strtolower($metodeLabel)) ?>"
                            data-selesai="<?= esc((string) $tsSelesai) ?>">
                            <td class="row-num px-4 py-3 text-slate-400"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3 font-semibold text-[#051747]">
                                <a href="<?= esc(site_url('order/detail/' . ($row['kode_order'] ?? ''))) ?>" class="hover:text-[#2E5CE6]">
                                    <?= esc((string) ($row['kode_order'] ?? '-')) ?>
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-600"><?= esc((string) ($row['nama_pelanggan'] ?? '-')) ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-600"><?= esc($jenisLabel) ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-600"><?= esc($tipeLabel) ?></td>
                            <td class="px-4 py-3 text-slate-600"><?= esc($namaProduk) ?></td>
                            <td class="px-4 py-3 text-slate-600"><?= esc($kategoriLabel) ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-600"><?= esc($tglPesan) ?></td>
                            <td class="px-4 py-3 whitespace-nowrap <?= $deadlineOverdue ? 'text-red-600 font-semibold' : 'text-slate-600' ?>">
                                <?php if ($deadlineRaw !== ''): ?>
                                    <?= esc(formatTanggalId($deadlineRaw)) ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 font-semibold text-[#051747]">
                                Rp <?= esc(number_format((float) ($row['total_harga'] ?? 0), 0, ',', '.')) ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc(getStatusBadgeClass($status)) ?>">
                                    <?= esc(getOrderStatusLabel($row)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-600"><?= esc($metodeLabel) ?></td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <?php if ($isSelesai): ?>
                                    <span class="text-emerald-600 font-medium"><?= esc($tglText) ?></span>
                                <?php elseif ($deadlineOverdue): ?>
                                    <span class="text-red-600 font-semibold cursor-help" title="Melewati deadline">—</span>
                                <?php else: ?>
                                    <span class="text-slate-500">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="laporanAdminEmptyFilter" class="hidden">
                        <td colspan="13" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada pesanan yang cocok dengan pencarian.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($daftarPesanan !== []): ?>
        <?= view('partials/admin_data_table_footer', [
            'entriesId'     => 'laporanAdminEntries',
            'entriesInfoId' => 'laporanAdminEntriesInfo',
            'paginationId'  => 'laporanAdminPagination',
            'prevPageId'    => 'laporanAdminPrev',
            'nextPageId'    => 'laporanAdminNext',
            'pageInfoId'    => 'laporanAdminPageInfo',
            'entryOptions'  => [
                ['value' => '10', 'label' => '10', 'selected' => true],
                ['value' => '25', 'label' => '25'],
                ['value' => '50', 'label' => '50'],
                ['value' => '0', 'label' => 'Semua'],
            ],
        ]) ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.adminDataTableConfig = {
        searchId: 'laporanAdminSearch',
        tbodyId: 'laporanAdminBody',
        emptyFilterRowId: 'laporanAdminEmptyFilter',
        entriesId: 'laporanAdminEntries',
        entriesInfoId: 'laporanAdminEntriesInfo',
        paginationId: 'laporanAdminPagination',
        prevPageId: 'laporanAdminPrev',
        nextPageId: 'laporanAdminNext',
        pageInfoId: 'laporanAdminPageInfo',
        rowSelector: 'tr.data-table-row',
        enableSort: true,
    };
</script>
<script>
    (() => {
        const toggle = document.getElementById('laporanAdminFilterToggle');
        const panel = document.getElementById('laporanAdminFilterPanel');
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
</script>
<?= view('partials/admin_data_table_scripts') ?>
<?= $this->endSection() ?>