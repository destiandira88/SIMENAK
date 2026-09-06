<?php
/**
 * Panel acuan pengerjaan untuk role produksi.
 *
 * @var array<string, mixed>         $order
 * @var list<array<string, mixed>>  $attrs
 */
$kodeOrder       = (string) ($order['kode_order'] ?? '');
$idOrder         = (int) ($order['id_order'] ?? 0);
$status          = (string) ($order['status'] ?? '');
$isCustom        = (int) ($order['is_custom'] ?? 0) === 1;
$namaProduk      = $isCustom ? 'Pesanan Custom' : (string) ($order['nama_produk'] ?? '-');
$tipePesananLabel = $isCustom ? 'Custom' : 'Standar';
$namaProdukKatalog = trim((string) ($order['nama_produk'] ?? ''));
$namaProdukLabel  = $namaProdukKatalog !== ''
    ? $namaProdukKatalog . ' ' . $tipePesananLabel
    : $namaProduk;
$jenisPelangganLabel = (string) ($order['jenis_pelanggan'] ?? 'perseorangan') === 'perusahaan'
    ? 'Kerjasama'
    : 'Perseorangan';
$sisaKuota       = (int) ($order['sisa_kuota'] ?? 0);
$kuotaRevisi     = (int) ($order['kuota_revisi'] ?? 0);
$usedKuota       = max(0, $kuotaRevisi - $sisaKuota);
$progressPct     = $kuotaRevisi > 0 ? min(100, (int) round(($usedKuota / $kuotaRevisi) * 100)) : 0;
$metodeKirim     = (string) ($order['metode_pengiriman'] ?? 'kurir');
$gambarKatalog   = (string) ($order['gambar_katalog'] ?? '');
$referensiDesain = (string) ($order['referensi_desain'] ?? '');
$noTelp          = trim((string) ($order['no_telp'] ?? ''));
$deadline        = $order['deadline_produksi'] ?? null;
$deadlineOverdue = false;
$deadlineUrgent  = false;
$deadlineLabel   = '-';

if (!empty($deadline)) {
    $tsDeadline    = strtotime((string) $deadline);
    $deadlineLabel = date('d M Y', $tsDeadline);
    $daysLeft      = (int) floor(($tsDeadline - strtotime('today')) / 86400);
    $deadlineOverdue = $daysLeft < 0;
    $deadlineUrgent  = $daysLeft >= 0 && $daysLeft <= 3;
}

