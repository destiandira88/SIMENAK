<?php
/**
 * @var string $title
 * @var string $subtitle
 * @var string $breadcrumb
 * @var string $image
 * @var bool   $subtitleAllowHtml
 */
$title      = (string) ($title ?? '');
$subtitle   = (string) ($subtitle ?? '');
$breadcrumb = (string) ($breadcrumb ?? '');
$image      = (string) ($image ?? 'cs.png');
$subtitleAllowHtml = (bool) ($subtitleAllowHtml ?? false);
?>
<style>
    html[data-theme="dark"] .page-hero-banner {
        background: #151f2e;
        border-color: rgba(255, 255, 255, 0.08);
    }

    html[data-theme="dark"] .page-hero-banner .page-hero-breadcrumb {
        color: #667085;
    }

    html[data-theme="dark"] .page-hero-banner .page-hero-breadcrumb-sep {
        color: rgba(255, 255, 255, 0.18);
    }

    html[data-theme="dark"] .page-hero-banner .page-hero-title {
        color: #f2f4f7;
    }

    html[data-theme="dark"] .page-hero-banner .page-hero-subtitle {
        color: #98a2b3;
    }
</style>
<div class="page-hero-banner mb-6 relative overflow-hidden rounded-2xl border border-[#D6E4FF] bg-gradient-to-r from-[#EEF4FF] via-[#F4F8FF] to-[#E8F0FE] px-4 sm:px-5 py-1">
    <div class="relative z-10 flex items-center justify-between gap-3 sm:gap-4">
        <div class="min-w-0 flex-1">
            <?php if ($breadcrumb !== ''): ?>
                <p class="page-hero-breadcrumb text-xs font-medium text-slate-500 mb-1">
                    <span>Beranda</span>
                    <span class="page-hero-breadcrumb-sep mx-1.5 text-slate-300" aria-hidden="true">•</span>
                    <span><?= esc($breadcrumb) ?></span>
                </p>
            <?php endif; ?>
            <h2 class="page-hero-title text-xl sm:text-2xl font-extrabold text-[#051747] leading-tight"><?= esc($title) ?></h2>
            <?php if ($subtitle !== ''): ?>
                <p class="page-hero-subtitle mt-1 text-sm text-slate-500 leading-snug"><?= $subtitleAllowHtml ? $subtitle : esc($subtitle) ?></p>
            <?php endif; ?>
        </div>
        <div class="hidden sm:flex shrink-0 items-center pointer-events-none select-none" aria-hidden="true">
            <img
                src="<?= esc(base_url('assets/' . $image), 'attr') ?>"
                alt=""
                class="h-16 sm:h-[4.75rem] lg:h-[4.95rem] w-auto object-contain"
                loading="lazy">
        </div>
    </div>
</div>
