<?php

/**
 * @var array<string, mixed> $snapshot
 * @var array<string, mixed> $segmenTahap
 */
$snapshot    = $snapshot ?? [];
$segmenTahap = $segmenTahap ?? [];

$pesananAktif     = (int) ($snapshot['pesananAktif'] ?? 0);
$tahapDesain      = (int) ($snapshot['tahapDesain'] ?? 0);
$tahapCetakFinish = (int) ($snapshot['tahapCetakFinish'] ?? 0);
$melewatiDeadline = (int) ($snapshot['melewatiDeadline'] ?? 0);

$topSlice = null;
$topCount = 0;
foreach ($segmenTahap['slices'] ?? [] as $slice) {
    $count = (int) ($slice['count'] ?? 0);
    if ($count > $topCount) {
        $topCount = $count;
        $topSlice = $slice;
    }
}

$pctDesain = $pesananAktif > 0 ? round(($tahapDesain / $pesananAktif) * 100) : 0;
$pctCetak  = $pesananAktif > 0 ? round(($tahapCetakFinish / $pesananAktif) * 100) : 0;
?>
<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 h-full flex flex-col">
    <h3 class="text-base font-bold text-[#051747] mb-1">Ringkasan Antrian</h3>
    <p class="text-xs text-slate-500 mt-0.5 mb-5">Interpretasi cepat antrian desain hari ini.</p>

    <?php if ($pesananAktif === 0): ?>
        <p class="text-sm py-16 text-center text-slate-400 flex-1">Tidak ada antrian desain aktif saat ini.</p>
    <?php else: ?>
        <div class="space-y-5 flex-1">
            <?php if ($topSlice !== null && $topCount > 0): ?>
                <div class="rounded-xl border border-slate-100 bg-[#F8FAFF] px-4 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 mb-1">Tahap Terbanyak</p>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full shrink-0" style="background-color: <?= esc((string) ($topSlice['color'] ?? '#2E5CE6')) ?>"></span>
                        <p class="text-sm font-bold text-[#051747]">
                            <?= esc((string) ($topSlice['label'] ?? '-')) ?>
                            <span class="font-semibold text-slate-500">— <?= esc((string) $topCount) ?> pesanan</span>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <div>
                <div class="flex items-center justify-between text-xs font-semibold text-slate-600 mb-1.5">
                    <span>Tahap Desain</span>
                    <span><?= esc((string) $tahapDesain) ?> pesanan (<?= esc((string) $pctDesain) ?>%)</span>
                </div>
                <div class="h-2.5 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full bg-[#8B5CF6] transition-all" style="width: <?= esc((string) max(0, min(100, $pctDesain))) ?>%"></div>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between text-xs font-semibold text-slate-600 mb-1.5">
                    <span>Tahap Cetak &amp; Finishing</span>
                    <span><?= esc((string) $tahapCetakFinish) ?> pesanan (<?= esc((string) $pctCetak) ?>%)</span>
                </div>
                <div class="h-2.5 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full bg-[#10B981] transition-all" style="width: <?= esc((string) max(0, min(100, $pctCetak))) ?>%"></div>
                </div>
            </div>

            <?php if ($melewatiDeadline > 0): ?>
                <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3">
                    <p class="text-sm font-semibold text-red-800">
                        <?= esc((string) $melewatiDeadline) ?> pesanan melewati deadline produksi
                    </p>
                    <p class="text-xs text-red-600 mt-0.5">Perlu tindak lanjut tim produksi.</p>
                </div>
            <?php else: ?>
                <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                    <p class="text-sm font-semibold text-emerald-800">Semua antrian masih dalam deadline</p>
                </div>
            <?php endif; ?>

            <div class="pt-2 border-t border-slate-100">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Total Antrian Aktif</p>
                <p class="text-2xl font-extrabold text-[#051747] mt-1"><?= esc((string) $pesananAktif) ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>
