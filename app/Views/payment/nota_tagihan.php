<?php
/**
 * @var string               $title
 * @var array<string, mixed> $order
 * @var string               $kodeNota
 * @var int                  $nominalPelunasan
 * @var int                  $totalHarga
 * @var bool                 $isPerusahaan
 * @var bool                 $sebelumKirim
 * @var string               $batasBayar
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php helper('notification'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Nota Tagihan') ?>-<?= esc((string) ($order['kode_order'] ?? '')) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #051747;
            --blue-accent: #2E5CE6;
            --border: #E2E8F0;
            --bg-page: #F0F2F8;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-page);
            color: #334155;
        }

        .no-print {
            max-width: 860px;
            margin: 0 auto;
            padding: 20px 16px 0;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .btn-primary { background: var(--navy); color: #fff; }
        .btn-primary:hover { background: var(--blue-accent); }
        .btn-outline { background: #fff; color: var(--navy); border: 1.5px solid var(--navy); }

        .invoice-wrap {
            max-width: 860px;
            margin: 16px auto 40px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(15, 23, 43, .08);
        }

        .invoice-header {
            background: var(--navy);
            color: #fff;
            padding: 28px 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
        }

        .invoice-logo { font-size: 22px; font-weight: 800; letter-spacing: -.02em; }
        .invoice-logo span { color: #6b9fff; display: block; font-size: 11px; letter-spacing: .14em; text-transform: uppercase; margin-top: 4px; font-weight: 700; }
        .invoice-title { font-size: 12px; font-weight: 700; opacity: .75; text-transform: uppercase; letter-spacing: .12em; text-align: right; }
        .invoice-num { font-size: 24px; font-weight: 800; margin-top: 6px; text-align: right; }

        .invoice-body { padding: 32px 40px; }

        .note-box {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 14px;
            padding: 16px 18px;
            font-size: 13px;
            color: #1E3A8A;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .invoice-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border);
        }

        .meta-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #94A3B8; margin-bottom: 6px; }
        .meta-value { font-size: 14px; font-weight: 600; color: var(--navy); line-height: 1.5; }

        .invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .invoice-table th {
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
            color: #94A3B8; padding: 8px 0; border-bottom: 2px solid var(--border); text-align: left;
        }
        .invoice-table td { padding: 14px 0; border-bottom: 1px solid #F1F5F9; font-size: 13px; color: var(--navy); }
        .invoice-table td.num { text-align: right; white-space: nowrap; }

        .invoice-total { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
        .total-row { display: flex; justify-content: space-between; width: 280px; font-size: 13px; }
        .total-row.grand { font-size: 18px; font-weight: 800; color: var(--navy); border-top: 2px solid var(--navy); padding-top: 10px; margin-top: 4px; }

        .rekening-card {
            background: var(--navy);
            color: #fff;
            border-radius: 16px;
            padding: 20px 22px;
            margin-top: 24px;
        }

        .rekening-card p { margin: 0; font-size: 13px; line-height: 1.7; }
        .rekening-card strong { font-size: 15px; }

        .invoice-footer {
            background: #F8FAFC;
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            font-size: 12px;
            color: #64748B;
        }

        .badge-menunggu {
            background: #FFEDD5;
            color: #9A3412;
            font-size: 11px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .invoice-wrap { box-shadow: none; border: none; margin: 0; max-width: 100%; border-radius: 0; }
        }

        @media (max-width: 640px) {
            .invoice-header, .invoice-body, .invoice-footer { padding-left: 20px; padding-right: 20px; }
            .invoice-meta { grid-template-columns: 1fr; }
            .invoice-header { flex-direction: column; }
            .invoice-title, .invoice-num { text-align: left; }
        }
    </style>
</head>
<body>
    <?php
    $kodeOrder   = (string) ($order['kode_order'] ?? '');
    $namaProduk  = (string) ($order['nama_produk'] ?? 'Jasa Cetak');
    $jumlah      = (int) ($order['jumlah_order'] ?? 1);
    $satuan      = (string) ($order['satuan'] ?? 'pcs');
    $namaPelanggan = (string) ($order['nama'] ?? '');
    $namaPerusahaan = trim((string) ($order['nama_perusahaan'] ?? ''));
    $detailUrl   = site_url('order/detail/' . $kodeOrder);
    ?>

    <div class="no-print">
        <a href="<?= esc($detailUrl) ?>" class="btn btn-outline inline-flex items-center gap-2">
            <?= view('partials/order_detail_svg_icon', ['icon' => 'arrow-left', 'class' => 'h-4 w-4 shrink-0']) ?>
            Kembali ke Detail Pesanan
        </a>
        <button type="button" class="btn btn-primary" onclick="window.print()">🖨 Cetak / Simpan PDF</button>
    </div>

    <div class="invoice-wrap">
        <div class="invoice-header">
            <div>
                <div class="invoice-logo">Z'PLACK<span>SIMENAK</span></div>
            </div>
            <div>
                <div class="invoice-title">Nota Tagihan</div>
                <div class="invoice-num"><?= esc($kodeNota) ?></div>
            </div>
        </div>

        <div class="invoice-body">
            <div class="note-box">
                <?php if ($sebelumKirim): ?>
                    Nota tagihan ini merupakan permohonan transfer pelunasan sebelum pengiriman/pengambilan pesanan.
                    Mohon melakukan transfer ke rekening Z'Plack dalam <strong>3 hari kerja</strong>.
                <?php else: ?>
                    Nota tagihan ini merupakan permohonan transfer untuk pelunasan pesanan
                    <?= $isPerusahaan ? ' atas nama perusahaan' : '' ?>.
                    Mohon melakukan transfer ke rekening Z'Plack dalam <strong>3 hari kerja</strong>.
                <?php endif; ?>
            </div>

            <div class="invoice-meta">
                <div>
                    <div class="meta-label">Tagihan Kepada</div>
                    <div class="meta-value">
                        <?php if ($isPerusahaan && $namaPerusahaan !== ''): ?>
                            <?= esc($namaPerusahaan) ?><br>
                            <span style="font-weight:500;color:#64748B;">PIC: <?= esc($namaPelanggan) ?></span>
                        <?php else: ?>
                            <?= esc($namaPelanggan) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <div class="meta-label">Informasi Tagihan</div>
                    <div class="meta-value">
                        Tgl Tagihan: <?= esc(date('d M Y')) ?><br>
                        Kode Pesanan: <?= esc($kodeOrder) ?>
                    </div>
                </div>
            </div>

            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Deskripsi</th>
                        <th>Qty</th>
                        <th>Satuan</th>
                        <th style="text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= esc($namaProduk) ?></td>
                        <td><?= esc((string) $jumlah) ?></td>
                        <td><?= esc($satuan) ?></td>
                        <td class="num">Rp <?= esc(number_format($totalHarga, 0, ',', '.')) ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="invoice-total">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span>Rp <?= esc(number_format($totalHarga, 0, ',', '.')) ?></span>
                </div>
                <?php if ((int) ($order['require_dp'] ?? 0) === 1): ?>
                    <div class="total-row">
                        <span>DP Terverifikasi (50%)</span>
                        <span>− Rp <?= esc(number_format(nominalDpFromTotal($totalHarga), 0, ',', '.')) ?></span>
                    </div>
                <?php endif; ?>
                <div class="total-row grand">
                    <span>Total Tagihan</span>
                    <span>Rp <?= esc(number_format($nominalPelunasan, 0, ',', '.')) ?></span>
                </div>
            </div>

            <div class="rekening-card">
                <p><strong>Transfer ke:</strong></p>
                <p>
                    Bank: BCA<br>
                    No. Rekening: <strong>1234567890</strong><br>
                    Atas Nama: <strong>Z'Plack Percetakan</strong><br>
                    Batas Pembayaran: <strong><?= esc($batasBayar) ?></strong>
                </p>
            </div>
        </div>

        <div class="invoice-footer">
            <span>Simpan nota ini sebagai bukti permohonan transfer.</span>
            <span class="badge-menunggu">Menunggu Transfer</span>
        </div>
    </div>
</body>
</html>
