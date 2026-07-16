<?php
/**
 * Ikon SVG untuk tombol/aksi UI umum.
 *
 * @var string $icon   plus
 * @var string $class
 */
$icon  = (string) ($icon ?? '');
$class = (string) ($class ?? 'h-4 w-4 shrink-0');
?>
<?php if ($icon === 'plus'): ?>
    <svg class="<?= esc($class) ?>" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
    </svg>
<?php elseif ($icon === 'copy'): ?>
    <svg class="<?= esc($class) ?>" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <rect x="9" y="9" width="11" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
        <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    </svg>
<?php endif; ?>
