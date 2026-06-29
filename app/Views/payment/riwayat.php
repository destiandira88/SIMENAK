<?php

/**
 * @var string                       $title
 * @var string                       $page_title
 * @var list<array<string, mixed>>   $payments
 * @var bool                         $readOnly
 * @var string                       $viewerRole
 */
$readOnly   = (bool) ($readOnly ?? false);
$viewerRole = (string) ($viewerRole ?? 'keuangan');
$filterJenis  = (string) ($_GET['jenis'] ?? '');
$filterStatus = (string) ($_GET['status'] ?? '');
$filterCari   = (string) ($_GET['cari'] ?? '');

$filtered = array_values(array_filter($payments, static function ($p) use ($filterJenis, $filterStatus, $filterCari) {
    if ($filterJenis !== '' && ($p['jenis'] ?? '') !== $filterJenis) {
        return false;
    }
    if ($filterStatus !== '' && ($p['status'] ?? '') !== $filterStatus) {
        return false;
    }
    if ($filterCari !== '') {
        $cari = strtolower($filterCari);
        if (
            !str_contains(strtolower((string) ($p['kode_order'] ?? '')), $cari)
            && !str_contains(strtolower((string) ($p['nama_pelanggan'] ?? '')), $cari)
            && !str_contains(strtolower((string) ($p['no_telp'] ?? '')), $cari)
        ) {
            return false;
        }
    }

    return true;
}));

$countTerverifikasi = 0;
$sumTerverifikasi   = 0.0;
foreach ($payments as $p) {
    if (($p['status'] ?? '') === 'terverifikasi') {
        $countTerverifikasi++;
        $sumTerverifikasi += (float) ($p['nominal'] ?? 0);
    }
}
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Riwayat Pembayaran') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title ?? 'Riwayat Semua Pembayaran') ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-6">
    <h2 class="text-xl font-extrabold text-[#051747]">
        <?= $readOnly ? 'Riwayat Pembayaran' : 'Riwayat Semua Pembayaran' ?>
    </h2>
    <p class="text-sm text-slate-500 mt-1">
        <?= $readOnly
            ? 'Pantau status dan riwayat pembayaran DP serta pelunasan.'
            : 'Seluruh riwayat pembayaran DP dan pelunasan' ?>
    </p>
</div>

<form method="get" action="<?= esc(site_url('riwayat-pembayaran')) ?>" class="flex gap-3 mb-5 flex-wrap items-end">
    <select name="jenis" onchange="this.form.submit()"
        class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
        <option value="">Semua Jenis</option>
        <option value="dp" <?= $filterJenis === 'dp' ? 'selected' : '' ?>>DP</option>
        <option value="pelunasan" <?= $filterJenis === 'pelunasan' ? 'selected' : '' ?>>Pelunasan</option>
    </select>
    <select name="status" onchange="this.form.submit()"
        class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
        <option value="">Semua Status</option>
        <option value="menunggu" <?= $filterStatus === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
        <option value="terverifikasi" <?= $filterStatus === 'terverifikasi' ? 'selected' : '' ?>>Terverifikasi</option>
        <option value="ditolak" <?= $filterStatus === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
    </select>
    <input type="text" name="cari" placeholder="Cari kode order / nama pelanggan..."
        value="<?= esc($filterCari) ?>"
        class="border border-slate-200 rounded-[14px] px-3 py-2 text-sm flex-1 min-w-[200px] focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[rgba(46,92,230,0.1)]">
    <button type="submit"
        class="bg-[#051747] text-white rounded-full text-sm font-bold px-4 py-2 hover:bg-[#2E5CE6] transition-colors">
        Cari
    </button>
</form>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
    <div class="bg-white border border-slate-100 rounded-xl p-4 shadow-sm">
        <p class="text-[10px] uppercase font-semibold text-slate-400 tracking-wide flex items-center gap-0.5">
            Total Semua
            <?= view('partials/metric_info_tooltip', ['text' => 'Jumlah transaksi berdasarkan filter yang sedang diterapkan.']) ?>
        </p>
        <p class="text-2xl font-extrabold text-[#051747] mt-1"><?= esc((string) count($payments)) ?></p>
        <p class="text-xs text-slate-500 mt-0.5">Transaksi</p>
    </div>
    <div class="bg-white border border-slate-100 rounded-xl p-4 shadow-sm">
        <p class="text-[10px] uppercase font-semibold text-slate-400 tracking-wide flex items-center gap-0.5">
            Terverifikasi
            <?= view('partials/metric_info_tooltip', ['text' => 'Jumlah transaksi terverifikasi pada hasil filter saat ini.']) ?>
        </p>
        <p class="text-2xl font-extrabold text-emerald-600 mt-1"><?= esc((string) $countTerverifikasi) ?></p>
        <p class="text-xs text-slate-500 mt-0.5">Transaksi</p>
    </div>
    <div class="bg-white border border-slate-100 rounded-xl p-4 shadow-sm">
        <p class="text-[10px] uppercase font-semibold text-slate-400 tracking-wide flex items-center gap-0.5">
            Total Nominal Terverifikasi
            <?= view('partials/metric_info_tooltip', ['text' => 'Menampilkan seluruh riwayat transaksi terverifikasi yang tercatat dalam sistem.']) ?>
        </p>
        <p class="text-lg font-extrabold text-[#051747] mt-1">
            Rp <?= esc(number_format($sumTerverifikasi, 0, ',', '.')) ?>
        </p>
        <p class="text-xs text-slate-500 mt-0.5">Sepanjang Waktu</p>
    </div>
