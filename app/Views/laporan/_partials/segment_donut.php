<?php

/**
 * @var string               $title
 * @var string               $chartId
 * @var array<string, mixed> $segment
 * @var string|null          $subtitle
 * @var bool|null            $showPercent
 * @var bool|null            $showRevenue
 */
$slices       = $segment['slices'] ?? [];
$totalCount   = (int) ($segment['totalCount'] ?? 0);
$hasData      = $totalCount > 0;
$subtitle     = trim((string) ($subtitle ?? 'Berdasarkan pesanan selesai pada periode ini.'));
$showPercent  = $showPercent ?? true;
$showRevenue  = $showRevenue ?? true;
?>
<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 h-full">
    <h3 class="text-base font-bold text-[#051747] mb-1"><?= esc($title) ?></h3>
    <p class="text-xs text-slate-500 mt-0.5 mb-4"><?= esc($subtitle) ?></p>

    <?php if (!$hasData): ?>
        <p class="text-sm py-16 text-center text-slate-400">Belum ada data pada periode ini.</p>
    <?php else: ?>
        <div class="flex flex-col sm:flex-row items-center gap-6">
            <div class="relative w-44 h-44 shrink-0">
                <canvas id="<?= esc($chartId) ?>" width="176" height="176" aria-label="<?= esc($title) ?>"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-2xl font-extrabold text-[#051747]"><?= esc((string) $totalCount) ?></span>
                    <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Pesanan</span>
                </div>
            </div>
            <div class="flex-1 w-full space-y-3">
                <?php foreach ($slices as $slice): ?>
                    <div class="flex items-start gap-3">
                        <span class="mt-1.5 w-3 h-3 rounded-full shrink-0" style="background-color: <?= esc((string) ($slice['color'] ?? '#CBD5E1')) ?>"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-[#051747]"><?= esc((string) ($slice['label'] ?? '-')) ?></p>
                            <p class="text-xs mt-0.5 text-slate-400">
                                <?= esc((string) ($slice['count'] ?? 0)) ?> pesanan
                                <?php if ($showPercent): ?>
                                    (<?= esc(number_format((float) ($slice['pct_count'] ?? 0), 1, ',', '.')) ?>%)
                                <?php endif; ?>
                                <?php if ($showRevenue): ?>
                                    · Rp <?= esc(number_format((float) ($slice['revenue'] ?? 0), 0, ',', '.')) ?>
                                    <?php if ((int) ($slice['count'] ?? 0) > 0): ?>
                                        · avg Rp <?= esc(number_format((float) ($slice['rata_rata'] ?? 0), 0, ',', '.')) ?>/pesanan
                                    <?php endif; ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
