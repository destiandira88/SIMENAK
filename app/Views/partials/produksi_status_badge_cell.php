<?php
/**
 * @var string               $status
 * @var array<string, mixed> $order
 * @var int                  $idOrder
 * @var bool                 $showAll
 * @var bool                 $readOnly
 */
$status   = (string) ($status ?? '');
$order    = is_array($order ?? null) ? $order : [];
$idOrder  = (int) ($idOrder ?? 0);
$showAll  = (bool) ($showAll ?? false);
$readOnly = (bool) ($readOnly ?? false);
$badgeClass = getStatusBadgeClass($status);
$label      = getOrderStatusLabel($order);
$clickable  = $showAll && ! $readOnly && $status === 'proses_cetak';
?>
<td class="px-4 py-3.5">
    <?php if ($clickable): ?>
        <div class="produksi-status-quick relative inline-block">
            <button
                type="button"
                class="produksi-status-badge-btn inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold <?= esc($badgeClass) ?>"
                data-status-quick-trigger
                data-id-order="<?= esc((string) $idOrder) ?>"
                aria-expanded="false"
                aria-haspopup="menu">
                <span class="produksi-status-badge-label"><?= esc($label) ?></span>
                <span class="produksi-status-badge-spinner hidden" aria-hidden="true">
                    <svg class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4z"></path>
                    </svg>
                </span>
            </button>
            <div class="produksi-status-quick-menu" role="menu" aria-label="Ubah status">
                <button type="button"
                    class="produksi-status-quick-option"
                    role="menuitem"
                    data-status-target="finishing">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold <?= esc(getStatusBadgeClass('finishing')) ?>">Finishing</span>
                </button>
            </div>
        </div>
    <?php else: ?>
        <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc($badgeClass) ?>">
            <?= esc($label) ?>
        </span>
    <?php endif; ?>
</td>