</div>

<?php if (empty($filtered)): ?>
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-8 text-center">
        <p class="text-slate-500 text-sm">Tidak ada data sesuai filter</p>
    </div>
<?php else: ?>
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#051747] text-white text-xs uppercase">
                        <th class="px-4 py-3 text-left font-semibold w-12">No</th>
                        <th class="px-4 py-3 text-left font-semibold">Kode Pembayaran</th>
                        <th class="px-4 py-3 text-left font-semibold">Kode Pesanan</th>
                        <th class="px-4 py-3 text-left font-semibold">Pelanggan</th>
                        <th class="px-4 py-3 text-left font-semibold">Jenis</th>
                        <th class="px-4 py-3 text-left font-semibold">Nominal</th>
                        <th class="px-4 py-3 text-left font-semibold">Tgl Unggah</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-left font-semibold">Verifikator</th>
                        <th class="px-4 py-3 text-left font-semibold">Tgl Verifikasi</th>
                        <th class="px-4 py-3 text-left font-semibold">Bukti</th>
                    </tr>
                </thead>
                <tbody id="riwayatTableBody">
                    <?php foreach ($filtered as $index => $p): ?>
                        <?php
                        $jenis   = (string) ($p['jenis'] ?? '');
                        $status  = (string) ($p['status'] ?? '');
                        $buktiTf = (string) ($p['bukti_tf'] ?? '');
                        ?>
                        <tr class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF]">
                            <td class="row-num px-4 py-3 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">
                                <?= esc((string) ($p['kode_payment'] ?? '-')) ?>
                            </td>
                            <td class="px-4 py-3 font-mono font-semibold text-[#051747]">
                                <?php $kodeOrder = (string) ($p['kode_order'] ?? '-'); ?>
                                <?php if ($readOnly && $kodeOrder !== '-'): ?>
                                    <a href="<?= esc(site_url('order/detail/' . $kodeOrder)) ?>"
                                        class="hover:text-[#2E5CE6] hover:underline">
                                        <?= esc($kodeOrder) ?>
                                    </a>
                                <?php else: ?>
                                    <?= esc($kodeOrder) ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?= view('partials/pelanggan_kontak_cell', [
                                    'nama'   => (string) ($p['nama_pelanggan'] ?? '-'),
                                    'noTelp' => (string) ($p['no_telp'] ?? ''),
                                ]) ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($jenis === 'dp'): ?>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">DP</span>
                                <?php else: ?>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">Pelunasan</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 font-semibold">
                                Rp <?= esc(number_format((float) ($p['nominal'] ?? 0), 0, ',', '.')) ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <?= !empty($p['tgl_upload'])
                                    ? esc(date('d M Y H:i', strtotime((string) $p['tgl_upload'])))
                                    : '-' ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold <?= esc(getPaymentRiwayatStatusBadgeClass($p)) ?>">
                                    <?= esc(getPaymentRiwayatStatusLabel($p, $viewerRole)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <?= !empty($p['nama_verifikator'])
                                    ? esc((string) $p['nama_verifikator'])
                                    : '-' ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <?= !empty($p['tgl_verifikasi'])
                                    ? esc(date('d M Y H:i', strtotime((string) $p['tgl_verifikasi'])))
                                    : '-' ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($buktiTf !== ''): ?>
                                    <a href="<?= esc(base_url('uploads/bukti_bayar/' . $buktiTf)) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-blue-600 underline text-xs">
                                        Lihat
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-400 text-xs">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?= view('partials/admin_data_table_footer') ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.adminDataTableConfig = {
        tbodyId: 'riwayatTableBody',
        rowSelector: 'tr.data-table-row',
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<?= $this->endSection() ?>