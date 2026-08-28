<?php

/**
 * @var array<string, mixed> $report
 * @var array<string, string> $filters
 * @var string $exportedByName
 * @var string $exportedByRole
 * @var string $exportedAt
 */

$range        = $report['range'] ?? [];
$summary      = $report['summaryCards'] ?? [];
$rekapRows    = $report['rekapKategori']['rows'] ?? [];
$rekapTotals  = $report['rekapKategori']['totals'] ?? [];
$daftarPesanan = $report['daftarPesanan'] ?? [];
$laporanModel = model(\App\Models\LaporanModel::class);

$formatRupiah = static function (float|int $amount): string {
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
};
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Admin SIMENAK</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #334155;
            margin: 0;
            padding: 20px;
            line-height: 1.4;
        }

        h1 {
            margin: 0 0 4px;
            font-size: 18px;
            color: #051747;
        }

        .meta {
            margin: 0 0 14px;
            font-size: 8px;
            color: #64748b;
        }

        h2 {
            margin: 16px 0 6px;
            font-size: 11px;
            color: #051747;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        th,
        td {
            border: 1px solid #e2e8f0;
            padding: 4px 5px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #051747;
            color: #fff;
            font-size: 7px;
            text-transform: uppercase;
        }

        .summary-table td:first-child {
            width: 55%;
            font-weight: 600;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: 700;
            color: #051747;
        }

        .mono {
            font-family: DejaVu Sans Mono, monospace;
            font-size: 7px;
        }

        .footer {
            margin-top: 16px;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>

<body>
    <h1>Laporan Admin SIMENAK</h1>
    <p class="meta">
        Periode: <?= esc((string) ($range['label'] ?? '-')) ?><br>
        Filter Status: <?= esc((string) ($filters['status'] ?? 'semua')) ?> |
        Kategori: <?= esc((string) ($filters['kategori'] ?? 'semua')) ?> |
        Jenis Pelanggan: <?= esc((string) ($filters['jenis_pelanggan'] ?? 'semua')) ?> |
        Tipe Pesanan: <?= esc((string) ($filters['tipe_pesanan'] ?? 'semua')) ?><br>
        Dicetak: <?= esc(date('d/m/Y H:i')) ?>
    </p>

    <h2>Ringkasan</h2>
    <table class="summary-table">
        <tbody>
            <tr>
                <td>Total Pesanan</td>
                <td class="text-right"><?= esc((string) ($summary['totalPesanan'] ?? 0)) ?></td>
            </tr>
            <tr>
                <td>Pesanan Masuk</td>
                <td class="text-right"><?= esc((string) ($summary['pesananMasuk'] ?? 0)) ?></td>
            </tr>
            <tr>
                <td>Pesanan Selesai</td>
                <td class="text-right"><?= esc((string) ($summary['pesananSelesai'] ?? 0)) ?></td>
            </tr>
            <tr>
                <td>Pesanan Dibatalkan</td>
                <td class="text-right"><?= esc((string) ($summary['pesananDibatalkan'] ?? 0)) ?></td>
            </tr>
            <tr>
                <td>Potensi Pesanan Aktif</td>
                <td class="text-right"><?= esc((string) ($report['potensiAktif'] ?? 0)) ?></td>
            </tr>
        </tbody>
    </table>

    <h2>Rekap Per Kategori</h2>
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
            <?php foreach ($rekapRows as $row): ?>
                <tr>
                    <td><?= esc((string) ($row['label'] ?? '-')) ?></td>
                    <td class="text-center"><?= esc((string) ($row['jumlah'] ?? 0)) ?></td>
                    <td class="text-right"><?= esc($formatRupiah((float) ($row['pendapatan'] ?? 0))) ?></td>
                    <td class="text-right"><?= esc($formatRupiah((float) ($row['rata_rata'] ?? 0))) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td class="font-bold">TOTAL</td>
                <td class="text-center font-bold"><?= esc((string) ($rekapTotals['jumlah'] ?? 0)) ?></td>
                <td class="text-right font-bold"><?= esc($formatRupiah((float) ($rekapTotals['pendapatan'] ?? 0))) ?></td>
                <td class="text-right font-bold"><?= esc($formatRupiah((float) ($rekapTotals['rata_rata'] ?? 0))) ?></td>
            </tr>
        </tbody>
    </table>

    <h2>Daftar Pesanan</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Order</th>
                <th>Pelanggan</th>
                <th>Jenis</th>
                <th>Tipe</th>
                <th>Produk</th>
                <th>Kategori</th>
                <th>Tgl Pesan</th>
                <th>Deadline</th>
                <th class="text-right">Total</th>
                <th>Status</th>
                <th>Metode</th>
                <th>Tgl Selesai</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($daftarPesanan === []): ?>
                <tr>
                    <td colspan="13" class="text-center">Tidak ada pesanan pada periode ini.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($daftarPesanan as $index => $row): ?>
                    <?php
                    $createdAt  = (string) ($row['created_at'] ?? '');
                    $deadline   = trim((string) ($row['deadline'] ?? ''));
                    $namaProduk = trim((string) ($row['nama_produk'] ?? ''));
                    $tglSelesai = $row['tgl_selesai'] ?? null;
                    ?>
                    <tr>
                        <td class="text-center"><?= esc((string) ($index + 1)) ?></td>
                        <td class="mono"><?= esc((string) ($row['kode_order'] ?? '-')) ?></td>
                        <td><?= esc((string) ($row['nama_pelanggan'] ?? '-')) ?></td>
                        <td><?= esc($laporanModel->jenisPelangganLabel((string) ($row['jenis_pelanggan'] ?? ''))) ?></td>
                        <td><?= esc($laporanModel->tipePesananLabel((int) ($row['is_custom'] ?? 0))) ?></td>
                        <td><?= esc($namaProduk !== '' ? $namaProduk : '-') ?></td>
                        <td><?= esc($laporanModel->kategoriLabel((string) ($row['kategori'] ?? ''))) ?></td>
                        <td><?= esc($createdAt !== '' ? date('d/m/Y', strtotime($createdAt)) : '-') ?></td>
                        <td><?= esc($deadline !== '' ? $deadline : '-') ?></td>
                        <td class="text-right"><?= esc($formatRupiah((float) ($row['total_harga'] ?? 0))) ?></td>
                        <td><?= esc((string) ($row['status'] ?? '-')) ?></td>
                        <td><?= esc($laporanModel->metodePengirimanLabel((string) ($row['metode_pengiriman'] ?? ''))) ?></td>
                        <td><?= esc($tglSelesai ? date('d/m/Y', strtotime((string) $tglSelesai)) : '-') ?></td>
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