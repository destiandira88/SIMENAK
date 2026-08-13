<?php
/**
 * @var array<string, mixed>              $order
 * @var list<array<string, mixed>>       $revisList
 * @var array<string, mixed>|null        $latest
 * @var string                           $role
 */
$kodeOrder     = (string) ($order['kode_order'] ?? '');
$idOrder       = (int) ($order['id_order'] ?? 0);
$sisaKuota     = (int) ($order['sisa_kuota'] ?? 0);
$kuotaRevisi   = (int) ($order['kuota_revisi'] ?? 0);
$usedKuota     = max(0, $kuotaRevisi - $sisaKuota);
$progressPct   = $kuotaRevisi > 0 ? min(100, (int) round(($usedKuota / $kuotaRevisi) * 100)) : 0;

$revisiBadges = [
    'uploaded'        => ['label' => 'Diunggah', 'dot' => 'bg-blue-500', 'class' => 'bg-blue-100 text-blue-800'],
    'diajukan_revisi' => ['label' => 'Revisi Diajukan', 'dot' => 'bg-amber-500', 'class' => 'bg-amber-100 text-amber-800'],
    'acc'             => ['label' => 'ACC ✓', 'dot' => 'bg-emerald-500', 'class' => 'bg-emerald-100 text-emerald-800'],
    'ditolak'         => ['label' => 'Ditolak', 'dot' => 'bg-red-500', 'class' => 'bg-red-100 text-red-800'],
];

$latestIdRevisi = is_array($latest) ? (int) ($latest['id_revisi'] ?? 0) : 0;
$orderStatus    = (string) ($order['status'] ?? '');

$adaDraftAcc = false;
$draftUntukPilih = [];
foreach ($revisList as $r) {
    $st = (string) ($r['status'] ?? '');
    if ($st === 'acc') {
        $adaDraftAcc = true;
    }
    if (in_array($st, ['uploaded', 'diajukan_revisi'], true)) {
        $draftUntukPilih[] = $r;
    }
}

$latestStatus = is_array($latest) ? (string) ($latest['status'] ?? '') : '';

$canAccRevisi = $role === 'pelanggan'
    && $sisaKuota > 0
    && is_array($latest)
    && $latestStatus === 'uploaded'
    && $latestIdRevisi > 0
    && in_array($orderStatus, ['proses_desain', 'proses_revisi'], true);

$pilihDraftUntukCetak = $role === 'pelanggan'
    && $sisaKuota <= 0
    && !$adaDraftAcc
    && $latestStatus === 'uploaded'
    && $draftUntukPilih !== []
    && in_array($orderStatus, ['proses_desain', 'proses_revisi'], true);

$menungguDraftFinal = $role === 'pelanggan'
    && $sisaKuota <= 0
    && !$adaDraftAcc
    && $latestStatus === 'diajukan_revisi'
    && in_array($orderStatus, ['proses_desain', 'proses_revisi'], true);

$backUrl = $role === 'pelanggan'
    ? site_url('order/detail/' . $kodeOrder)
    : site_url('antrian-desain');
$backLabel = $role === 'pelanggan' ? 'Detail Pesanan' : 'Antrian Desain';
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Riwayat Revisi') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title ?? 'Approval History Revisi') ?><?= $this->endSection() ?>

