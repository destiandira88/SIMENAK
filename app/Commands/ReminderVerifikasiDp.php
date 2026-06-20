<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;

class ReminderVerifikasiDp extends BaseCommand
{
    protected $group       = 'SIMENAK';
    protected $name        = 'reminder-verifikasi-dp';
    protected $description = 'Kirim reminder ke keuangan (>=2 jam) dan eskalasi ke owner (>=6 jam) untuk DP belum diverifikasi';
    protected $usage       = 'reminder-verifikasi-dp';

    public function run(array $params): void
    {
        helper('notification');

        $db = \Config\Database::connect();

        if (!$this->reminderColumnsReady($db)) {
            CLI::error('Jalankan docs/sql/payments_reminder_verif_flags.sql terlebih dahulu.');

            return;
        }

        try {
            $reminder = $this->kirimReminder2Jam($db);
            $eskalasi = $this->kirimEskalasi6Jam($db);
            CLI::write("Selesai. Reminder 2 jam: {$reminder}, Eskalasi 6 jam: {$eskalasi}.", 'green');
        } catch (\Throwable $e) {
            log_message('error', '[ReminderVerifikasiDp] {msg}', ['msg' => $e->getMessage()]);
            CLI::error('Error: ' . $e->getMessage());
        }
    }

    private function reminderColumnsReady(BaseConnection $db): bool
    {
        return $db->fieldExists('reminder_verif_2j_sent', 'payments')
            && $db->fieldExists('reminder_verif_6j_sent', 'payments');
    }

    private function fetchPendingDpPayments(BaseConnection $db): array
    {
        return $db->table('payments py')
            ->select('py.*, o.kode_order, o.id_order, u.nama AS nama_pelanggan')
            ->join('orders o', 'o.id_order = py.id_order')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('py.jenis', 'dp')
            ->where('py.status', 'menunggu')
            ->where('py.tgl_upload IS NOT NULL', null, false)
            ->get()
            ->getResultArray();
    }

    private function kirimReminder2Jam(BaseConnection $db): int
    {
        $cutoff = date('Y-m-d H:i:s', time() - (2 * 3600));
        $sent   = 0;

        $payments = $this->fetchPendingDpPayments($db);

        foreach ($payments as $pay) {
            if ((int) ($pay['reminder_verif_2j_sent'] ?? 0) === 1) {
                continue;
            }

            if ((string) ($pay['tgl_upload'] ?? '') === '' || (string) $pay['tgl_upload'] > $cutoff) {
                continue;
            }

            try {
                $this->notifyKeuanganReminder($db, $pay);
                $db->table('payments')
                    ->where('id_payment', (int) $pay['id_payment'])
                    ->update(['reminder_verif_2j_sent' => 1]);

                CLI::write("[REMINDER 2J] {$pay['kode_order']} — notif terkirim ke keuangan.", 'yellow');
                $sent++;
            } catch (\Throwable $e) {
                log_message('error', '[ReminderVerifikasiDp::2j] {kode} {msg}', [
                    'kode' => $pay['kode_order'] ?? '',
                    'msg'  => $e->getMessage(),
                ]);
                CLI::error('[REMINDER 2J] Gagal: ' . ($pay['kode_order'] ?? '') . ' — ' . $e->getMessage());
            }
        }

        if ($sent === 0) {
            CLI::write('Tidak ada DP untuk reminder 2 jam.', 'light_gray');
        }

        return $sent;
    }

