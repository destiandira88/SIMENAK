<?php
/**
 * Monitoring Cetak → Finishing (1 pesanan).
 *
 * @var array<string, mixed> $order
 * @var string               $role
 * @var string               $tglMulaiCetak
 * @var string               $tglFinishing
 * @var int|null             $durasiHari
 * @var bool                 $stepCetakDone
 * @var bool                 $stepFinishingDone
 * @var bool                 $canKlikFinishing
 */
helper('deadline');

$kodeOrder       = (string) ($order['kode_order'] ?? '');
$idOrder         = (int) ($order['id_order'] ?? 0);
$namaProduk      = (string) ($order['nama_produk'] ?? '-');
$namaPelanggan   = (string) ($order['nama_pelanggan'] ?? '-');
$status          = (string) ($order['status'] ?? '');
$jumlahOrder     = (int) ($order['jumlah_order'] ?? 0);
$satuan          = (string) ($order['satuan'] ?? 'pcs');
$deadline        = trim((string) ($order['deadline_produksi'] ?? ''));
$gambar          = trim((string) ($order['gambar_katalog'] ?? $order['gambar'] ?? ''));
$tglMulaiCetak   = (string) ($tglMulaiCetak ?? '');
$tglFinishing    = (string) ($tglFinishing ?? '');
$durasiHari      = $durasiHari ?? null;
$stepCetakDone   = (bool) ($stepCetakDone ?? false);
$stepFinishingDone = (bool) ($stepFinishingDone ?? false);
$canKlikFinishing  = (bool) ($canKlikFinishing ?? false);
$backUrl = site_url('manajemen-produksi');

