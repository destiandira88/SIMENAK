<?php
/**
 * Partial: Ringkasan penyelesaian pesanan.
 *
 * @var string      $statusSelesaiLabel
 * @var string|null $catatanSelesai
 */
$statusSelesaiLabel = (string) ($statusSelesaiLabel ?? 'Selesai');
$catatanSelesai     = $catatanSelesai ?? null;
$muted              = '#64748B';
?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:16px 0 0;">
<tr>
<td style="padding:0 0 8px;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:700;color:#051747;letter-spacing:0.06em;text-transform:uppercase;">
Ringkasan Penyelesaian
</td>
</tr>
<tr>
<td style="padding:12px 0 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">Status</td>
<td align="right" style="padding:6px 0;">
<span style="display:inline-block;padding:4px 12px;font-family:Arial,Helvetica,sans-serif;font-size:11px;font-weight:600;border-radius:999px;background-color:#10B981;color:#ffffff;">
<?= esc($statusSelesaiLabel) ?>
</span>
</td>
</tr>
</table>
<?php if ($catatanSelesai !== null && $catatanSelesai !== ''): ?>
<p style="margin:12px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#4A5568;line-height:1.55;">
<?= esc($catatanSelesai) ?>
</p>
<?php endif; ?>
<p style="margin:12px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#4A5568;line-height:1.55;">
Terima kasih telah mempercayakan kebutuhan cetak kepada Z'Plack.
</p>
</td>
</tr>
</table>
