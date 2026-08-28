<?php

/**
 * @var array<string, mixed> $report
 * @var array<string, string> $filters
 */

$range           = $report['range'] ?? [];
$summary         = $report['summaryCards'] ?? [];
$rekapRows       = $report['rekapJenis']['rows'] ?? [];
$rekapTotals     = $report['rekapJenis']['totals'] ?? [];
$daftarTransaksi = $report['daftarTransaksi'] ?? [];
$laporanModel    = model(\App\Models\LaporanModel::class);

$formatRupiah = static function (float|int $amount): string {
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
};

$formatTanggal = static function (?string $value): string {
    $value = trim((string) $value);

    return $value !== '' ? date('d/m/Y H:i', strtotime($value)) : '-';
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi SIMENAK</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #334155;
            margin: 0;
            padding: 24px;
            line-height: 1.45;
        }
        h1 {
            margin: 0 0 4px;
            font-size: 18px;
            color: #051747;
        }
        .meta { margin: 0 0 16px; font-size: 9px; color: #64748b; }
        h2 {
            margin: 18px 0 8px;
            font-size: 12px;
            color: #051747;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #051747;
            color: #fff;
            font-size: 8px;
            text-transform: uppercase;
        }
        .summary-table td:first-child { width: 55%; font-weight: 600; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; color: #051747; }
        .footer {
            margin-top: 20px;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
        }
        .mono { font-family: DejaVu Sans Mono, monospace; font-size: 8px; }
    </style>
</head>
<body>
    <h1>Laporan Transaksi SIMENAK</h1>
    <p class="meta">
        Periode: <?= esc((string) ($range['label'] ?? '-')) ?><br>
        Filter Jenis: <?= esc((string) ($filters['jenis'] ?? 'semua')) ?> |
        Filter Status: <?= esc((string) ($filters['status'] ?? 'semua')) ?><br>
        Dicetak: <?= esc(date('d/m/Y H:i')) ?>
    </p>

    <h2>Ringkasan</h2>
    <table class="summary-table">
        <tbody>
            <tr>
                <td>Total Pemasukan (terverifikasi)</td>
                <td class="text-right font-bold"><?= esc($formatRupiah((float) ($summary['totalPemasukan'] ?? 0))) ?></td>
            </tr>
            <tr>
                <td>Pemasukan DP</td>
                <td class="text-right"><?= esc($formatRupiah((float) ($summary['pemasukanDp'] ?? 0))) ?></td>
            </tr>
            <tr>
                <td>Pemasukan Pelunasan</td>
                <td class="text-right"><?= esc($formatRupiah((float) ($summary['pemasukanPelunasan'] ?? 0))) ?></td>
            </tr>
            <tr>
                <td>Menunggu Verifikasi (saat ini)</td>
                <td class="text-right"><?= esc((string) ($summary['menungguVerifikasi'] ?? 0)) ?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;- DP menunggu</td>
                <td class="text-right"><?= esc((string) ($summary['menungguDp'] ?? 0)) ?></td>
            </tr>
            <tr>
                <td>&nbsp;&nbsp;- Pelunasan menunggu</td>
                <td class="text-right"><?= esc((string) ($summary['menungguPelunasan'] ?? 0)) ?></td>
            </tr>
        </tbody>
    </table>

    <h2>Rekap Per Jenis Pembayaran (terverifikasi dalam periode)</h2>
    <table>
        <thead>
            <tr>
                <th>Jenis</th>
                <th class="text-center">Jumlah Transaksi</th>
                <th class="text-right">Total Nominal</th>
                <th class="text-right">Rata-rata/Transaksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rekapRows === []): ?>
                <tr>
                    <td colspan="4" class="text-center">Tidak ada pemasukan terverifikasi pada periode ini.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($rekapRows as $row): ?>
                    <tr>
                        <td><?= esc((string) ($row['label'] ?? '-')) ?></td>
                        <td class="text-center"><?= esc((string) ($row['jumlah'] ?? 0)) ?></td>
                        <td class="text-right"><?= esc($formatRupiah((float) ($row['nominal'] ?? 0))) ?></td>
                        <td class="text-right"><?= esc($formatRupiah((float) ($row['rata_rata'] ?? 0))) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td class="font-bold">TOTAL</td>
                    <td class="text-center font-bold"><?= esc((string) ($rekapTotals['jumlah'] ?? 0)) ?></td>
                    <td class="text-right font-bold"><?= esc($formatRupiah((float) ($rekapTotals['nominal'] ?? 0))) ?></td>
                    <td class="text-right font-bold"><?= esc($formatRupiah((float) ($rekapTotals['rata_rata'] ?? 0))) ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Daftar Transaksi</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Bayar</th>
                <th>Kode Order</th>
                <th>Pelanggan</th>
                <th>Jenis</th>
                <th class="text-right">Nominal</th>
                <th>Status</th>
                <th>Tgl Upload</th>
                <th>Tgl Verifikasi</th>
                <th>Diverifikasi Oleh</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($daftarTransaksi === []): ?>
                <tr>
                    <td colspan="10" class="text-center">Tidak ada transaksi yang cocok dengan filter.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($daftarTransaksi as $index => $row): ?>
                    <?php
                    $emailVerifikator = trim((string) ($row['email_verifikator'] ?? ''));
                    ?>
                    <tr>
                        <td class="text-center"><?= esc((string) ($index + 1)) ?></td>
                        <td class="mono"><?= esc((string) ($row['kode_payment'] ?? '-')) ?></td>
                        <td class="mono"><?= esc((string) ($row['kode_order'] ?? '-')) ?></td>
                        <td><?= esc((string) ($row['nama_pelanggan'] ?? '-')) ?></td>
                        <td><?= esc($laporanModel->paymentJenisLabel((string) ($row['jenis'] ?? ''))) ?></td>
                        <td class="text-right"><?= esc($formatRupiah((float) ($row['nominal'] ?? 0))) ?></td>
                        <td><?= esc((string) ($row['status'] ?? '-')) ?></td>
                        <td><?= esc($formatTanggal((string) ($row['tgl_upload'] ?? ''))) ?></td>
                        <td><?= esc($formatTanggal((string) ($row['tgl_verifikasi'] ?? ''))) ?></td>
                        <td><?= esc($emailVerifikator !== '' ? $emailVerifikator : '-') ?></td>
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
