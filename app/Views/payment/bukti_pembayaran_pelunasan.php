<?php
/**
 * @var string               $title
 * @var array<string, mixed> $order
 * @var array<string, mixed> $payment
 * @var bool                 $isPerusahaan
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php helper('notification'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        (function() {
            try {
                if (localStorage.getItem('simenak-dashboard-theme') === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            } catch (e) {}
        })();
    </script>
    <title><?= esc($title ?? 'Bukti Pembayaran Pelunasan') ?>-<?= esc((string) ($order['kode_order'] ?? '')) ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/favicon1.png') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #051747;
            --blue-accent: #2E5CE6;
            --border: #E2E8F0;
            --bg-page: #F0F2F8;
            --green: #065F46;
            --green-bg: #D1FAE5;
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
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background-color .15s ease, color .15s ease, border-color .15s ease;
            flex-shrink: 0;
        }

        .btn-primary { background: var(--navy); color: #fff; }
        .btn-primary:hover { background: var(--blue-accent); }
        .btn-outline {
            background: #fff;
            color: var(--navy);
            border: 2px solid var(--navy);
        }
        .btn-outline:hover {
            background: var(--navy);
            color: #fff;
        }

        .btn svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

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
        .invoice-num { font-size: 22px; font-weight: 800; margin-top: 6px; text-align: right; font-family: ui-monospace, monospace; }

        .invoice-body { padding: 32px 40px; }

        .note-box {
            background: var(--green-bg);
            border: 1px solid #6EE7B7;
            border-radius: 14px;
            padding: 16px 18px;
            font-size: 13px;
            color: var(--green);
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
        .total-row { display: flex; justify-content: space-between; width: 300px; font-size: 13px; }
        .total-row.grand { font-size: 20px; font-weight: 800; color: var(--green); border-top: 2px solid var(--green); padding-top: 10px; margin-top: 4px; }

        .verif-card {
            background: #F8FAFC;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px 22px;
            margin-top: 24px;
        }

        .verif-card p { margin: 0 0 8px; font-size: 13px; line-height: 1.7; color: #475569; }
        .verif-card p:last-child { margin-bottom: 0; }
        .verif-card strong { color: var(--navy); }

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

        .badge-lunas {
            background: var(--green-bg);
            color: var(--green);
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

        html[data-theme="dark"] {
            color-scheme: dark;
            --bg-page: #0c111c;
            --border: rgba(255, 255, 255, 0.08);
            --surface: #151f2e;
            --surface-muted: #0f1624;
            --text-body: #98a2b3;
            --text-strong: #e4e7ec;
            --green: #6ee7b7;
            --green-bg: rgba(16, 185, 129, 0.12);
        }

        html[data-theme="dark"] body {
            background: var(--bg-page);
            color: var(--text-body);
        }

        html[data-theme="dark"] .btn-outline {
            background: transparent;
            color: var(--text-strong);
            border-color: rgba(255, 255, 255, 0.18);
        }

        html[data-theme="dark"] .btn-outline:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        html[data-theme="dark"] .invoice-wrap {
            background: var(--surface);
            border-color: var(--border);
            box-shadow: none;
        }

        html[data-theme="dark"] .note-box {
            background: var(--green-bg);
            border-color: rgba(16, 185, 129, 0.35);
            color: var(--green);
        }

        html[data-theme="dark"] .note-box strong {
            color: #a7f3d0;
        }

        html[data-theme="dark"] .meta-label,
        html[data-theme="dark"] .invoice-table th {
            color: #667085;
        }

        html[data-theme="dark"] .meta-value,
        html[data-theme="dark"] .invoice-table td {
            color: var(--text-strong);
        }

        html[data-theme="dark"] .invoice-table th {
            border-bottom-color: var(--border);
        }

        html[data-theme="dark"] .invoice-table td {
            border-bottom-color: rgba(255, 255, 255, 0.06);
        }

        html[data-theme="dark"] .invoice-meta {
            border-bottom-color: var(--border);
        }

        html[data-theme="dark"] .total-row.grand {
            color: var(--green);
            border-top-color: rgba(16, 185, 129, 0.35);
        }

        html[data-theme="dark"] .verif-card {
            background: var(--surface-muted);
            border-color: var(--border);
        }

        html[data-theme="dark"] .verif-card p {
            color: var(--text-body);
        }

        html[data-theme="dark"] .verif-card strong {
            color: var(--text-strong);
        }

        html[data-theme="dark"] .invoice-footer {
            background: var(--surface-muted);
            color: var(--text-body);
        }

        html[data-theme="dark"] .badge-lunas {
            background: var(--green-bg);
            color: var(--green);
        }

        html[data-theme="dark"] .meta-value span[style] {
            color: #98a2b3 !important;
        }

        html[data-theme="dark"] .verif-card p[style] {
            color: #667085 !important;
        }
    </style>
</head>
<body>
    <?php
    $kodeOrder      = (string) ($order['kode_order'] ?? '');
    $kodePayment    = (string) ($payment['kode_payment'] ?? '');
    $namaProduk     = (string) ($order['nama_produk'] ?? 'Jasa Cetak');
    $jumlah         = (int) ($order['jumlah_order'] ?? 1);
    $satuan         = (string) ($order['satuan'] ?? 'pcs');
    $namaPelanggan  = (string) ($order['nama'] ?? '');
    $namaPerusahaan = trim((string) ($order['nama_perusahaan'] ?? ''));
    $nominal        = (int) round((float) ($payment['nominal'] ?? 0));
    $tglUpload      = (string) ($payment['tgl_upload'] ?? '');
    $tglVerifikasi  = (string) ($payment['tgl_verifikasi'] ?? '');
    $namaVerifikator = trim((string) ($payment['nama_verifikator'] ?? 'Bagian Keuangan'));
    $detailUrl      = site_url('order/detail/' . $kodeOrder);
    ?>

    <div class="no-print">
        <a href="<?= esc($detailUrl) ?>" class="btn btn-outline inline-flex items-center gap-2">
            <?= view('partials/order_detail_svg_icon', ['icon' => 'arrow-left', 'class' => 'h-4 w-4 shrink-0']) ?>
            Kembali ke Detail Pesanan
        </a>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Cetak / Simpan PDF
        </button>
    </div>

    <div class="invoice-wrap">
        <div class="invoice-header">
            <div>
                <div class="invoice-logo">Z'PLACK<span>SIMENAK</span></div>
            </div>
            <div>
                <div class="invoice-title">Bukti Pembayaran</div>
                <div class="invoice-num"><?= esc($kodePayment) ?></div>
            </div>
        </div>

        <div class="invoice-body">
            <div class="note-box">
                Dokumen ini merupakan bukti resmi bahwa pembayaran pelunasan pesanan
                <strong><?= esc($kodeOrder) ?></strong> telah diterima dan diverifikasi oleh Z'Plack.
            </div>

            <div class="invoice-meta">
                <div>
                    <div class="meta-label">Dibayar Oleh</div>
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
                    <div class="meta-label">Informasi Pembayaran</div>
                    <div class="meta-value">
                        Kode Pesanan: <?= esc($kodeOrder) ?><br>
                        Jenis: Pelunasan
                        <?php if ($tglUpload !== ''): ?>
                            <br>Tgl Transfer: <?= esc(date('d M Y H:i', strtotime($tglUpload))) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Deskripsi</th>
                        <th>Qty</th>
                        <th>Satuan</th>
                        <th style="text-align:right;">Nominal Dibayar</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Pelunasan-<?= esc($namaProduk) ?></td>
                        <td><?= esc((string) $jumlah) ?></td>
                        <td><?= esc($satuan) ?></td>
                        <td class="num">Rp <?= esc(number_format($nominal, 0, ',', '.')) ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="invoice-total">
                <div class="total-row grand">
                    <span>Total Diterima</span>
                    <span>Rp <?= esc(number_format($nominal, 0, ',', '.')) ?></span>
                </div>
            </div>

            <div class="verif-card">
                <p><strong>Verifikasi Keuangan</strong></p>
                <p>
                    Status: <strong>LUNAS / Terverifikasi</strong><br>
                    <?php if ($tglVerifikasi !== ''): ?>
                        Diverifikasi: <strong><?= esc(date('d M Y H:i', strtotime($tglVerifikasi))) ?> WIB</strong><br>
                    <?php endif; ?>
                    Oleh: <strong><?= esc($namaVerifikator) ?></strong>
                </p>
                <p style="font-size:12px;color:#94A3B8;margin-top:12px;">
                    Dokumen ini diterbitkan secara elektronik oleh SIMENAK Z'Plack dan sah tanpa tanda tangan basah.
                </p>
            </div>
        </div>

        <div class="invoice-footer">
            <span>Simpan dokumen ini sebagai bukti pembayaran resmi.</span>
            <span class="badge-lunas">Lunas</span>
        </div>
    </div>
</body>
</html>
