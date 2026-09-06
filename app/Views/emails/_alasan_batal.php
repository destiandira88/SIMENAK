<?php
/**
 * Partial: Alasan pembatalan (tanpa mekanisme refund DP).
 *
 * @var string $alasanBatal
 */
$alasanBatal = (string) ($alasanBatal ?? '-');
?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:16px 0 0;">
<tr>
<td style="padding:0 0 8px;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:700;color:#051747;letter-spacing:0.06em;text-transform:uppercase;">
Alasan Pembatalan
</td>
</tr>
<tr>
<td style="padding:12px 0 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background-color:#FEF2F2;border:1px solid #FECACA;border-radius:8px;">
<tr>
<td style="padding:12px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#991B1B;line-height:1.5;">
<?= esc($alasanBatal) ?>
</td>
</tr>
</table>
<p style="margin:12px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#4A5568;line-height:1.55;">
Hubungi admin Z'Plack jika ada pertanyaan.
</p>
</td>
</tr>
</table>
