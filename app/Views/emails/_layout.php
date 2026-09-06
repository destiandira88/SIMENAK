<?php
/**
 * Layout email HTML terstruktur (table-based, inline CSS).
 * Referensi visual: template DP Terverifikasi.
 * Warna brand: primary #051747, accent #2E5CE6, bg #F0F2F8, border #E2E8F0.
 *
 * @var string      $pesanHtml
 * @var string|null $ctaUrl
 * @var string|null $ctaLabel
 */
$ctaUrl   = $ctaUrl ?? null;
$ctaLabel = $ctaLabel ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SIMENAK Z'Plack</title>
</head>
<body style="margin:0;padding:0;background-color:#F0F2F8;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F0F2F8;border-collapse:collapse;">
<tr>
<td align="center" style="padding:32px 12px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background-color:#ffffff;border:1px solid #E2E8F0;border-collapse:collapse;">

<!-- Header -->
<tr>
<td style="background-color:#051747;padding:20px 28px;">
<p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:16px;font-weight:700;color:#ffffff;letter-spacing:0.02em;">SIMENAK Z'Plack</p>
</td>
</tr>

<!-- Pesan singkat -->
<tr>
<td style="padding:28px 28px 20px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.65;color:#4A5568;">
<?= $pesanHtml ?? '' ?>
</td>
</tr>

<!-- Separator (pola DP Terverifikasi) -->
<tr>
<td style="padding:0 28px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
<tr>
<td style="border-top:1px solid #E2E8F0;font-size:0;line-height:0;height:1px;">&nbsp;</td>
</tr>
</table>
</td>
</tr>

<!-- Section content -->
<tr>
<td style="padding:8px 28px 8px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.65;color:#4A5568;">
<?= $this->renderSection('content') ?>
</td>
</tr>

<?php if ($ctaUrl !== null && $ctaUrl !== '' && $ctaLabel !== null && $ctaLabel !== ''): ?>
<tr>
<td align="center" style="padding:24px 28px 28px;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
<tr>
<td align="center" bgcolor="#051747" style="background-color:#051747;border-radius:8px;">
<a href="<?= esc($ctaUrl) ?>" style="display:inline-block;padding:12px 28px;font-family:Arial,Helvetica,sans-serif;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:8px;">
<?= esc($ctaLabel) ?>
</a>
</td>
</tr>
</table>
</td>
</tr>
<?php else: ?>
<tr>
<td style="padding:0 0 20px;font-size:0;line-height:0;">&nbsp;</td>
</tr>
<?php endif; ?>

<!-- Footer -->
<tr>
<td align="center" style="background-color:#F8FAFC;border-top:1px solid #E2E8F0;padding:16px 28px;">
<p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#94A3B8;line-height:1.5;">
SIMENAK · Sistem Informasi Pemesanan Z'Plack
</p>
</td>
</tr>

</table>
</td>
</tr>
</table>
</body>
</html>
