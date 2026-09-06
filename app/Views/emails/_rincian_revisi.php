<?php
/**
 * Partial: Rincian Revisi Desain.
 *
 * @var int         $versi
 * @var string|null $draftUrl       Absolute URL thumbnail draft or null
 * @var string|null $tglUploadLabel
 * @var int|null    $sisaKuota
 * @var int|null    $kuotaRevisi
 */
$versi          = (int) ($versi ?? 1);
$draftUrl       = $draftUrl ?? null;
$tglUploadLabel = $tglUploadLabel ?? null;
$sisaKuota      = isset($sisaKuota) ? (int) $sisaKuota : null;
$kuotaRevisi    = isset($kuotaRevisi) ? (int) $kuotaRevisi : null;
$muted          = '#64748B';
$border         = '#E2E8F0';
?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:16px 0 0;">
<tr>
<td style="padding:0 0 8px;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:700;color:#051747;letter-spacing:0.06em;text-transform:uppercase;">
Rincian Revisi Desain
</td>
</tr>

<tr>
<td style="padding:12px 0 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background-color:#F8FAFC;border:1px solid <?= $border ?>;border-radius:8px;">
<tr>
<td width="64" valign="middle" style="padding:12px;">
<?php if ($draftUrl !== null && $draftUrl !== ''): ?>
<img src="<?= esc($draftUrl) ?>" alt="Draft v<?= (int) $versi ?>" width="56" height="56" style="display:block;width:56px;height:56px;object-fit:cover;border-radius:6px;border:0;">
<?php else: ?>
<table role="presentation" width="56" height="56" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background-color:#E2E8F0;border-radius:6px;">
<tr>
<td align="center" valign="middle" width="56" height="56" style="width:56px;height:56px;font-family:Arial,Helvetica,sans-serif;font-size:10px;color:#94A3B8;">
Draft
</td>
</tr>
</table>
<?php endif; ?>
</td>
<td valign="middle" style="padding:12px 12px 12px 0;font-family:Arial,Helvetica,sans-serif;">
<p style="margin:0;font-size:14px;font-weight:700;color:#051747;line-height:1.35;">Draft v<?= (int) $versi ?></p>
<?php if ($tglUploadLabel !== null && $tglUploadLabel !== ''): ?>
<p style="margin:4px 0 0;font-size:12px;color:<?= $muted ?>;line-height:1.4;">Diunggah <?= esc($tglUploadLabel) ?></p>
<?php endif; ?>
</td>
</tr>
</table>
</td>
</tr>

<?php if ($sisaKuota !== null && $kuotaRevisi !== null): ?>
<tr>
<td style="padding:12px 0 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
<tr>
<td style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:<?= $muted ?>;">Sisa Kuota Revisi</td>
<td align="right" style="padding:6px 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:700;color:#051747;">
<?= (int) $sisaKuota ?> dari <?= (int) $kuotaRevisi ?>
</td>
</tr>
</table>
</td>
</tr>
<?php endif; ?>
</table>
