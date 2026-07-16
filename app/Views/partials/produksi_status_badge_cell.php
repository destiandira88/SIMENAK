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
                class="produksi-status-badge-btn produksi-status-badge-btn--editable inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-semibold <?= esc($badgeClass) ?>"
                data-status-quick-trigger
                data-id-order="<?= esc((string) $idOrder) ?>"
                aria-expanded="false"
                aria-haspopup="menu"
                title="Klik untuk ubah ke Finishing"
                aria-label="Status <?= esc($label) ?>. Klik untuk ubah ke Finishing">
                <span class="produksi-status-badge-label"><?= esc($label) ?></span>
                <svg class="produksi-status-badge-chevron h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
                <span class="produksi-status-badge-spinner hidden" aria-hidden="true">
                    <svg class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4z"></path>
                    </svg>
                </span>
            </button>
            <div class="produksi-status-quick-menu" role="menu" aria-label="Ubah status">
                <p class="px-2.5 pt-1.5 pb-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Ubah ke</p>
                <button type="button"
                    class="produksi-status-quick-option"
                    role="menuitem"
                    data-status-target="finishing">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold <?= esc(getStatusBadgeClass('finishing')) ?>">Finishing</span>
                    <span class="ml-auto text-[10px] text-slate-400">Cetak selesai</span>
                </button>
            </div>
        </div>
    <?php else: ?>
        <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc($badgeClass) ?>">
            <?= esc($label) ?>
        </span>
    <?php endif; ?>
</td>
