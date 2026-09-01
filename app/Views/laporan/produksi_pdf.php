<?php

/**
 * @var array<string, mixed> $report
 * @var array<string, string> $filters
 */

$range         = $report['range'] ?? [];
$snapshot      = $report['snapshot'] ?? [];
$aktivitas     = $report['aktivitas'] ?? [];
$daftarPesanan = $report['daftarPesanan'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Desain SIMENAK</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #334155; margin: 0; padding: 20px; line-height: 1.4; }
        h1 { margin: 0 0 4px; font-size: 18px; color: #051747; }
        .meta { margin: 0 0 14px; font-size: 8px; color: #64748b; }
        h2 { margin: 16px 0 6px; font-size: 11px; color: #051747; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #e2e8f0; padding: 4px 5px; text-align: left; vertical-align: top; }
        th { background: #051747; color: #fff; font-size: 7px; text-transform: uppercase; }
        .summary-table td:first-child { width: 60%; font-weight: 600; }
        .mono { font-family: DejaVu Sans Mono, monospace; font-size: 7px; }
        .footer { margin-top: 16px; font-size: 8px; color: #94a3b8; text-align: center; }
        table.data-table { table-layout: fixed; }
        table.data-table th,
        table.data-table td {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
    </style>
</head>
<body>
    <h1>Laporan Desain SIMENAK</h1>
    <p class="meta">
        Periode aktivitas revisi: <?= esc((string) ($range['label'] ?? '-')) ?><br>
        Filter Kategori: <?= esc((string) ($filters['kategori'] ?? 'semua')) ?><br>
        Dicetak: <?= esc(date('d/m/Y H:i')) ?>
    </p>

    <h2>Antrian Desain Saat Ini (ringkasan hari ini)</h2>
    <table class="summary-table">
        <tbody>
            <tr><td>Antrian Desain Aktif</td><td class="text-right"><?= esc((string) ($snapshot['pesananAktif'] ?? 0)) ?></td></tr>
            <tr><td>Tahap Desain</td><td class="text-right"><?= esc((string) ($snapshot['tahapDesain'] ?? 0)) ?></td></tr>
            <tr><td>Tahap Cetak &amp; Finishing</td><td class="text-right"><?= esc((string) ($snapshot['tahapCetakFinish'] ?? 0)) ?></td></tr>
            <tr><td>Melewati Deadline</td><td class="text-right"><?= esc((string) ($snapshot['melewatiDeadline'] ?? 0)) ?></td></tr>
        </tbody>
    </table>

    <h2>Aktivitas Revisi dalam Periode</h2>
    <table class="summary-table">
        <tbody>
            <tr><td>Total Draft Diupload</td><td class="text-right"><?= esc((string) ($aktivitas['totalDraft'] ?? 0)) ?></td></tr>
            <tr><td>ACC</td><td class="text-right"><?= esc((string) ($aktivitas['acc'] ?? 0)) ?></td></tr>
            <tr><td>Ditolak</td><td class="text-right"><?= esc((string) ($aktivitas['ditolak'] ?? 0)) ?></td></tr>
            <tr><td>Diajukan Revisi</td><td class="text-right"><?= esc((string) ($aktivitas['diajukanRevisi'] ?? 0)) ?></td></tr>
            <tr><td>Menunggu Respon</td><td class="text-right"><?= esc((string) ($aktivitas['menungguRespon'] ?? $aktivitas['uploaded'] ?? 0)) ?></td></tr>
            <tr>
                <td>Rata-rata Penolakan/Pesanan</td>
                <td class="text-right"><?= esc(($aktivitas['rataRevisi'] ?? null) !== null ? number_format((float) $aktivitas['rataRevisi'], 1, ',', '.') : '-') ?></td>
            </tr>
        </tbody>
    </table>

    <h2>Daftar Pesanan (aktivitas revisi dalam periode)</h2>
    <table class="data-table">
        <colgroup>
            <col style="width: 4%;">
            <col style="width: 9%;">
            <col style="width: 10%;">
            <col style="width: 16%;">
            <col style="width: 8%;">
            <col style="width: 14%;">
            <col style="width: 6%;">
            <col style="width: 7%;">
            <col style="width: 8%;">
            <col style="width: 8%;">
            <col style="width: 6%;">
        </colgroup>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Order</th>
                <th>Pelanggan</th>
                <th>Produk</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>Kuota</th>
                <th>Penolakan</th>
                <th>Versi Terakhir</th>
                <th>Versi Dipilih</th>
                <th>Telat</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($daftarPesanan === []): ?>
                <tr><td colspan="11" class="text-center">Tidak ada pesanan dengan aktivitas revisi pada periode ini.</td></tr>
            <?php else: ?>
                <?php foreach ($daftarPesanan as $index => $row): ?>
                    <?php $deadline = trim((string) ($row['deadline'] ?? '')); ?>
                    <tr>
                        <td class="text-center"><?= esc((string) ($index + 1)) ?></td>
                        <td class="mono"><?= esc((string) ($row['kode_order'] ?? '-')) ?></td>
                        <td><?= esc((string) ($row['nama_pelanggan'] ?? '-')) ?></td>
                        <td><?= esc((int) ($row['is_custom'] ?? 0) === 1 ? 'Pesanan Custom' : (string) ($row['nama_produk'] ?? '-')) ?></td>
                        <td><?= esc($deadline !== '' ? $deadline : '-') ?></td>
                        <td><?= esc((string) ($row['status'] ?? '-')) ?></td>
                        <td class="text-center"><?= esc((int) ($row['sisa_kuota'] ?? 0) . '/' . (int) ($row['kuota_revisi'] ?? 0)) ?></td>
                        <td class="text-center"><?= esc((string) ($row['jumlah_revisi_periode'] ?? 0)) ?></td>
                        <td class="text-center"><?= esc((string) ($row['versi_terakhir'] ?? 0)) ?></td>
                        <td class="text-center"><?= esc((int) ($row['versi_dipilih'] ?? 0) > 0 ? (string) ($row['versi_dipilih'] ?? 0) : '-') ?></td>
                        <td class="text-center"><?= esc(!empty($row['is_telat']) ? 'Ya' : 'Tidak') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?= view('laporan/_partials/pdf_footer', [
        'exportedByName' => $exportedByName,
        'exportedByRole' => $exportedByRole,
        'exportedAt'     => $exportedAt,
    ]) ?>
</body>
</html>