<?= $this->section('banner_title') ?><?= esc($page_title ?? 'Approval History Revisi') ?><?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>Riwayat persetujuan dan revisi desain untuk pesanan <?= esc($kodeOrder) ?>.<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="max-w-3xl mx-auto">
    <a href="<?= esc($backUrl) ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#2E5CE6] hover:underline mb-4">
        <?= view('partials/order_detail_svg_icon', ['icon' => 'arrow-left', 'class' => 'h-4 w-4 shrink-0']) ?>
        <?= esc($backLabel) ?>
    </a>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6" style="background:var(--navy); color:#fff; border:none;">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-5">
            <div>
                <p class="text-white/60 text-xs uppercase font-semibold mb-1">Kode Pesanan</p>
                <p class="font-mono font-bold text-lg"><?= esc($kodeOrder) ?></p>
            </div>
            <div>
                <p class="text-white/60 text-xs uppercase font-semibold mb-1">Produk</p>
                <p class="font-semibold"><?= esc((string) ($order['nama_produk'] ?? '-')) ?></p>
            </div>
            <div>
                <p class="text-white/60 text-xs uppercase font-semibold mb-1">Pelanggan</p>
                <p class="font-semibold"><?= esc((string) ($order['nama_pelanggan'] ?? '-')) ?></p>
            </div>
            <div>
                <p class="text-white/60 text-xs uppercase font-semibold mb-1">Status Pesanan</p>
                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold bg-white/15">
                    <?= esc(getOrderStatusLabel($order)) ?>
                </span>
            </div>
        </div>
        <div>
            <div class="flex justify-between text-xs font-semibold uppercase text-white/70 mb-2">
                <span>Kuota Revisi: <?= esc((string) $kuotaRevisi) ?> kali</span>
                <span class="<?= $sisaKuota <= 1 ? 'text-red-300' : 'text-emerald-300' ?>">
                    Sisa <?= esc((string) $sisaKuota) ?> kuota
                </span>
            </div>
            <div class="h-2 rounded-full bg-white/20 overflow-hidden">
                <div class="h-full rounded-full bg-[#2E5CE6] transition-all" style="width:<?= esc((string) $progressPct) ?>%"></div>
            </div>
            <p class="text-xs text-white/60 mt-2"><?= esc((string) $usedKuota) ?> revisi digunakan dari <?= esc((string) $kuotaRevisi) ?></p>
        </div>
    </div>

    <?php if ($role === 'pelanggan' && $sisaKuota === 1): ?>
        <div class="text-sm text-amber-900 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-6">
            <p class="font-bold mb-1">⚠ Ini kesempatan revisi terakhir Anda</p>
            <p class="text-xs text-amber-800 leading-relaxed">
                Setelah revisi ini dikirim, kuota revisi akan habis. Draft berikutnya dari Bagian Produksi hanya dapat disetujui (ACC) dan tidak dapat diajukan revisi kembali.
            </p>
        </div>
    <?php endif; ?>

    <h3 class="text-sm font-bold text-[#051747] uppercase tracking-wide mb-4">Riwayat Revisi Desain</h3>

    <?php if ($revisList === []): ?>
        <div class="bg-white rounded-xl border border-slate-100 p-8 text-center text-slate-500 text-sm">
            Belum ada riwayat revisi desain.
        </div>
    <?php else: ?>
        <div class="relative pl-6 space-y-6 mb-8">
            <div class="absolute left-2 top-2 bottom-2 w-px bg-slate-200"></div>
            <?php foreach ($revisList as $r): ?>
                <?php
                $revStatus = (string) ($r['status'] ?? 'uploaded');
                $badge     = $revisiBadges[$revStatus] ?? ['label' => $revStatus, 'dot' => 'bg-slate-400', 'class' => 'bg-slate-100 text-slate-600'];
                $versi     = (int) ($r['versi'] ?? 0);
                $revCode   = (string) ($r['kode_revisi'] ?? '');
                if ($revCode === '') {
                    $revCode = 'REV-' . str_pad((string) $idOrder, 4, '0', STR_PAD_LEFT)
                        . '-' . str_pad((string) $versi, 2, '0', STR_PAD_LEFT);
                }
                $fileDraft = (string) ($r['file_draft'] ?? '');
                $isAccDraft = $revStatus === 'acc';
                $cardClass  = $isAccDraft
                    ? 'revisi-card revisi-card-acc bg-white border-2 border-emerald-500 shadow-sm'
                    : 'revisi-card bg-white border border-slate-100 shadow-sm';
                ?>
                <div class="relative">
                    <span class="absolute -left-6 top-4 w-3.5 h-3.5 rounded-full ring-4 ring-white <?= esc($badge['dot']) ?>"></span>
                    <div class="<?= esc($cardClass) ?> rounded-xl p-4">
                        <?php if ($isAccDraft): ?>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-emerald-700 mb-2 flex items-center gap-1">
                                <span aria-hidden="true">✓</span> Draft dipilih untuk cetak
                            </p>
                        <?php endif; ?>
                        <div class="flex flex-wrap justify-between gap-2 items-start mb-3">
                            <p class="font-bold text-[#051747] text-sm">
                                Draft v<?= esc((string) $versi) ?>-<?= esc($revCode) ?>
                            </p>
                            <div class="text-right">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-semibold <?= esc($badge['class']) ?>">
                                    <?= esc($badge['label']) ?>
                                </span>
                                <?php if (!empty($r['created_at'])): ?>
                                    <p class="text-[10px] text-slate-400 mt-1">
                                        <?= esc(date('d M Y H:i', strtotime((string) $r['created_at']))) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex gap-4 flex-col sm:flex-row">
                            <div class="w-full sm:w-28 h-24 bg-slate-100 rounded-lg overflow-hidden shrink-0">
                                <?php if ($fileDraft !== ''): ?>
                                    <a href="<?= esc(base_url('uploads/draft_desain/' . $fileDraft)) ?>" target="_blank" rel="noopener noreferrer">
                                        <img src="<?= esc(base_url('uploads/draft_desain/' . $fileDraft)) ?>"
                                            alt="Draft"
                                            class="w-full h-full object-cover"
                                            onerror="this.style.display='none'">
                                    </a>
                                <?php else: ?>
                                    <span class="flex items-center justify-center h-full text-2xl">🖼</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 text-sm space-y-2">
                                <?php if (!empty($r['catatan_prod'])): ?>
                                    <p><span class="font-semibold text-slate-600">Catatan Produksi:</span>
                                        <?= esc((string) $r['catatan_prod']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($r['catatan_revisi'])): ?>
                                    <p class="text-amber-800 bg-amber-50 rounded-lg px-3 py-2">
                                        <span class="font-semibold">Catatan Revisi Anda:</span>
                                        <?= esc((string) $r['catatan_revisi']) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if ($revStatus === 'acc'): ?>
                                    <p class="text-xs text-emerald-700 font-semibold">✓ Desain disetujui-pesanan lanjut ke proses cetak</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($menungguDraftFinal): ?>
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5 mb-6">
            <p class="font-bold text-sm text-[#051747] mb-1 inline-flex items-center gap-2">
                <?= view('partials/order_detail_svg_icon', ['icon' => 'waiting', 'class' => 'h-5 w-5 shrink-0']) ?>
                Menunggu Produksi mengunggah draft final
            </p>
            <p class="text-xs text-slate-600 leading-relaxed">
                Kuota revisi Anda sudah habis. Tim produksi sedang menyiapkan draft final
                berdasarkan catatan revisi terakhir Anda. Setelah diunggah, Anda dapat
                menyetujui (ACC) desain untuk lanjut ke proses cetak.
            </p>
        </div>
    <?php elseif ($pilihDraftUntukCetak): ?>
        <div class="notice-danger rounded-xl p-5 mb-6">
            <h4 class="font-bold mb-1">Pilih draft untuk dicetak</h4>
            <p class="text-xs mb-4">Kuota revisi habis. Pilih versi yang akan diproses produksi.</p>
            <form method="post" action="<?= esc(site_url('revisi/acc')) ?>" class="space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="id_order" value="<?= esc((string) $idOrder) ?>">
                <?php foreach ($draftUntukPilih as $i => $d): ?>
                    <?php
                    $idRevPick = (int) ($d['id_revisi'] ?? 0);
                    $versiPick = (int) ($d['versi'] ?? 0);
                    $filePick  = (string) ($d['file_draft'] ?? '');
                    $stPick    = (string) ($d['status'] ?? '');
                    $badgePick = $revisiBadges[$stPick] ?? ['label' => $stPick, 'class' => 'bg-slate-100 text-slate-600'];
                    ?>
                    <label class="flex gap-3 items-start p-3 rounded-xl border-2 border-slate-200 bg-white cursor-pointer hover:border-[#2E5CE6] has-[:checked]:border-[#051747] has-[:checked]:bg-blue-50/40">
                        <input type="radio" name="id_revisi" value="<?= esc((string) $idRevPick) ?>"
                            class="mt-1 shrink-0 accent-[#051747]"
                            <?= $i === count($draftUntukPilih) - 1 ? 'checked' : '' ?> required>
                        <div class="w-16 h-14 bg-slate-100 rounded-lg overflow-hidden shrink-0">
                            <?php if ($filePick !== ''): ?>
                                <img src="<?= esc(base_url('uploads/draft_desain/' . $filePick)) ?>"
                                    alt="v<?= esc((string) $versiPick) ?>"
                                    class="w-full h-full object-cover">
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 text-sm">
                            <p class="font-bold text-[#051747]">Draft v<?= esc((string) $versiPick) ?></p>
                            <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold <?= esc($badgePick['class']) ?>">
                                <?= esc($badgePick['label']) ?>
                            </span>
                        </div>
                    </label>
                <?php endforeach; ?>
                <button type="submit"
                    class="w-full bg-emerald-500 text-white py-2.5 rounded-full text-sm font-bold hover:bg-emerald-600 transition-colors">
                    ✓ ACC Draft Terpilih untuk Cetak
                </button>
            </form>
        </div>
    <?php elseif ($canAccRevisi): ?>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 mb-6">
            <h4 class="font-bold text-[#051747] mb-3">Tindakan pada Draft Terbaru</h4>
            <div class="flex flex-wrap gap-3">
                <button type="button"
                    onclick="document.getElementById('modalAccHistory').classList.remove('hidden')"
                    class="bg-emerald-500 text-white px-5 py-2.5 rounded-full text-sm font-bold hover:bg-emerald-600 transition-colors">
                    ✓ ACC Desain
                </button>
                <button type="button"
                    onclick="document.getElementById('modalRevisiHistory').classList.remove('hidden')"
                    class="bg-amber-500 text-white px-5 py-2.5 rounded-full text-sm font-bold hover:bg-amber-600 transition-colors">
                    <?= $sisaKuota === 1 ? '↺ Ajukan Revisi Terakhir' : '↺ Ajukan Revisi' ?>
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($canAccRevisi && !$pilihDraftUntukCetak): ?>
    <div id="modalAccHistory" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-xl">
            <h3 class="font-bold text-lg text-[#051747] mb-2">Konfirmasi ACC Desain</h3>
            <p class="text-sm text-slate-500 mb-4">Desain akan di-ACC dan pesanan lanjut ke proses cetak.</p>
            <form method="post" action="<?= esc(site_url('revisi/acc')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id_order" value="<?= esc((string) $idOrder) ?>">
                <input type="hidden" name="id_revisi" value="<?= esc((string) $latestIdRevisi) ?>">
                <div class="flex gap-3 justify-end">
                    <button type="button"
                        onclick="document.getElementById('modalAccHistory').classList.add('hidden')"
                        class="px-4 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="bg-emerald-500 text-white rounded-lg px-4 py-2 text-sm font-bold hover:bg-emerald-600">
                        ✓ Ya, ACC Desain
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalRevisiHistory" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-xl">
            <h3 class="font-bold text-lg text-[#051747] mb-1">
                <?= $sisaKuota === 1 ? 'Ajukan Revisi Terakhir' : 'Ajukan Revisi Desain' ?>
            </h3>
            <?php if ($sisaKuota === 1): ?>
                <div class="text-xs text-amber-900 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mb-4">
                    <p class="font-bold mb-0.5 inline-flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 shrink-0 text-amber-700" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 3.5 2.8 19.5h18.4L12 3.5Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                            <path d="M12 9v5.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                            <circle cx="12" cy="17.25" r="1" fill="currentColor"/>
                        </svg>
                        Revisi terakhir pastikan catatan sudah lengkap
                    </p>
                    <p class="text-amber-800 leading-relaxed">
                        Setelah pengajuan revisi ini dikirim, kuota revisi akan habis. Draft berikutnya dari Produksi hanya dapat di-ACC dan tidak dapat diajukan revisi kembali.
                    </p>
                </div>
            <?php else: ?>
                <p class="text-xs text-amber-600 mb-4">Sisa kuota: <?= esc((string) $sisaKuota) ?> revisi</p>
            <?php endif; ?>
            <form id="formAjukanRevisiHistory" method="post" action="<?= esc(site_url('revisi/ajukan')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id_order" value="<?= esc((string) $idOrder) ?>">
                <input type="hidden" name="id_revisi" value="<?= esc((string) $latestIdRevisi) ?>">
                <textarea name="catatan_revisi" rows="4" required
                    placeholder="Jelaskan perubahan yang diinginkan secara detail..."
                    class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"></textarea>
                <div class="flex gap-3 justify-end mt-4">
                    <button type="button"
                        onclick="document.getElementById('modalRevisiHistory').classList.add('hidden')"
                        class="px-4 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="bg-amber-500 text-white rounded-lg px-4 py-2 text-sm font-bold hover:bg-amber-600">
                        <?= $sisaKuota === 1 ? 'Kirim Revisi Terakhir' : 'Kirim Revisi' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if ($role === 'pelanggan' && $sisaKuota === 1 && $canAccRevisi && !$pilihDraftUntukCetak): ?>
<script>
(function() {
    var form = document.getElementById('formAjukanRevisiHistory');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        var ok = window.confirm(
            'Ini adalah REVISI TERAKHIR Anda.\n\n' +
            'Setelah dikirim, draft berikutnya dari produksi hanya bisa di-ACC — tombol Ajukan Revisi tidak tersedia lagi.\n\n' +
            'Lanjutkan kirim revisi terakhir?'
        );
        if (!ok) {
            e.preventDefault();
        }
    });
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
