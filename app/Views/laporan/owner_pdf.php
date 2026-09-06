<?php

/**
 * @var array<string, mixed> $report
 * @var array<string, string> $filters
 */

$period = $report['period'] ?? [];

$formatRupiah = static function (float|int $amount): string {
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
};

$formatGrowth = static function (?float $value): string {
    if ($value === null) {
        return '-';
    }

    return ($value >= 0 ? '+' : '') . number_format($value, 1, ',', '.') . '%';
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Owner SIMENAK</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #334155; margin: 0; padding: 24px; line-height: 1.45; }
        h1 { margin: 0 0 4px; font-size: 18px; color: #051747; }
        .meta { margin: 0 0 16px; font-size: 9px; color: #64748b; }
        h2 { margin: 18px 0 8px; font-size: 12px; color: #051747; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #e2e8f0; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #051747; color: #fff; font-size: 8px; text-transform: uppercase; }
        .summary-table td:first-child { width: 55%; font-weight: 600; }
        .font-bold { font-weight: 700; color: #051747; }
        .footer { margin-top: 20px; font-size: 8px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <h1>Laporan Owner SIMENAK</h1>
    <p class="meta">
        Periode: <?= esc((string) ($period['label'] ?? '-')) ?><br>
        Dicetak: <?= esc(date('d/m/Y H:i')) ?>
    </p>

    <h2>Ringkasan</h2>
    <table class="summary-table">
        <tbody>
            <tr><td>Total Pesanan</td><td><?= esc((string) ($report['totalPesanan'] ?? 0)) ?></td></tr>
            <tr><td>Pesanan Selesai</td><td><?= esc((string) ($report['pesananSelesai'] ?? 0)) ?></td></tr>
            <tr><td>Total Pendapatan</td><td class="font-bold"><?= esc($formatRupiah((float) ($report['totalPendapatan'] ?? 0))) ?></td></tr>
            <tr><td>Pertumbuhan Pesanan (%)</td><td><?= esc($formatGrowth(isset($report['growthOrders']) ? (float) $report['growthOrders'] : null)) ?></td></tr>
            <tr><td>Rata-rata Selesai (hari)</td><td><?= esc($report['avgSelesaiHari'] !== null ? (string) $report['avgSelesaiHari'] : '-') ?></td></tr>
            <tr><td>Pesanan Dibatalkan</td><td><?= esc((string) ($report['pesananDibatalkan'] ?? 0)) ?></td></tr>
        </tbody>
    </table>

    <h2>Segmen Pelanggan</h2>
    <table>
        <thead>
            <tr>
                <th>Jenis</th>
                <th class="text-center">Jumlah Pesanan</th>
                <th class="text-center">% Pesanan</th>
                <th class="text-right">Pendapatan</th>
                <th class="text-right">Rata-rata/Pesanan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report['segmenPelanggan']['slices'] ?? [] as $row): ?>
                <tr>
                    <td><?= esc((string) ($row['label'] ?? '-')) ?></td>
                    <td class="text-center"><?= esc((string) ($row['count'] ?? 0)) ?></td>
                    <td class="text-center"><?= esc((string) ($row['pct_count'] ?? 0)) ?></td>
                    <td class="text-right"><?= esc($formatRupiah((float) ($row['revenue'] ?? 0))) ?></td>
                    <td class="text-right"><?= esc($formatRupiah((float) ($row['rata_rata'] ?? 0))) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Segmen Pemesanan</h2>
    <table>
        <thead>
            <tr>
                <th>Jenis</th>
                <th class="text-center">Jumlah Pesanan</th>
                <th class="text-center">% Pesanan</th>
                <th class="text-right">Pendapatan</th>
                <th class="text-right">Rata-rata/Pesanan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report['segmenPemesanan']['slices'] ?? [] as $row): ?>
                <tr>
                    <td><?= esc((string) ($row['label'] ?? '-')) ?></td>
                    <td class="text-center"><?= esc((string) ($row['count'] ?? 0)) ?></td>
                    <td class="text-center"><?= esc((string) ($row['pct_count'] ?? 0)) ?></td>
                    <td class="text-right"><?= esc($formatRupiah((float) ($row['revenue'] ?? 0))) ?></td>
                    <td class="text-right"><?= esc($formatRupiah((float) ($row['rata_rata'] ?? 0))) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Rekap Per Kategori <span style="font-weight:400;font-size:9px;color:#64748b;">(dihitung dari pesanan selesai)</span></h2>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th class="text-center">Jumlah Pesanan</th>
                <th class="text-right">Total Pendapatan</th>
                <th class="text-right">Rata-rata/Pesanan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report['rekapKategori'] ?? [] as $row): ?>
                <tr>
                    <td><?= esc((string) ($row['label'] ?? '-')) ?></td>
                    <td class="text-center"><?= esc((string) ($row['jumlah'] ?? 0)) ?></td>
                    <td class="text-right"><?= esc($formatRupiah((float) ($row['pendapatan'] ?? 0))) ?></td>
                    <td class="text-right"><?= esc($formatRupiah((float) ($row['rata_rata'] ?? 0))) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Top Produk Terlaris</h2>
    <table>
        <thead>
            <tr>
                <th>Nama Produk</th>
                <th class="text-center">Jumlah Pesanan</th>
                <th class="text-right">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report['topProduk'] ?? [] as $row): ?>
                <tr>
                    <td><?= esc((string) ($row['nama_produk'] ?? '-')) ?></td>
                    <td class="text-center"><?= esc((string) ($row['jumlah'] ?? 0)) ?></td>
                    <td class="text-right"><?= esc($formatRupiah((float) ($row['pendapatan'] ?? 0))) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?= view('laporan/_partials/pdf_footer', [
        'exportedByName' => $exportedByName,
        'exportedByRole' => $exportedByRole,
        'exportedAt'     => $exportedAt,
    ]) ?>
</body>
</html>