$labelDurasi = '-';
if ($durasiHari !== null) {
    $labelDurasi = $durasiHari === 0
        ? '0 hari (sama hari)'
        : $durasiHari . ' hari';
}
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Monitoring Produksi') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title ?? 'Monitoring Produksi') ?><?= $this->endSection() ?>
<?= $this->section('banner_title') ?>Monitoring Produksi<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>Lacak tahap cetak hingga finishing untuk <?= esc($kodeOrder) ?>.<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="max-w-2xl mx-auto">
    <a href="<?= esc($backUrl) ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#2E5CE6] hover:underline mb-4">
        <?= view('partials/order_detail_svg_icon', ['icon' => 'arrow-left', 'class' => 'h-4 w-4 shrink-0']) ?>
        Manajemen Produksi
    </a>

    <div class="bg-white rounded-[20px] border border-[#E2E8F0] shadow-sm p-6 mb-6">
        <div class="flex gap-4 items-start">
            <div class="w-20 h-20 rounded-2xl bg-slate-100 overflow-hidden shrink-0 border border-slate-100">
                <?php if ($gambar !== ''): ?>
                    <img src="<?= esc(base_url('uploads/katalog/' . $gambar)) ?>"
                        alt="<?= esc($namaProduk) ?>"
                        class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-slate-300 text-2xl">🖼</div>
                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-wide text-[#83A2CD] mb-1">Produk</p>
                <h2 class="text-lg font-extrabold text-[#051747] leading-snug"><?= esc($namaProduk) ?></h2>
                <p class="font-mono text-sm font-semibold text-[#2E5CE6] mt-1"><?= esc($kodeOrder) ?></p>
                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600">
                    <span>Pelanggan: <strong class="text-[#051747]"><?= esc($namaPelanggan) ?></strong></span>
                    <span>Jumlah: <strong class="text-[#051747]"><?= esc((string) $jumlahOrder) ?> <?= esc($satuan) ?></strong></span>
                    <?php if ($deadline !== ''): ?>
                        <span>Deadline: <strong class="text-[#051747]"><?= esc(formatTanggalId($deadline)) ?></strong></span>
                    <?php endif; ?>
                </div>
                <div class="mt-3">
                    <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc(getStatusBadgeClass($status)) ?>">
                        <?= esc(getOrderStatusLabel($order, $role)) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-[20px] border border-[#E2E8F0] shadow-sm p-6 sm:p-8">
        <h3 class="text-sm font-bold uppercase tracking-wide text-[#051747] mb-8">Tahap Produksi</h3>

        <div class="relative flex items-start justify-between gap-2 mb-10 px-2">
            <?php
            $linePct = $stepFinishingDone ? 100 : ($stepCetakDone ? 50 : 0);
            ?>
            <div class="absolute left-[16%] right-[16%] top-5 h-0.5 bg-slate-200 rounded-full" aria-hidden="true"></div>
            <div class="absolute left-[16%] top-5 h-0.5 bg-[#2E5CE6] rounded-full transition-all"
                style="width: calc((100% - 32%) * <?= (int) $linePct ?> / 100);"
                aria-hidden="true"></div>

            <div class="relative z-10 flex flex-col items-center w-[40%] text-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold border-2
                    <?= $stepCetakDone
                        ? 'bg-[#051747] border-[#051747] text-white'
                        : 'bg-white border-slate-200 text-slate-400' ?>">
                    <?= $stepCetakDone ? '✓' : '1' ?>
                </div>
                <p class="mt-3 text-sm font-bold <?= $stepCetakDone ? 'text-[#051747]' : 'text-slate-400' ?>">Cetak</p>
                <p class="mt-1 text-xs text-slate-500">
                    <?php if ($tglMulaiCetak !== ''): ?>
                        Mulai: <?= esc(formatTanggalId($tglMulaiCetak)) ?>
                    <?php elseif ($status === 'proses_cetak'): ?>
                        Sedang dicetak
                    <?php else: ?>
                        Belum dimulai
                    <?php endif; ?>
                </p>
            </div>

            <div class="relative z-10 flex flex-col items-center w-[40%] text-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold border-2
                    <?= $stepFinishingDone
                        ? 'bg-[#051747] border-[#051747] text-white'
                        : ($canKlikFinishing
                            ? 'bg-white border-[#2E5CE6] text-[#2E5CE6] ring-4 ring-[#2E5CE6]/10'
                            : 'bg-white border-slate-200 text-slate-400') ?>">
                    <?= $stepFinishingDone ? '✓' : '2' ?>
                </div>
                <p class="mt-3 text-sm font-bold <?= $stepFinishingDone || $canKlikFinishing ? 'text-[#051747]' : 'text-slate-400' ?>">Finishing</p>
                <p class="mt-1 text-xs text-slate-500">
                    <?php if ($tglFinishing !== ''): ?>
                        Selesai: <?= esc(formatTanggalId($tglFinishing)) ?>
                    <?php elseif ($canKlikFinishing): ?>
                        Siap dikonfirmasi
                    <?php else: ?>
                        Menunggu cetak selesai
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <?php if ($canKlikFinishing): ?>
            <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-5">
                <p class="text-sm font-semibold text-[#051747] mb-1">Konfirmasi selesai cetak</p>
                <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                    Setelah Anda menekan tombol di bawah, tahap finishing akan dicatat beserta tanggalnya,
                    dan lama proses dari cetak ke finishing akan ditampilkan.
                </p>
                <form method="post"
                    action="<?= esc(site_url('produksi/update-status')) ?>"
                    class="js-action-confirm-form"
                    data-confirm-variant="status-finishing"
                    data-confirm-kode="<?= esc($kodeOrder) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_order" value="<?= esc((string) $idOrder) ?>">
                    <input type="hidden" name="new_status" value="finishing">
                    <input type="hidden" name="return_to" value="monitoring">
                    <button type="submit"
                        class="w-full bg-[#051747] text-white py-3 rounded-full text-sm font-bold uppercase tracking-wide hover:bg-[#2E5CE6] transition-colors">
                        Finishing
                    </button>
                </form>
            </div>
        <?php elseif ($stepFinishingDone && $tglFinishing !== ''): ?>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-5">
                <p class="text-sm font-bold text-emerald-900 mb-3 inline-flex items-center gap-2">
                    <svg class="h-4 w-4 shrink-0 text-emerald-700" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Ringkasan Cetak → Finishing
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                    <div class="rounded-xl bg-white border border-emerald-100 px-3 py-3">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-1">Mulai Cetak</p>
                        <p class="font-bold text-[#051747]">
                            <?= $tglMulaiCetak !== '' ? esc(formatTanggalId($tglMulaiCetak)) : 'Tidak tercatat' ?>
                        </p>
                    </div>
                    <div class="rounded-xl bg-white border border-emerald-100 px-3 py-3">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-1">Finishing</p>
                        <p class="font-bold text-[#051747]"><?= esc(formatTanggalId($tglFinishing)) ?></p>
                    </div>
                    <div class="rounded-xl bg-white border border-emerald-100 px-3 py-3">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-1">Durasi</p>
                        <p class="font-bold text-[#051747]"><?= esc($labelDurasi) ?></p>
                    </div>
                </div>
            </div>
        <?php elseif ($role === 'owner' && $status === 'proses_cetak'): ?>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                Menunggu bagian produksi mengonfirmasi finishing.
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
