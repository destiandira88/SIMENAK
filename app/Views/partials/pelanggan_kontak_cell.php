<?php
/**
 * @var string      $nama
 * @var string|null $noTelp
 */
$nama   = (string) ($nama ?? '-');
$noTelp = trim((string) ($noTelp ?? ''));
?>
<p class="font-semibold text-[#051747]"><?= esc($nama) ?></p>
<?php if ($noTelp !== ''): ?>
    <a href="tel:<?= esc(preg_replace('/\D+/', '', $noTelp) ?: $noTelp) ?>"
       class="text-xs text-slate-500 mt-0.5 block hover:text-[#2E5CE6]">
        <?= esc($noTelp) ?>
    </a>
<?php endif; ?>
