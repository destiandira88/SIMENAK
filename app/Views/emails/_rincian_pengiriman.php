<?php
/**
 * Partial: Rincian Pengiriman.
 *
 * @var string      $metodeLabel
 * @var string|null $noResi
 * @var string|null $ekspedisi
 * @var string|null $tglKirimLabel
 */
$metodeLabel   = (string) ($metodeLabel ?? '-');
$noResi        = $noResi ?? null;
$ekspedisi     = $ekspedisi ?? null;
$tglKirimLabel = $tglKirimLabel ?? null;
$muted         = '#64748B';
$border        = '#E2E8F0';
$accent        = '#2E5CE6';
?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:16px 0 0;">
<tr>
<td style="padding:0 0 8px;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:700;color:#051747;letter-spacing:0.06em;text-transform:uppercase;">
Rincian Pengiriman
</td>
</tr>
<tr>
<td style="padding:12px 0 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">Metode</td>
<td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1E293B;">
<?= esc($metodeLabel) ?>
</td>
</tr>
<?php if ($noResi !== null && $noResi !== ''): ?>
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">No. Resi</td>
<td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:700;color:<?= $accent ?>;">
<?= esc($noResi) ?>
</td>
</tr>
<?php endif; ?>
<?php if ($ekspedisi !== null && $ekspedisi !== ''): ?>
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">Ekspedisi</td>
<td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1E293B;">
<?= esc($ekspedisi) ?>
</td>
</tr>
<?php endif; ?>
<?php if ($tglKirimLabel !== null && $tglKirimLabel !== ''): ?>
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">Tgl Kirim</td>
<td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1E293B;">
<?= esc($tglKirimLabel) ?>
</td>
</tr>
<?php endif; ?>
</table>
</td>
</tr>
</table>