$gambarUrl = '';
if ($gambarKatalog !== '' && is_file(FCPATH . 'uploads/katalog/' . $gambarKatalog)) {
    $gambarUrl = base_url('uploads/katalog/' . $gambarKatalog);
}
?>
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden lg:sticky lg:top-4">
    <div class="px-5 py-4 border-b border-slate-100 bg-[#051747] text-white">
        <p class="text-xs font-bold uppercase tracking-wide text-white/70">Acuan Pengerjaan</p>
        <p class="font-mono font-bold text-lg mt-0.5"><?= esc($kodeOrder) ?></p>
    </div>

    <div class="p-5 space-y-4">
        <?php if ($gambarUrl !== ''): ?>
            <div class="rounded-xl overflow-hidden border border-slate-100 bg-slate-50 aspect-[4/3]">
                <img src="<?= esc($gambarUrl) ?>"
                     alt="<?= esc($namaProduk) ?>"
                     class="w-full h-full object-cover">
            </div>
        <?php else: ?>
            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 aspect-[4/3] flex items-center justify-center text-slate-400 text-sm">
                Tanpa gambar katalog
            </div>
        <?php endif; ?>

        <div>
            <p class="text-xs font-bold uppercase text-slate-400 mb-1">Produk</p>
            <p class="font-semibold text-[#051747]"><?= esc($namaProdukLabel) ?></p>
            <?php if ($isCustom): ?>
                <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">Custom</span>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
                <p class="text-xs font-bold uppercase text-slate-400 mb-1">Status</p>
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-semibold <?= esc(getStatusBadgeClass($status)) ?>">
                    <?= esc(getOrderStatusLabel($order)) ?>
                </span>
            </div>
            <div>
                <p class="text-xs font-bold uppercase text-slate-400 mb-1">Jumlah</p>
                <p class="font-semibold text-slate-700">
                    <?= esc((string) ($order['jumlah_order'] ?? 0)) ?>
                    <?= esc((string) ($order['satuan'] ?? 'pcs')) ?>
                </p>
            </div>
            <div class="col-span-2">
                <p class="text-xs font-bold uppercase text-slate-400 mb-1">Deadline Produksi</p>
                <p class="font-semibold <?= $deadlineOverdue ? 'text-red-600' : ($deadlineUrgent ? 'text-amber-600' : 'text-slate-700') ?>">
                    <?= esc($deadlineLabel) ?>
                </p>
                <?php if ($deadlineOverdue): ?>
                    <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-100 text-red-800">⚠ Lewat deadline</span>
                <?php elseif ($deadlineUrgent): ?>
                    <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">⚡ Mendesak</span>
                <?php endif; ?>
            </div>
            <div class="col-span-2">
                <p class="text-xs font-bold uppercase text-slate-400 mb-1">Pelanggan</p>
                <p class="font-semibold text-slate-700">
                    <?= esc((string) ($order['nama_pelanggan'] ?? '-')) ?>
                    (<?= esc($jenisPelangganLabel) ?>)
                </p>
                <?php if ($noTelp !== ''): ?>
                    <p class="text-xs text-slate-500 mt-0.5"><?= esc($noTelp) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-span-2">
                <p class="text-xs font-bold uppercase text-slate-400 mb-1">Metode Kirim</p>
                <p class="text-slate-700"><?= $metodeKirim === 'kurir' ? 'Kurir' : 'Ambil Sendiri' ?></p>
            </div>
        </div>

        <?php if (!empty($order['detail_pesanan'])): ?>
            <div class="border-t border-slate-100 pt-4">
                <p class="text-xs font-bold uppercase text-slate-500 mb-2">Spesifikasi Pesanan</p>
                <p class="text-sm text-slate-600 whitespace-pre-line"><?= esc((string) $order['detail_pesanan']) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($isCustom && !empty($order['catatan_custom'])): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
                <p class="text-xs font-bold uppercase text-amber-800 mb-1">Catatan Custom</p>
                <p class="text-sm text-amber-900 whitespace-pre-line"><?= esc((string) $order['catatan_custom']) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($attrs !== []): ?>
            <?php helper('eav'); ?>
            <div class="border-t border-slate-100 pt-4">
                <p class="text-xs font-bold uppercase text-indigo-800 mb-3">Spesifikasi Produk (EAV)</p>
                <div class="space-y-3">
                    <?php foreach ($attrs as $a): ?>
                        <?php
                        $fieldKey   = (string) ($a['attribute_key'] ?? '');
                        $fieldType  = (string) ($a['field_type'] ?? 'text');
                        $fieldLabel = (string) ($a['field_label'] ?? $fieldKey);
                        $attrVal    = (string) ($a['attribute_val'] ?? '');
                        $isTurutMengundang = $fieldKey === 'turut_mengundang';
                        if ($fieldLabel === '') {
                            $fieldLabel = $fieldKey;
                        }
                        ?>
                        <div>
                            <p class="text-[10px] font-bold uppercase text-slate-400 mb-0.5"><?= esc($fieldLabel) ?></p>
                            <?php if ($fieldType === 'file' && $attrVal !== ''): ?>
                                <a href="<?= esc(base_url('uploads/lampiran_peta/' . $attrVal)) ?>"
                                   target="_blank" rel="noopener noreferrer"
                                   class="text-[#2E5CE6] underline text-sm">Lihat File</a>
                            <?php elseif ($isTurutMengundang): ?>
                                <?php $turutList = parseTurutMengundangList($attrVal); ?>
                                <?php if ($turutList === []): ?>
                                    <p class="text-sm text-slate-400">-</p>
                                <?php else: ?>
                                    <ol class="list-decimal list-inside space-y-1 text-sm text-slate-700 pl-0.5">
                                        <?php foreach ($turutList as $namaTurut): ?>
                                            <li class="leading-relaxed"><?= esc($namaTurut) ?></li>
                                        <?php endforeach; ?>
                                    </ol>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-sm text-slate-700 whitespace-pre-line"><?= esc($attrVal !== '' ? $attrVal : '-') ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="border-t border-slate-100 pt-4">
            <p class="text-xs font-bold uppercase text-slate-500 mb-2">Referensi Desain</p>
            <?php if ($referensiDesain !== ''): ?>
                <a href="<?= esc(base_url('uploads/referensi/' . $referensiDesain)) ?>"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 text-sm font-semibold text-[#2E5CE6] hover:underline">
                    📎 Lihat Referensi Pelanggan
                </a>
                <?php if (preg_match('/\.(jpe?g|png|gif|webp)$/i', $referensiDesain)): ?>
                    <div class="mt-2 rounded-lg overflow-hidden border border-slate-100">
                        <img src="<?= esc(base_url('uploads/referensi/' . $referensiDesain)) ?>"
                             alt="Referensi"
                             class="w-full max-h-40 object-contain bg-slate-50">
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-sm text-slate-400">Tidak ada file referensi</p>
            <?php endif; ?>
        </div>

        <div class="border-t border-slate-100 pt-4">
            <div class="flex justify-between text-xs font-semibold uppercase text-slate-500 mb-2">
                <span>Kuota Revisi</span>
                <span class="<?= $sisaKuota <= 1 ? 'text-red-600' : 'text-emerald-600' ?>">
                    Sisa <?= esc((string) $sisaKuota) ?>/<?= esc((string) $kuotaRevisi) ?>
                </span>
            </div>
            <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full rounded-full bg-[#2E5CE6] transition-all" style="width:<?= esc((string) $progressPct) ?>%"></div>
            </div>
        </div>

        <?php if ($status === 'proses_cetak'): ?>
            <a href="<?= esc(site_url('monitoring-produksi/' . $kodeOrder)) ?>"
                class="block w-full text-center border-2 border-[#051747] text-[#051747] py-2.5 rounded-full text-sm font-bold hover:bg-[#051747] hover:text-white transition-colors mb-3">
                Monitoring Produksi
            </a>
            <form method="post"
                action="<?= esc(site_url('produksi/update-status')) ?>"
                class="js-action-confirm-form"
                data-confirm-variant="status-finishing"
                data-confirm-kode="<?= esc($kodeOrder) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id_order" value="<?= esc((string) $idOrder) ?>">
                <input type="hidden" name="new_status" value="finishing">
                <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-full text-sm font-bold hover:bg-indigo-700 transition-colors">
                    ✓ Cetak Selesai
                </button>
            </form>
        <?php elseif ($status === 'finishing'): ?>
            <a href="<?= esc(site_url('monitoring-produksi/' . $kodeOrder)) ?>"
                class="block w-full text-center border-2 border-[#051747] text-[#051747] py-2.5 rounded-full text-sm font-bold hover:bg-[#051747] hover:text-white transition-colors">
                Monitoring Produksi
            </a>
        <?php endif; ?>
    </div>
</div>
