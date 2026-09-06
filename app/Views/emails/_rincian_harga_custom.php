<?php
/**
 * Partial: Rincian harga custom (konfirmasi Admin).
 *
 * @var string      $hargaLabel
 * @var string      $estimasiLabel
 * @var string|null $deadlineLabel
 * @var string|null $catatanAdmin
 */
$hargaLabel    = (string) ($hargaLabel ?? '-');
$estimasiLabel = (string) ($estimasiLabel ?? '-');
$deadlineLabel = $deadlineProduksiLabel ?? $deadlineLabel ?? null;
$catatanAdmin  = $catatanAdmin ?? null;
$muted         = '#64748B';
$border        = '#E2E8F0';
?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:16px 0 0;">
<tr>
<td style="padding:0 0 8px;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:700;color:#051747;letter-spacing:0.06em;text-transform:uppercase;">
Rincian Harga Custom
</td>
</tr>
<tr>
<td style="padding:12px 0 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">Harga</td>
<td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:700;color:#051747;">
<?= esc($hargaLabel) ?>
</td>
</tr>
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">Estimasi pengerjaan</td>
<td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1E293B;">
<?= esc($estimasiLabel) ?>
</td>
</tr>
<?php if ($deadlineLabel !== null && $deadlineLabel !== ''): ?>
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">Deadline produksi</td>
<td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1E293B;">
<?= esc($deadlineLabel) ?>
</td>
</tr>
<?php endif; ?>
<?php if ($catatanAdmin !== null && trim((string) $catatanAdmin) !== ''): ?>
<tr>
<td colspan="2" style="padding:12px 0 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background-color:#F8FAFC;border:1px solid <?= $border ?>;border-radius:8px;">
<tr>
<td style="padding:12px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#4A5568;line-height:1.5;">
<strong style="color:#051747;">Catatan Admin:</strong><br>
<?= esc((string) $catatanAdmin) ?>
</td>
</tr>
</table>
</td>
</tr>
<?php endif; ?>
</table>
</td>
</tr>
</table>
