<?php
/**
 * Ikon SVG untuk tombol/aksi UI umum.
 *
 * @var string $icon   plus|copy|arrow-square
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
<?php elseif ($icon === 'arrow-square'): ?>
    <svg class="<?= esc($class) ?>" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M11 9L8 12M8 12L11 15M8 12H16M7.2 20H16.8C17.9201 20 18.4802 20 18.908 19.782C19.2843 19.5903 19.5903 19.2843 19.782 18.908C20 18.4802 20 17.9201 20 16.8V7.2C20 6.0799 20 5.51984 19.782 5.09202C19.5903 4.71569 19.2843 4.40973 18.908 4.21799C18.4802 4 17.9201 4 16.8 4H7.2C6.0799 4 5.51984 4 5.09202 4.21799C4.71569 4.40973 4.40973 4.71569 4.21799 5.09202C4 5.51984 4 6.07989 4 7.2V16.8C4 17.9201 4 18.4802 4.21799 18.908C4.40973 19.2843 4.71569 19.5903 5.09202 19.782C5.51984 20 6.07989 20 7.2 20Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
<?php endif; ?>