    private function kirimEskalasi6Jam(BaseConnection $db): int
    {
        $cutoff = date('Y-m-d H:i:s', time() - (6 * 3600));
        $sent   = 0;

        $payments = $this->fetchPendingDpPayments($db);

        foreach ($payments as $pay) {
            if ((int) ($pay['reminder_verif_6j_sent'] ?? 0) === 1) {
                continue;
            }

            if ((string) ($pay['tgl_upload'] ?? '') === '' || (string) $pay['tgl_upload'] > $cutoff) {
                continue;
            }

            try {
                $this->notifyOwnerEskalasi($db, $pay);
                $db->table('payments')
                    ->where('id_payment', (int) $pay['id_payment'])
                    ->update(['reminder_verif_6j_sent' => 1]);

                CLI::write("[ESKALASI 6J] {$pay['kode_order']} — eskalasi terkirim ke owner.", 'red');
                $sent++;
            } catch (\Throwable $e) {
                log_message('error', '[ReminderVerifikasiDp::6j] {kode} {msg}', [
                    'kode' => $pay['kode_order'] ?? '',
                    'msg'  => $e->getMessage(),
                ]);
                CLI::error('[ESKALASI 6J] Gagal: ' . ($pay['kode_order'] ?? '') . ' — ' . $e->getMessage());
            }
        }

        if ($sent === 0) {
            CLI::write('Tidak ada DP untuk eskalasi 6 jam.', 'light_gray');
        }

        return $sent;
    }

    /**
     * @param array<string, mixed> $pay
     */
    private function notifyKeuanganReminder(BaseConnection $db, array $pay): void
    {
        $keuanganUsers = $db->table('users')->where('role', 'keuangan')->get()->getResultArray();
        $kodeOrder     = (string) $pay['kode_order'];
        $namaPelanggan = (string) $pay['nama_pelanggan'];

        foreach ($keuanganUsers as $ku) {
            sendNotifEmail(
                (string) $ku['email'],
                "⏰ Reminder: Verifikasi DP {$kodeOrder}",
                '<p>Halo <strong>' . esc((string) $ku['nama']) . '</strong>,</p>'
                . '<p>Bukti DP untuk pesanan <strong>' . esc($kodeOrder) . '</strong>'
                . ' dari <strong>' . esc($namaPelanggan) . '</strong>'
                . ' sudah menunggu verifikasi selama <strong>2 jam</strong>.</p>'
                . '<p>Segera verifikasi agar proses pesanan tidak terlambat.</p>'
                . '<p><a href="' . esc(site_url('verifikasi-dp')) . '">Buka halaman verifikasi DP</a></p>'
            );
            sendNotifInApp(
                (int) $ku['id_user'],
                (int) $pay['id_order'],
                'Reminder Verifikasi DP',
                'Bukti DP ' . $kodeOrder . ' belum diverifikasi selama 2 jam.'
            );
        }
    }

    /**
     * @param array<string, mixed> $pay
     */
    private function notifyOwnerEskalasi(BaseConnection $db, array $pay): void
    {
        $ownerUsers    = $db->table('users')->where('role', 'owner')->get()->getResultArray();
        $kodeOrder     = (string) $pay['kode_order'];
        $namaPelanggan = (string) $pay['nama_pelanggan'];

        foreach ($ownerUsers as $own) {
            sendNotifEmail(
                (string) $own['email'],
                "🚨 Eskalasi: DP {$kodeOrder} belum diverifikasi 6 jam",
                '<p>Halo <strong>' . esc((string) $own['nama']) . '</strong>,</p>'
                . '<p>Bukti DP pesanan <strong>' . esc($kodeOrder) . '</strong>'
                . ' dari <strong>' . esc($namaPelanggan) . '</strong>'
                . ' belum diverifikasi selama <strong>6 jam</strong>.</p>'
                . '<p>Mohon koordinasikan bagian keuangan untuk segera menindaklanjuti.</p>'
                . '<p><a href="' . esc(site_url('dashboard')) . '">Buka dashboard</a></p>'
            );
            sendNotifInApp(
                (int) $own['id_user'],
                (int) $pay['id_order'],
                'Eskalasi: DP Belum Diverifikasi',
                'DP ' . $kodeOrder . ' belum diverifikasi 6 jam. Koordinasikan bagian keuangan.'
            );
        }
    }
}
