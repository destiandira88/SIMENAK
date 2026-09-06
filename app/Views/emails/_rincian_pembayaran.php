<?php
/**
 * Partial: Rincian Pembayaran (pola visual sama dengan email DP Terverifikasi).
 *
 * @var string      $jenisPembayaran
 * @var string      $nominalLabel
 * @var string      $statusLabel
 * @var string      $statusTone       'success'|'danger'|'warning'
 * @var string|null $tglVerifikasiLabel
 * @var string|null $catatanTolak
 */
$jenisPembayaran    = (string) ($jenisPembayaran ?? '-');
$nominalLabel       = (string) ($nominalLabel ?? '-');
$statusLabel        = (string) ($statusLabel ?? '-');
$statusTone         = (string) ($statusTone ?? 'success');
$tglVerifikasiLabel = $tglVerifikasiLabel ?? null;
$catatanTolak       = trim((string) ($catatanTolak ?? ''));
$muted              = '#64748B';

// Badge solid + teks putih — sama seperti mockup DP Terverifikasi
$badgeBg = '#10B981';
$badgeFg = '#ffffff';
if ($statusTone === 'danger') {
    $badgeBg = '#EF4444';
    $badgeFg = '#ffffff';
} elseif ($statusTone === 'warning') {
    $badgeBg = '#F59E0B';
    $badgeFg = '#ffffff';
}
?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:16px 0 0;">
<tr>
<td style="padding:0 0 8px;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:700;color:#051747;letter-spacing:0.06em;text-transform:uppercase;">
Rincian Pembayaran
</td>
</tr>
<tr>
<td style="padding:12px 0 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">Jenis Pembayaran</td>
<td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1E293B;">
<?= esc($jenisPembayaran) ?>
</td>
</tr>
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">Nominal</td>
<td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:700;color:#051747;">
<?= esc($nominalLabel) ?>
</td>
</tr>
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">Status</td>
<td align="right" style="padding:6px 0;">
<span style="display:inline-block;padding:4px 12px;font-family:Arial,Helvetica,sans-serif;font-size:11px;font-weight:600;border-radius:999px;background-color:<?= $badgeBg ?>;color:<?= $badgeFg ?>;">
<?= esc($statusLabel) ?>
</span>
</td>
</tr>
<?php if ($tglVerifikasiLabel !== null && $tglVerifikasiLabel !== ''): ?>
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">Diverifikasi pada</td>
<td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1E293B;">
<?= esc($tglVerifikasiLabel) ?>
</td>
</tr>
<?php endif; ?>
<?php if ($catatanTolak !== ''): ?>
<tr>
<td colspan="2" style="padding:12px 0 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background-color:#FEF2F2;border:1px solid #FECACA;border-radius:8px;">
<tr>
<td style="padding:12px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#991B1B;line-height:1.5;">
<strong>Alasan penolakan:</strong><br>
<?= esc($catatanTolak) ?>
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
