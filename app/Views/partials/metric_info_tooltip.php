<?php
/**
 * @var string $text
 */
$text = trim((string) ($text ?? ''));
if ($text === '') {
    return;
}
?>
<span class="group relative inline-flex align-middle ml-0.5 shrink-0" tabindex="0" aria-label="Keterangan metrik">
    <svg class="w-3.5 h-3.5 text-slate-400 cursor-help" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <span class="pointer-events-none invisible opacity-0 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100 absolute z-20 bg-slate-800 text-white text-[11px] leading-relaxed rounded-lg p-2.5 w-56 -top-1 left-5 shadow-lg transition-opacity">
        <?= esc($text) ?>
    </span>
</span>
