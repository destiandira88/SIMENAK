<?php

/**
 * @var array<string, mixed>              $order
 * @var list<array<string, mixed>>       $attrs
 * @var list<array<string, mixed>>       $revisList
 * @var list<array<string, mixed>>       $payments
 * @var array<string, mixed>|null        $pengiriman
 * @var string                           $title
 * @var string                           $page_title
 */
$role        = (string) session()->get('role');
$kodeOrder   = (string) ($order['kode_order'] ?? '');
$status      = (string) ($order['status'] ?? '');
$idOrder     = (int) ($order['id_order'] ?? 0);
$kategoriKey = (string) ($order['kategori'] ?? '');
$totalHarga  = (float) ($order['total_harga'] ?? 0);
$sisaKuota   = (int) ($order['sisa_kuota'] ?? 0);
$kuotaRevisi = (int) ($order['kuota_revisi'] ?? 0);
$requireDp   = (int) ($order['require_dp'] ?? 0) === 1;
$isCustom    = (int) ($order['is_custom'] ?? 0) === 1;

$kategoriLabel = match ($kategoriKey) {
    'desain_grafis' => 'Desain Grafis',
    'cetak_digital' => 'Cetak Digital',
    'cetak_offset'  => 'Cetak Offset',
    'media_promosi' => 'Media Promosi',
    default         => str_replace('_', ' ', $kategoriKey),
};
$kategoriBadgeClass = match ($kategoriKey) {
    'desain_grafis' => 'bg-purple-100 text-purple-800',
    'cetak_digital' => 'bg-blue-100 text-blue-800',
    'cetak_offset'  => 'bg-orange-100 text-orange-800',
    'media_promosi' => 'bg-green-100 text-green-800',
    default         => 'bg-slate-100 text-slate-600',
};

$jenisPelanggan = (string) ($order['jenis_pelanggan'] ?? 'perseorangan');
$metodeKirim    = (string) ($order['metode_pengiriman'] ?? 'kurir');

$dpRecord    = null;
$lunasRecord = null;
foreach ($payments as $pay) {
    if (($pay['jenis'] ?? '') === 'dp') {
        $dpRecord = $pay;
    }
    if (($pay['jenis'] ?? '') === 'pelunasan') {
        $lunasRecord = $pay;
    }
}

$hasBuktiDp = false;
foreach ($payments as $p) {
    if (($p['jenis'] ?? '') === 'dp') {
        $hasBuktiDp = true;
        break;
    }
}

$tampilCountdown = ($order['status'] === 'menunggu_verifikasi_dp')
    && ((int) ($order['require_dp'] ?? 0) === 1)
    && !$hasBuktiDp
    && !empty($order['batas_upload_dp']);

$batasUploadDp = $order['batas_upload_dp'] ?? null;

$namaHari  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$namaBulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
$tsBatas   = $batasUploadDp ? strtotime((string) $batasUploadDp) : 0;
$deadlineTampil = $batasUploadDp
    ? $namaHari[(int) date('w', $tsBatas)] . ', '
    . date('d', $tsBatas) . ' '
    . $namaBulan[(int) date('n', $tsBatas)] . ' '
    . date('Y', $tsBatas)
    . ' pukul ' . date('H:i', $tsBatas) . ' WIB'
    : '';

$canUploadDp = $status === 'menunggu_verifikasi_dp'
    && ($dpRecord === null || ($dpRecord['status'] ?? '') === 'ditolak');

$canUploadLunas = in_array($status, ['siap_kirim', 'siap_diambil', 'pesanan_diterima'], true)
    && ($lunasRecord === null || ($lunasRecord['status'] ?? '') === 'ditolak');

$statusList = [
    'menunggu_verifikasi_dp'    => 'Menunggu Verifikasi DP',
    'terverifikasi'             => 'DP Terverifikasi',
    'proses_desain'             => 'Proses Desain',
    'proses_revisi'             => 'Proses Revisi',
    'proses_cetak'              => 'Proses Cetak',
    'finishing'                 => 'Finishing',
    'siap_kirim'                => 'Siap Dikirim',
    'dikirim'                   => 'Dikirim',
    'pesanan_diterima'          => 'Pesanan Diterima',
    'menunggu_verifikasi_lunas' => 'Menunggu Pelunasan',
    'selesai'                   => 'Selesai',
];
if ($isCustom) {
    $statusList = array_merge([
        'menunggu_konfirmasi_harga'     => 'Menunggu Konfirmasi Harga',
        'menunggu_konfirmasi_pelanggan' => 'Menunggu Konfirmasimu',
    ], $statusList);
}
$statusKeys  = array_keys($statusList);
$currentIdx  = array_search($status, $statusKeys, true);

