<?php
/**
 * Partial: Rincian Pesanan (selalu ada — pola sama dengan email DP Terverifikasi).
 *
 * @var string      $kodeOrder
 * @var string      $namaProduk
 * @var string|null $gambarUrl
 * @var string|null $produkSubteks
 * @var string|null $tglOrderLabel
 * @var string|null $deadlineLabel
 */
$kodeOrder     = (string) ($kodeOrder ?? '');
$namaProduk    = (string) ($namaProduk ?? '-');
$gambarUrl     = $gambarUrl ?? null;
$produkSubteks = $produkSubteks ?? null;
$tglOrderLabel = $tglOrderLabel ?? null;
$deadlineLabel = $deadlineLabel ?? null;
$accent        = '#2E5CE6';
$muted         = '#64748B';
$border        = '#E2E8F0';
?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:16px 0 0;">
<tr>
<td style="padding:0 0 8px;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:700;color:#051747;letter-spacing:0.06em;text-transform:uppercase;">
Rincian Pesanan
</td>
</tr>
<tr>
<td style="padding:12px 0 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">No. Pesanan</td>
<td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:700;color:<?= $accent ?>;">
<?= esc($kodeOrder) ?>
</td>
</tr>
<?php if ($tglOrderLabel !== null && $tglOrderLabel !== ''): ?>
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">Tanggal Pesan</td>
<td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1E293B;">
<?= esc($tglOrderLabel) ?>
</td>
</tr>
<?php endif; ?>
<?php if ($deadlineLabel !== null && $deadlineLabel !== ''): ?>
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">Deadline Pengerjaan</td>
<td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1E293B;">
<?= esc($deadlineLabel) ?>
</td>
</tr>
<?php endif; ?>
</table>
</td>
</tr>

<tr>
<td style="padding:12px 0 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background-color:#F8FAFC;border:1px solid <?= $border ?>;border-radius:8px;">
<tr>
<td width="64" valign="middle" style="padding:12px;">
<?php if ($gambarUrl !== null && $gambarUrl !== ''): ?>
<img src="<?= esc($gambarUrl) ?>" alt="<?= esc($namaProduk) ?>" width="56" height="56" style="display:block;width:56px;height:56px;object-fit:cover;border-radius:6px;border:0;">
<?php else: ?>
<table role="presentation" width="56" height="56" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background-color:#E2E8F0;border-radius:6px;">
<tr>
<td align="center" valign="middle" width="56" height="56" style="width:56px;height:56px;font-family:Arial,Helvetica,sans-serif;font-size:10px;color:#94A3B8;">
—
</td>
</tr>
</table>
<?php endif; ?>
</td>
<td valign="middle" style="padding:12px 12px 12px 0;font-family:Arial,Helvetica,sans-serif;">
<p style="margin:0;font-size:14px;font-weight:700;color:#051747;line-height:1.35;"><?= esc($namaProduk) ?></p>
<?php if ($produkSubteks !== null && $produkSubteks !== ''): ?>
<p style="margin:4px 0 0;font-size:12px;color:<?= $muted ?>;line-height:1.4;"><?= esc($produkSubteks) ?></p>
<?php endif; ?>
</td>
</tr>
</table>
</td>
</tr>

<!-- Separator ke section berikutnya (pola DP Terverifikasi) -->
<tr>
<td style="padding:20px 0 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
<tr>
<td style="border-top:1px solid <?= $border ?>;font-size:0;line-height:0;height:1px;">&nbsp;</td>
</tr>
</table>
</td>
</tr>
</table>
