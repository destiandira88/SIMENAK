<?php
/**
 * Partial: Info status sederhana (finishing).
 *
 * @var string      $statusLabel
 * @var string|null $statusNote
 */
$statusLabel = (string) ($statusLabel ?? 'Finishing');
$statusNote  = $statusNote ?? null;
$muted       = '#64748B';
?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:16px 0 0;">
<tr>
<td style="padding:0 0 8px;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:700;color:#051747;letter-spacing:0.06em;text-transform:uppercase;">
Status Pesanan
</td>
</tr>
<tr>
<td style="padding:12px 0 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">Status</td>
<td align="right" style="padding:6px 0;">
<span style="display:inline-block;padding:4px 12px;font-family:Arial,Helvetica,sans-serif;font-size:11px;font-weight:600;border-radius:999px;background-color:#0E7490;color:#ffffff;">
<?= esc($statusLabel) ?>
</span>
</td>
</tr>
</table>
<?php if ($statusNote !== null && $statusNote !== ''): ?>
<p style="margin:12px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#4A5568;line-height:1.55;">
<?= esc($statusNote) ?>
</p>
<?php endif; ?>
</td>
</tr>
</table>