$revisiStatusBadges = [
    'uploaded'         => ['label' => 'Diunggah', 'class' => 'bg-blue-100 text-blue-800'],
    'diajukan_revisi'  => ['label' => 'Revisi Diajukan', 'class' => 'bg-amber-100 text-amber-800'],
    'acc'              => ['label' => '✓ ACC', 'class' => 'bg-green-100 text-green-800'],
    'ditolak'          => ['label' => 'Ditolak', 'class' => 'bg-red-100 text-red-800'],
];

$latestRevis    = $revisList !== [] ? $revisList[array_key_last($revisList)] : null;
$idRevisiTerbaru = $latestRevis !== null ? (int) ($latestRevis['id_revisi'] ?? 0) : 0;
$revisCount     = count($revisList);
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Detail Pesanan') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title ?? 'Detail Pesanan') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
    <div>
        <p class="text-xs text-slate-400 mb-1">
            <?php if ($role === 'pelanggan'): ?>
                <a href="<?= site_url('order') ?>" class="hover:text-[#051747]">Pesanan Saya</a>
            <?php elseif ($role === 'admin'): ?>
                <a href="<?= site_url('list-pemesanan') ?>" class="hover:text-[#051747]">List Pemesanan</a>
            <?php elseif ($role === 'keuangan'): ?>
                <a href="<?= site_url('verifikasi-dp') ?>" class="hover:text-[#051747]">Verifikasi Pembayaran</a>
            <?php elseif ($role === 'produksi'): ?>
                <a href="<?= site_url('antrian-desain') ?>" class="hover:text-[#051747]">Antrian Desain</a>
            <?php else: ?>
                <a href="<?= site_url('dashboard') ?>" class="hover:text-[#051747]">Dashboard</a>
            <?php endif; ?>
            <span class="mx-1">/</span>
            <span class="text-slate-500"><?= esc($kodeOrder) ?></span>
        </p>
        <h1 class="font-mono font-extrabold text-2xl text-[#051747]"><?= esc($kodeOrder) ?></h1>
        <span class="inline-flex mt-2 px-4 py-1.5 rounded-full text-sm font-semibold <?= esc(getStatusBadgeClass($status)) ?>">
            <?= esc(getStatusLabel($status, $hasBuktiDp)) ?>
        </span>
    </div>
    <?php if ($role === 'pelanggan'): ?>
        <a href="<?= site_url('order') ?>"
           class="inline-flex items-center justify-center border-2 border-[#051747] text-[#051747] px-5 py-2.5 rounded-full text-sm font-bold hover:bg-[#051747] hover:text-white transition-colors shrink-0">
            ← Pesanan Saya
        </a>
    <?php elseif ($role === 'admin'): ?>
        <a href="<?= site_url('list-pemesanan') ?>"
           class="inline-flex items-center justify-center border-2 border-[#051747] text-[#051747] px-5 py-2.5 rounded-full text-sm font-bold hover:bg-[#051747] hover:text-white transition-colors shrink-0">
            ← List Pemesanan
        </a>
    <?php elseif ($role === 'keuangan'): ?>
        <a href="<?= site_url('verifikasi-dp') ?>"
           class="inline-flex items-center justify-center border-2 border-[#051747] text-[#051747] px-5 py-2.5 rounded-full text-sm font-bold hover:bg-[#051747] hover:text-white transition-colors shrink-0">
            ← Verifikasi Pembayaran
        </a>
    <?php elseif ($role === 'produksi'): ?>
        <a href="<?= site_url('antrian-desain') ?>"
           class="inline-flex items-center justify-center border-2 border-[#051747] text-[#051747] px-5 py-2.5 rounded-full text-sm font-bold hover:bg-[#051747] hover:text-white transition-colors shrink-0">
            ← Antrian Desain
        </a>
    <?php else: ?>
        <a href="<?= site_url('dashboard') ?>"
           class="inline-flex items-center justify-center border-2 border-[#051747] text-[#051747] px-5 py-2.5 rounded-full text-sm font-bold hover:bg-[#051747] hover:text-white transition-colors shrink-0">
            ← Dashboard
        </a>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
    <div class="lg:col-span-3 space-y-5">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="font-bold text-[#051747] mb-4 pb-3 border-b border-slate-100">Informasi Pesanan</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Produk</p>
                    <p class="font-semibold text-[#051747]"><?= esc((string) ($order['nama_produk'] ?? '-')) ?></p>
                    <?php if ($kategoriKey !== ''): ?>
                        <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold <?= esc($kategoriBadgeClass) ?>">
                            <?= esc($kategoriLabel) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Jumlah</p>
                    <p class="text-slate-700"><?= esc((string) ($order['jumlah_order'] ?? 0)) ?> <?= esc((string) ($order['satuan'] ?? '')) ?></p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Jenis Pelanggan</p>
                    <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                        <?= $jenisPelanggan === 'perusahaan' ? 'Perusahaan' : 'Perseorangan' ?>
                    </span>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Metode Kirim</p>
                    <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                        <?= $metodeKirim === 'kurir' ? 'Kurir' : 'Ambil Sendiri' ?>
                    </span>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Deadline</p>
                    <p class="text-slate-700">
                        <?= !empty($order['deadline']) ? esc(date('d M Y', strtotime((string) $order['deadline']))) : '-' ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Total Harga</p>
                    <?php if ($totalHarga > 0): ?>
                        <p class="font-semibold text-[#051747]">Rp <?= esc(number_format($totalHarga, 0, ',', '.')) ?></p>
                    <?php else: ?>
                        <span class="text-amber-600 font-medium">Dikonfirmasi Admin</span>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Tanggal Pesan</p>
                    <p class="text-slate-700">
                        <?= !empty($order['created_at']) ? esc(date('d M Y H:i', strtotime((string) $order['created_at']))) : '-' ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400 mb-1">Referensi Desain</p>
                    <?php if (!empty($order['referensi_desain'])): ?>
                        <a
                            href="<?= esc(base_url('uploads/referensi/' . $order['referensi_desain'])) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-[#2E5CE6] underline text-sm">
                            Lihat File
                        </a>
                    <?php else: ?>
                        <span class="text-slate-400 text-sm">Tidak ada</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($order['detail_pesanan'])): ?>
                <div class="border-t border-slate-100 pt-3 mt-4">
                    <p class="text-xs font-bold uppercase text-slate-500 mb-1">Spesifikasi Pesanan</p>
                    <p class="text-sm text-slate-600 whitespace-pre-line"><?= esc((string) $order['detail_pesanan']) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($attrs !== []): ?>
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl shadow-sm p-6">
                <h2 class="font-bold text-indigo-900 mb-4 pb-3 border-b border-indigo-200">
                    📋 Spesifikasi <?= esc((string) ($order['nama_produk'] ?? '')) ?>
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <?php foreach ($attrs as $a): ?>
                        <?php
                        $fieldType = (string) ($a['field_type'] ?? 'text');
                        $fieldLabel = (string) ($a['field_label'] ?? $a['attribute_key'] ?? '');
                        $attrVal   = (string) ($a['attribute_val'] ?? '');
                        ?>
                        <div>
                            <p class="text-xs font-bold uppercase text-slate-500 mb-1"><?= esc($fieldLabel) ?></p>
                            <?php if ($fieldType === 'file' && $attrVal !== ''): ?>
                                <a
                                    href="<?= esc(base_url('uploads/lampiran_peta/' . $attrVal)) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-[#2E5CE6] underline text-sm">
                                    Lihat File
                                </a>
                            <?php else: ?>
                                <p class="text-sm text-slate-700"><?= esc($attrVal) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <div class="flex flex-wrap justify-between items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                <h2 class="font-bold text-[#051747]">🎨 Revisi Desain</h2>
                <p class="text-sm text-slate-600">
                    Sisa Kuota:
                    <span class="<?= $sisaKuota <= 1 ? 'text-red-600' : 'text-green-600' ?> font-bold">
                        <?= esc((string) $sisaKuota) ?>/<?= esc((string) $kuotaRevisi) ?>
                    </span>
                </p>
            </div>

            <?php if ($revisList === []): ?>
                <div class="text-center py-8 text-slate-400 text-sm">
                    Belum ada draft desain. Menunggu bagian produksi.
                </div>
            <?php else: ?>
                <?php foreach ($revisList as $idx => $r): ?>
                    <?php
                    $revisStatus = (string) ($r['status'] ?? 'uploaded');
                    $revBadge    = $revisiStatusBadges[$revisStatus] ?? ['label' => $revisStatus, 'class' => 'bg-slate-100 text-slate-600'];
                    $isLatest    = $idx === $revisCount - 1;
                    $revCode     = 'REV-' . str_pad((string) $idOrder, 4, '0', STR_PAD_LEFT)
                        . '-' . str_pad((string) ($r['versi'] ?? 0), 2, '0', STR_PAD_LEFT);
                    $fileDraft   = (string) ($r['file_draft'] ?? '');
                    ?>
                    <div class="border border-slate-200 rounded-xl p-4 mb-3 last:mb-0">
                        <div class="flex gap-4 items-start">
                            <div class="w-20 h-16 bg-slate-100 rounded-lg flex items-center justify-center overflow-hidden shrink-0">
                                <?php if ($fileDraft !== ''): ?>
                                    <a href="<?= esc(base_url('uploads/draft_desain/' . $fileDraft)) ?>" target="_blank" rel="noopener noreferrer" class="w-full h-full">
                                        <img
                                            src="<?= esc(base_url('uploads/draft_desain/' . $fileDraft)) ?>"
                                            alt="Draft v<?= esc((string) ($r['versi'] ?? '')) ?>"
                                            class="w-full h-full object-cover rounded-lg"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <span class="text-2xl hidden">🖼</span>
                                    </a>
                                <?php else: ?>
                                    <span class="text-2xl">🖼</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap justify-between gap-2 items-start">
                                    <p class="text-sm font-bold text-[#051747]">
                                        Draft v<?= esc((string) ($r['versi'] ?? '')) ?> — <?= esc($revCode) ?>
                                    </p>
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-semibold <?= esc($revBadge['class']) ?>">
                                        <?= esc($revBadge['label']) ?>
                                    </span>
                                </div>
                                <?php if (!empty($r['catatan_prod'])): ?>
                                    <p class="text-xs text-slate-500 mt-1">Produksi: <?= esc((string) $r['catatan_prod']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($r['catatan_revisi'])): ?>
                                    <p class="text-xs text-amber-700 bg-amber-50 rounded px-2 py-1 mt-1">
                                        Catatanmu: <?= esc((string) $r['catatan_revisi']) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($r['created_at'])): ?>
                                    <p class="text-xs text-slate-400 mt-1">
                                        <?= esc(date('d M Y H:i', strtotime((string) $r['created_at']))) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($isLatest && $role === 'pelanggan' && $revisStatus === 'uploaded'): ?>
                            <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-slate-100">
                                <button
                                    type="button"
                                    onclick="document.getElementById('modalAcc').classList.remove('hidden')"
                                    class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-green-600 transition-colors">
                                    ✓ ACC Desain
                                </button>
                                <?php if ($sisaKuota > 0): ?>
                                    <button
                                        type="button"
                                        onclick="document.getElementById('modalRevisi').classList.remove('hidden')"
                                        class="bg-amber-500 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-amber-600 transition-colors">
                                        ↺ Ajukan Revisi
                                    </button>
                                <?php else: ?>
                                    <button
                                        type="button"
                                        disabled
                                        class="bg-amber-500 text-white px-4 py-2 rounded-lg text-sm font-bold opacity-50 cursor-not-allowed">
                                        ↺ Ajukan Revisi
                                    </button>
                                    <p class="text-xs text-red-500 w-full">Kuota revisi habis. Hanya bisa ACC.</p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-5">
        <?php if ($isCustom && $status === 'menunggu_konfirmasi_harga' && $role === 'admin'): ?>
            <div class="bg-white border border-slate-200 rounded-xl p-5 mb-5 shadow-sm">
                <p class="font-bold text-[#051747] mb-1">⭐ Set Penawaran Harga Custom</p>
                <p class="text-sm text-slate-500 mb-4">
                    Tetapkan harga dan estimasi untuk pesanan ini, lalu kirim ke pelanggan.
                </p>
                <?php if (!empty($order['catatan_custom'])): ?>
                    <div class="bg-slate-50 rounded-xl p-3 mb-4 text-sm text-slate-700">
                        <span class="font-semibold">Catatan pelanggan:</span>
                        <?= esc((string) $order['catatan_custom']) ?>
                    </div>
                <?php endif; ?>
                <form method="post" action="<?= esc(site_url('list-pemesanan/set-harga')) ?>" class="space-y-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_order" value="<?= esc((string) $idOrder) ?>">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Harga Ditawarkan (Rp)</label>
                        <input type="number" name="harga_custom" min="1" required
                            class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"
                            placeholder="Contoh: 350000">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Estimasi Pengerjaan</label>
                        <input type="text" name="estimasi_custom" required
                            class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"
                            placeholder="Contoh: 5-7 hari kerja">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Catatan untuk Pelanggan</label>
                        <textarea name="catatan_admin_custom" rows="3"
                            class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"
                            placeholder="Rincian material, finishing, dll."></textarea>
                    </div>
                    <button type="submit"
                        class="bg-[#051747] text-white px-5 py-2.5 rounded-full font-bold text-sm hover:bg-[#2E5CE6] transition-colors">
                        Kirim Penawaran ke Pelanggan
                    </button>
                </form>
            </div>
        <?php elseif ($isCustom && $status === 'menunggu_konfirmasi_harga' && $role === 'pelanggan'): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-5">
                <p class="font-bold text-amber-800">⏳ Menunggu Konfirmasi Harga</p>
                <p class="text-sm text-amber-700 mt-1">
                    Admin sedang meninjau spesifikasi custom kamu.
                    Kamu akan mendapat notifikasi saat harga sudah ditetapkan.
                </p>
            </div>
        <?php elseif ($isCustom && $status === 'menunggu_konfirmasi_pelanggan' && $role === 'pelanggan'): ?>
            <div class="bg-blue-50 border border-blue-300 rounded-xl p-5 mb-5">
                <p class="font-bold text-[#051747] mb-3">💰 Penawaran Harga dari Admin</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-500">Harga Ditawarkan</p>
                        <p class="text-2xl font-bold text-[#051747]">
                            Rp <?= esc(number_format((float) ($order['harga_custom'] ?? 0), 0, ',', '.')) ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-500">Estimasi Pengerjaan</p>
                        <p class="text-lg font-semibold text-[#051747]">
                            <?= esc((string) ($order['estimasi_custom'] ?? '-')) ?>
                        </p>
                    </div>
                </div>

                <?php if (!empty($order['catatan_admin_custom'])): ?>
                    <div class="bg-white border border-slate-200 rounded-lg p-3 mb-4">
                        <p class="text-xs font-bold text-slate-500 uppercase">Catatan Admin</p>
                        <p class="text-sm text-slate-700"><?= esc((string) $order['catatan_admin_custom']) ?></p>
                    </div>
                <?php endif; ?>

                <div class="flex flex-wrap gap-3">
                    <form method="post" action="<?= esc(site_url('custom-order/setuju')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_order" value="<?= esc((string) $idOrder) ?>">
                        <button
                            type="submit"
                            class="bg-green-500 text-white px-5 py-2.5 rounded-full font-bold text-sm hover:bg-green-600 transition-colors">
                            ✓ Saya Setuju
                        </button>
                    </form>

                    <form method="post" action="<?= esc(site_url('custom-order/tolak')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_order" value="<?= esc((string) $idOrder) ?>">
                        <button
                            type="submit"
                            onclick="return confirm('Yakin menolak penawaran ini? Pesanan akan dibatalkan.')"
                            class="bg-red-100 text-red-700 px-5 py-2.5 rounded-full font-bold text-sm hover:bg-red-200 transition-colors">
                            ✗ Tolak Penawaran
                        </button>
                    </form>
                </div>
            </div>
        <?php elseif ($isCustom && $status === 'menunggu_konfirmasi_pelanggan' && $role === 'admin'): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-5">
                <p class="font-bold text-[#051747]">Penawaran terkirim — menunggu konfirmasi pelanggan</p>
                <p class="text-sm text-slate-600 mt-2">
                    Harga: <strong>Rp <?= esc(number_format((float) ($order['harga_custom'] ?? 0), 0, ',', '.')) ?></strong>
                    · Estimasi: <strong><?= esc((string) ($order['estimasi_custom'] ?? '-')) ?></strong>
                </p>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="font-bold text-[#051747] mb-4">📍 Status Pesanan</h2>
            <div class="pl-1">
                <?php foreach ($statusList as $key => $label): ?>
                    <?php
                    $idx       = array_search($key, $statusKeys, true);
                    $isDone    = $currentIdx !== false && $idx !== false && $idx < $currentIdx;
                    $isActive  = $key === $status;
                    $isPending = $currentIdx !== false && $idx !== false && $idx > $currentIdx;
                    $isLast    = $key === $statusKeys[array_key_last($statusKeys)];
                    ?>
                    <div class="flex gap-3 items-start mb-1">
                        <div class="flex flex-col items-center">
                            <?php
                            $dotClass = 'w-4 h-4 rounded-full flex-shrink-0 mt-0.5 ';
                            if ($isActive) {
                                $dotClass .= 'bg-[#2E5CE6] ring-4 ring-[#2E5CE6]/20 animate-pulse';
                            } elseif ($isDone) {
                                $dotClass .= 'bg-[#2E5CE6]';
                            } else {
                                $dotClass .= 'bg-slate-200 border-2 border-slate-300';
                            }
                            ?>
                            <div class="<?= esc($dotClass) ?>"></div>
                            <?php if (!$isLast): ?>
                                <div class="w-px h-3 bg-slate-200 my-0.5"></div>
                            <?php endif; ?>
                        </div>
                        <div class="pb-2">
                            <p class="text-sm <?= $isActive ? 'font-bold text-[#051747]' : ($isDone ? 'font-medium text-slate-500' : 'text-slate-400') ?>">
                                <?= esc($label) ?>
                            </p>
                            <?php if ($isActive): ?>
                                <span class="text-xs text-[#2E5CE6]">← Status saat ini</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="font-bold text-[#051747] mb-4 pb-3 border-b border-slate-100">💳 Pembayaran</h2>

            <?php if ($requireDp): ?>
                <?php
                $dpStatus = $dpRecord['status'] ?? null;
                $dpBadge  = match ($dpStatus) {
                    'menunggu'      => ['class' => 'bg-amber-100 text-amber-800', 'label' => 'Menunggu Verifikasi'],
                    'terverifikasi' => ['class' => 'bg-green-100 text-green-800', 'label' => '✓ Terverifikasi'],
                    'ditolak'       => ['class' => 'bg-red-100 text-red-800', 'label' => '✗ Ditolak'],
                    default         => ['class' => 'bg-slate-100 text-slate-500', 'label' => 'Belum Upload'],
                };
                ?>
                <div class="border border-slate-200 rounded-xl p-4 mb-3">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-semibold text-sm text-slate-700">Uang Muka (DP 50%)</span>
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-semibold <?= esc($dpBadge['class']) ?>">
                            <?= esc($dpBadge['label']) ?>
                        </span>
                    </div>
                    <p class="text-xl font-bold text-[#051747]">
                        Rp <?= esc(number_format($totalHarga > 0 ? $totalHarga * 0.5 : 0, 0, ',', '.')) ?>
                    </p>

                    <?php if ($tampilCountdown): ?>
                        <div id="dpDeadlineCard"
                            class="rounded-xl p-4 mb-3 border transition-colors duration-500 bg-amber-50 border-amber-200">
                            <div class="flex items-center gap-2 mb-2">
                                <span id="dpDeadlineIcon" class="text-base">⏰</span>
                                <p id="dpDeadlineTitle" class="font-bold text-amber-800 text-sm">
                                    Batas Upload Bukti DP
                                </p>
                            </div>
                            <p class="text-xs text-amber-700 mb-1">
                                Upload sebelum:
                                <strong><?= esc($deadlineTampil) ?></strong>
                            </p>
                            <p id="countdownDp"
                                class="text-sm font-bold font-mono text-amber-800 mb-1">
                                Sisa waktu: --:--:--
                            </p>
                            <p id="dpDeadlineNote" class="text-xs text-amber-600">
                                Jika melewati batas waktu, pesanan dapat dibatalkan otomatis.
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php
                    $dpDitolak         = $dpRecord !== null && ($dpRecord['status'] ?? '') === 'ditolak';
                    $dpMenungguVerif   = $hasBuktiDp && $dpRecord !== null && ($dpRecord['status'] ?? '') === 'menunggu';
                    $showDpUploadForm  = $role === 'pelanggan'
                        && $status === 'menunggu_verifikasi_dp'
                        && ($tampilCountdown || $dpDitolak);
                    $nominalDpTampil   = $totalHarga > 0 ? $totalHarga * 0.5 : 0;
                    ?>

                    <?php if ($dpMenungguVerif): ?>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mt-3">
                            <p class="font-bold text-amber-800 text-sm">⏳ Bukti DP sedang diverifikasi</p>
                            <?php if (!empty($dpRecord['tgl_upload'])): ?>
                                <p class="text-xs text-amber-700 mt-1">
                                    Diunggah: <?= esc(date('d M Y H:i', strtotime((string) $dpRecord['tgl_upload']))) ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($dpRecord['bukti_tf'])): ?>
                                <a href="<?= esc(base_url('uploads/bukti_bayar/' . $dpRecord['bukti_tf'])) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-block text-xs text-blue-600 underline mt-2">
                                    Lihat bukti
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($dpDitolak): ?>
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mt-3">
                            <p class="font-bold text-red-700 text-sm">❌ Bukti DP Ditolak</p>
                            <p class="text-xs text-red-600 mt-1">
                                Alasan: <?= esc((string) ($dpRecord['catatan_tolak'] ?? '-')) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($showDpUploadForm): ?>
                        <form
                            id="formUploadDp"
                            method="post"
                            action="<?= esc(site_url('order/' . $kodeOrder . '/upload-dp')) ?>"
                            enctype="multipart/form-data"
                            class="mt-3 scroll-mt-24">
                            <div class="bg-white border border-slate-200 rounded-xl p-5">
                                <h3 class="font-bold text-[#051747] mb-4">Upload Bukti Transfer DP</h3>
                                <div class="bg-slate-50 rounded-lg p-3 mb-4">
                                    <p class="text-sm font-semibold text-[#051747]">
                                        Nominal DP (50%): Rp <?= esc(number_format($nominalDpTampil, 0, ',', '.')) ?>
                                    </p>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Transfer ke rekening Z'Plack: BCA 1234567890 a/n Z'Plack Percetakan
                                    </p>
                                </div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">
                                    Bukti Transfer (JPG/PNG/PDF, maks 2MB)
                                </label>
                                <input
                                    type="file"
                                    name="bukti_dp"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    required
                                    class="block w-full text-sm text-slate-500 border border-slate-200 rounded-xl px-3 py-2 file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#051747] file:text-white hover:file:bg-[#2E5CE6] focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                                <button
                                    type="submit"
                                    class="mt-4 bg-[#051747] text-white text-xs font-bold uppercase px-5 py-2.5 rounded-full hover:bg-[#2E5CE6] transition-colors">
                                    Upload Bukti DP
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>

                    <?php if ($dpRecord !== null && ($dpRecord['status'] ?? '') === 'terverifikasi' && !empty($dpRecord['tgl_verifikasi'])): ?>
                        <p class="text-xs text-green-600 mt-2">
                            ✓ Diverifikasi <?= esc(date('d M Y', strtotime((string) $dpRecord['tgl_verifikasi']))) ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php
            $lunasStatus = $lunasRecord['status'] ?? null;
            if (!$canUploadLunas && $lunasRecord === null) {
                $lunasBadge = ['class' => 'bg-slate-100 text-slate-500', 'label' => 'Belum Aktif'];
            } elseif ($lunasRecord === null && $canUploadLunas) {
                $lunasBadge = ['class' => 'bg-amber-100 text-amber-800', 'label' => 'Perlu Dibayar'];
            } else {
                $lunasBadge = match ($lunasStatus) {
                    'menunggu'      => ['class' => 'bg-amber-100 text-amber-800', 'label' => 'Menunggu Verifikasi'],
                    'terverifikasi' => ['class' => 'bg-green-100 text-green-800', 'label' => '✓ Lunas'],
                    'ditolak'       => ['class' => 'bg-red-100 text-red-800', 'label' => '✗ Ditolak'],
                    default         => ['class' => 'bg-slate-100 text-slate-500', 'label' => 'Belum Aktif'],
                };
            }
            ?>
            <div class="border border-slate-200 rounded-xl p-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="font-semibold text-sm text-slate-700">Pelunasan (50%)</span>
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-semibold <?= esc($lunasBadge['class']) ?>">
                        <?= esc($lunasBadge['label']) ?>
                    </span>
                </div>
                <p class="text-xl font-bold text-[#051747]">
                    Rp <?= esc(number_format($totalHarga > 0 ? $totalHarga * 0.5 : 0, 0, ',', '.')) ?>
                </p>

                <?php if ($canUploadLunas && $role === 'pelanggan'): ?>
                    <form
                        method="post"
                        action="<?= esc(site_url('payment/upload-pelunasan')) ?>"
                        enctype="multipart/form-data"
                        class="mt-3">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_order" value="<?= esc((string) $idOrder) ?>">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Upload Bukti Pelunasan</label>
                        <input
                            type="file"
                            name="bukti_tf"
                            accept=".jpg,.jpeg,.png,.pdf"
                            required
                            class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#051747] file:text-white hover:file:bg-[#2E5CE6]">
                        <p class="text-xs text-slate-400 mt-1">JPG, PNG, PDF — Maks 2MB</p>
                        <button
                            type="submit"
                            class="mt-3 bg-[#051747] text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-[#2E5CE6] transition-colors">
                            Upload Bukti Pelunasan
                        </button>
                    </form>
                <?php elseif (!$canUploadLunas && $lunasRecord === null): ?>
                    <p class="text-xs text-slate-400 mt-2">Aktif setelah pesanan siap kirim atau diterima.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($pengiriman !== null): ?>
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <div class="border border-slate-200 rounded-xl p-4">
                    <p class="font-semibold text-sm text-[#051747] mb-2">🚚 Info Pengiriman</p>
                    <?php if (!empty($pengiriman['no_resi'])): ?>
                        <p class="text-sm text-slate-700">
                            Resi: <?= esc((string) $pengiriman['no_resi']) ?>
                            <?php if (!empty($pengiriman['nama_ekspedisi'])): ?>
                                via <?= esc((string) $pengiriman['nama_ekspedisi']) ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($pengiriman['status_kirim'])): ?>
                        <span class="inline-flex mt-2 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-cyan-100 text-cyan-800">
                            <?= esc(str_replace('_', ' ', (string) $pengiriman['status_kirim'])) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($pengiriman['tgl_kirim'])): ?>
                        <p class="text-xs text-slate-500 mt-2">
                            Dikirim: <?= esc(date('d M Y', strtotime((string) $pengiriman['tgl_kirim']))) ?>
                        </p>
                    <?php endif; ?>

                    <?php if (($pengiriman['status_kirim'] ?? '') === 'dikirim' && $role === 'pelanggan'): ?>
                        <form method="post" action="<?= esc(site_url('pengiriman/konfirmasi')) ?>" class="mt-3">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id_order" value="<?= esc((string) $idOrder) ?>">
                            <button
                                type="submit"
                                class="bg-green-500 text-white rounded-lg px-4 py-2 text-sm font-bold hover:bg-green-600 transition-colors">
                                ✓ Konfirmasi Pesanan Diterima
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($idRevisiTerbaru > 0): ?>
    <div id="modalAcc" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-xl">
            <h3 class="font-bold text-lg text-[#051747] mb-2">Konfirmasi ACC Desain</h3>
            <p class="text-sm text-slate-500 mb-4">Desain akan di-ACC dan pesanan lanjut ke proses cetak.</p>
            <form method="post" action="<?= esc(site_url('revisi/acc')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id_order" value="<?= esc((string) $idOrder) ?>">
                <input type="hidden" name="id_revisi" value="<?= esc((string) $idRevisiTerbaru) ?>">
                <div class="flex gap-3 justify-end">
                    <button
                        type="button"
                        onclick="document.getElementById('modalAcc').classList.add('hidden')"
                        class="px-4 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="bg-green-500 text-white rounded-lg px-4 py-2 text-sm font-bold hover:bg-green-600">
                        ✓ Ya, ACC Desain
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalRevisi" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-xl">
            <h3 class="font-bold text-lg text-[#051747] mb-1">Ajukan Revisi</h3>
            <p class="text-xs text-amber-600 mb-4">Sisa kuota: <?= esc((string) $sisaKuota) ?> revisi</p>
            <form method="post" action="<?= esc(site_url('revisi/ajukan')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id_order" value="<?= esc((string) $idOrder) ?>">
                <input type="hidden" name="id_revisi" value="<?= esc((string) $idRevisiTerbaru) ?>">
                <textarea
                    name="catatan_revisi"
                    rows="4"
                    required
                    placeholder="Jelaskan apa yang perlu diubah secara detail..."
                    class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"></textarea>
                <div class="flex gap-3 justify-end mt-4">
                    <button
                        type="button"
                        onclick="document.getElementById('modalRevisi').classList.add('hidden')"
                        class="px-4 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="bg-amber-500 text-white rounded-lg px-4 py-2 text-sm font-bold hover:bg-amber-600">
                        Kirim Revisi
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if ($tampilCountdown): ?>
    <script>
        (function() {
            const deadline = new Date('<?= date('Y-m-d\TH:i:s', $tsBatas) ?>');
            const elCountdown = document.getElementById('countdownDp');
            const elCard = document.getElementById('dpDeadlineCard');
            const elTitle = document.getElementById('dpDeadlineTitle');
            const elNote = document.getElementById('dpDeadlineNote');
            const elIcon = document.getElementById('dpDeadlineIcon');
            if (!elCountdown || !elCard) return;

            const pad = (n) => String(n).padStart(2, '0');

            function setStateNormal() {
                elCard.className = 'rounded-xl p-4 mb-4 border transition-colors duration-500 bg-amber-50 border-amber-200';
                elCountdown.className = 'text-sm font-bold font-mono text-amber-800 mb-1';
                elTitle.className = 'font-bold text-amber-800 text-sm';
                elNote.className = 'text-xs text-amber-600';
                elIcon.textContent = '⏰';
            }

            function setStateUrgent() {
                elCard.className = 'rounded-xl p-4 mb-4 border transition-colors duration-500 bg-red-50 border-red-300';
                elCountdown.className = 'text-sm font-bold font-mono text-red-700 mb-1 animate-pulse';
                elTitle.className = 'font-bold text-red-700 text-sm';
                elNote.className = 'text-xs text-red-600';
                elIcon.textContent = '🚨';
            }

            function setStateExpired() {
                elCard.className = 'rounded-xl p-4 mb-4 border bg-red-50 border-red-300';
                elCountdown.className = 'text-sm font-bold font-mono text-red-700 mb-1';
                elTitle.className = 'font-bold text-red-700 text-sm';
                elNote.className = 'text-xs text-red-600 font-semibold';
                elIcon.textContent = '⛔';
            }

            function updateCountdown() {
                const diff = deadline - new Date();

                if (diff <= 0) {
                    setStateExpired();
                    elCountdown.textContent = 'Batas waktu telah terlewat.';
                    elTitle.textContent = 'Segera Upload atau Hubungi Admin';
                    elNote.textContent = 'Batas waktu terlewat. Pesanan dapat dibatalkan otomatis.';
                    return;
                }

                const jam = Math.floor(diff / 3600000);
                const menit = Math.floor((diff % 3600000) / 60000);
                const detik = Math.floor((diff % 60000) / 1000);

                elCountdown.textContent = 'Sisa waktu: ' +
                    pad(jam) + ' jam ' +
                    pad(menit) + ' menit ' +
                    pad(detik) + ' detik';

                if (diff < 3 * 3600000) {
                    setStateUrgent();
                    elTitle.textContent = '🚨 Segera Upload Bukti DP!';
                    elNote.textContent = 'Kurang dari 3 jam lagi. Pesanan dibatalkan otomatis jika terlewat.';
                } else {
                    setStateNormal();
                    elTitle.textContent = 'Batas Upload Bukti DP';
                    elNote.textContent = 'Jika melewati batas waktu, pesanan dapat dibatalkan otomatis.';
                }
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);
        })();
    </script>
<?php endif; ?>
<?= $this->endSection() ?>