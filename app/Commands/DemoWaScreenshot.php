<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Kirim 3 contoh notifikasi WA realistis (untuk screenshot dokumentasi).
 *
 * Usage: php spark demo-wa-screenshot [nomor_opsional]
 * Jika nomor diisi, ketiga contoh dikirim ke nomor itu.
 * Jika kosong: pelanggan dari DB (no_telp), keuangan/owner dari .env fonnte.notify.*
 */
class DemoWaScreenshot extends BaseCommand
{
    protected $group       = 'SIMENAK';
    protected $name        = 'demo-wa-screenshot';
    protected $description = 'Kirim contoh notifikasi WA pelanggan/keuangan/owner untuk screenshot';
    protected $usage       = 'demo-wa-screenshot [nomor]';

    public function run(array $params): void
    {
        helper('notification');

        if (! isFonnteConfigured()) {
            CLI::error('fonnte.token belum diisi di .env');

            return;
        }

        $override = trim((string) ($params[0] ?? ''));
        $db       = \Config\Database::connect();

        $nomorPelanggan = $override !== ''
            ? normalizeNomorWa($override)
            : $this->resolvePelangganWa($db);
        $nomorKeuangan = $override !== ''
            ? normalizeNomorWa($override)
            : normalizeNomorWa((string) env('fonnte.notify.keuangan', ''));
        $nomorOwner = $override !== ''
            ? normalizeNomorWa($override)
            : normalizeNomorWa((string) env('fonnte.notify.owner', ''));

        $kodeOrder = 'ORD-' . date('Ymd') . '-0001';
        $base      = rtrim(site_url('/'), '/');

        $pesanPelanggan = buildNotifWaText(
            "DP Terverifikasi-{$kodeOrder}",
            'Pembayaran DP Anda telah diverifikasi. Pesanan masuk tahap desain.',
            $base . '/order/detail/' . $kodeOrder
        );

        $pesanKeuangan = buildNotifWaText(
            "Bukti DP Baru-{$kodeOrder}",
            'Pelanggan QA Demo mengunggah bukti DP Rp 2.500.000. Segera verifikasi.',
            $base . '/verifikasi-dp'
        );

        $pesanOwner = buildNotifWaText(
            "Eskalasi Verifikasi DP-{$kodeOrder}",
            'Bukti DP belum diverifikasi lebih dari 6 jam. Mohon pantau tim keuangan.',
            $base . '/riwayat-aktivitas'
        );

        $jobs = [
            ['Pelanggan', $nomorPelanggan, $pesanPelanggan],
            ['Keuangan',  $nomorKeuangan,  $pesanKeuangan],
            ['Owner',     $nomorOwner,     $pesanOwner],
        ];

        foreach ($jobs as [$label, $nomor, $pesan]) {
            if ($nomor === null) {
                CLI::error("[{$label}] nomor tidak tersedia — lewati");
                continue;
            }

            CLI::write("Mengirim ke {$label} ({$nomor}) ...", 'yellow');
            $ok = sendNotifWa($nomor, $pesan);
            if ($ok) {
                CLI::write("[{$label}] TERKIRIM", 'green');
            } else {
                CLI::error("[{$label}] GAGAL — cek log / device Fonnte");
            }
            // jeda singkat agar tidak dianggap flood
            usleep(800000);
        }

        CLI::write('Selesai. Cek WhatsApp untuk screenshot.', 'cyan');
    }

    private function resolvePelangganWa($db): ?string
    {
        $row = $db->table('users u')
            ->select('p.no_telp')
            ->join('pelanggan p', 'p.id_user = u.id_user')
            ->where('u.role', 'pelanggan')
            ->where('u.is_active', 1)
            ->where('p.no_telp IS NOT NULL', null, false)
            ->where('p.no_telp !=', '')
            ->orderBy('u.id_user', 'DESC')
            ->get()
            ->getRowArray();

        return normalizeNomorWa((string) ($row['no_telp'] ?? ''));
    }
}
