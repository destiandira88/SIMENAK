<?php
/**
 * Ikon SVG full color untuk halaman detail pesanan.
 *
 * @var string $icon   waiting|location|payment|clipboard|palette|arrow-left|arrow-right
 * @var string $class
 */
$icon  = (string) ($icon ?? '');
$class = (string) ($class ?? 'h-5 w-5 shrink-0');
?>
<?php if ($icon === 'waiting'): ?>
    <svg class="<?= esc($class) ?>" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M7 2h10v5.5l-2.75 2.75L17 12.5V22H7v-9.5l2.75-2.75L7 7.5V2z" fill="#F59E0B"/>
        <path d="M9 4h6v3.1l-3 3-3-3V4z" fill="#FDE68A"/>
        <path d="M9 13.9l3 3 3-3V20H9v-6.1z" fill="#FBBF24"/>
        <rect x="10.25" y="10.25" width="3.5" height="1.5" rx="0.75" fill="#D97706"/>
    </svg>
<?php elseif ($icon === 'location'): ?>
    <svg class="<?= esc($class) ?>" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" fill="#EF4444"/>
        <circle cx="12" cy="9" r="2.75" fill="#FFFFFF"/>
        <circle cx="12" cy="9" r="1.25" fill="#B91C1C"/>
    </svg>
<?php elseif ($icon === 'payment'): ?>
    <svg class="<?= esc($class) ?>" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <rect x="2" y="5" width="20" height="14" rx="2.5" fill="#051747"/>
        <rect x="2" y="9" width="20" height="3" fill="#2E5CE6"/>
        <rect x="4.5" y="13.5" width="5" height="3.5" rx="0.75" fill="#FBBF24"/>
        <rect x="11" y="14.25" width="7.5" height="1.25" rx="0.625" fill="#94A3B8"/>
        <rect x="11" y="16.25" width="5" height="1.25" rx="0.625" fill="#64748B"/>
    </svg>
<?php elseif ($icon === 'clipboard'): ?>
    <svg class="<?= esc($class) ?>" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <rect x="5" y="3" width="14" height="18" rx="2" fill="#6366F1"/>
        <rect x="7.5" y="1.5" width="9" height="4.5" rx="1.25" fill="#A5B4FC"/>
        <rect x="7.5" y="1.5" width="9" height="2.25" rx="1.125" fill="#C7D2FE"/>
        <rect x="8" y="9" width="8" height="1.5" rx="0.75" fill="#E0E7FF"/>
        <rect x="8" y="12" width="8" height="1.5" rx="0.75" fill="#E0E7FF"/>
        <rect x="8" y="15" width="5.5" height="1.5" rx="0.75" fill="#E0E7FF"/>
    </svg>
<?php elseif ($icon === 'palette'): ?>
    <svg class="<?= esc($class) ?>" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M12 3c-4.42 0-8 3.13-8 7.5 0 2.76 1.74 5.18 4.35 6.2.45.18.75.6.75 1.08v.72c0 .83.67 1.5 1.5 1.5h1.1c.55 0 1-.45 1-1v-.55c0-.83.67-1.5 1.5-1.5H15c2.76 0 5-2.24 5-5 0-4.37-3.58-7.5-8-7.5z" fill="#FDE68A"/>
        <circle cx="8.5" cy="10" r="1.35" fill="#EF4444"/>
        <circle cx="11.5" cy="7.5" r="1.35" fill="#3B82F6"/>
        <circle cx="15" cy="10" r="1.35" fill="#22C55E"/>
        <circle cx="13" cy="13.5" r="1.35" fill="#A855F7"/>
        <circle cx="9.5" cy="13.5" r="1.1" fill="#F97316"/>
    </svg>
<?php elseif ($icon === 'arrow-left'): ?>
    <svg class="<?= esc($class) ?>" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M15 6.75 9.75 12 15 17.25" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
<?php elseif ($icon === 'arrow-right'): ?>
    <svg class="<?= esc($class) ?>" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M9 6.75 14.25 12 9 17.25" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
<?php endif; ?>
