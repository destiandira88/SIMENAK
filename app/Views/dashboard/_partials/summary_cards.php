<?php
/**
 * @var list<array{label: string, value: int|string, icon: string, color: string, isText?: bool, subLabel?: string, tooltip?: string, valueColor?: string, valueHighlight?: string|null, valueSuffix?: string|null}> $cards
 * @var string|null $gridClass
 */
$iconPaths = [
    'clipboard'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>',
    'clock'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'wallet'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
    'check'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'calendar'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
    'star'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>',
    'truck'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 6.5h9.5V16H3V6.5zm9.5 3H16l3.5 3.5V16h-7V9.5zm0-3h2.5l2 3H12.5V6.5zM6.5 17.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm11 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>',
    'check-circle' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'cash'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>',
    'layers'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>',
    'edit'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
    'printer'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>',
    'sparkles'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
    'chart'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
    'x-circle'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
];
$gridClass = $gridClass ?? 'grid-cols-1 sm:grid-cols-2 xl:grid-cols-4';
?>
<div class="grid <?= esc($gridClass) ?> gap-4 mb-6">
    <?php foreach ($cards as $card): ?>
        <?php
        $iconKey = $card['icon'] ?? 'clipboard';
        $path    = $iconPaths[$iconKey] ?? $iconPaths['clipboard'];
        $color      = esc($card['color'] ?? '#2E5CE6');
        $valueColor = esc($card['valueColor'] ?? 'var(--navy)');
        $isText     = !empty($card['isText']);
        ?>
        <div class="card p-6 flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0"
                 style="background: <?= $color ?>15;">
                <svg class="w-7 h-7" style="color: <?= $color ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <?= $path ?>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-semibold uppercase tracking-wider mb-1 flex items-center gap-0.5" style="color:var(--text-muted);">
                    <?= esc($card['label']) ?>
                    <?php if (!empty($card['tooltip'])): ?>
                        <?= view('partials/metric_info_tooltip', ['text' => $card['tooltip']]) ?>
                    <?php endif; ?>
                </p>
                <?php if ($card['valueHighlight'] ?? null): ?>
                    <p class="<?= $isText ? 'text-lg' : 'text-3xl' ?> font-extrabold">
                        <span style="color: <?= $valueColor ?>;"><?= esc((string) $card['valueHighlight']) ?></span>
                        <?php if (($card['valueSuffix'] ?? '') !== ''): ?>
                            <span class="font-extrabold" style="color: var(--navy);"> <?= esc((string) $card['valueSuffix']) ?></span>
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <p class="<?= $isText ? 'text-lg' : 'text-3xl' ?> font-extrabold truncate" style="color: <?= $valueColor ?>;">
                        <?= esc((string) $card['value']) ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($card['subLabel'])): ?>
                    <p class="text-[10px] text-slate-400 mt-0.5 truncate"><?= esc((string) $card['subLabel']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
