<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;

class CekDpDeadline extends BaseCommand
{
    protected $group       = 'SIMENAK';
    protected $name        = 'cek-dp-deadline';
    protected $description = 'Kirim reminder DP jam ke-12 dan batalkan otomatis pesanan timeout jam ke-24';
    protected $usage       = 'cek-dp-deadline';

    public function run(array $params): void
    {
        helper('notification');

        $db = \Config\Database::connect();

        try {
            $this->kirimReminderJam12($db);
            $this->autoBatalJam24($db);
            CLI::write('Selesai.', 'green');
        } catch (\Throwable $e) {
            log_message('error', '[CekDpDeadline] {msg}', ['msg' => $e->getMessage()]);
            CLI::error('Error: ' . $e->getMessage());
        }
    }

    private function kirimReminderJam12(BaseConnection $db): void
    {
        $paidIds = $this->getOrderIdsWithDpPayment($db);
        $jam12   = date('Y-m-d H:i:s', strtotime('+12 hours'));

        $builder = $db->table('orders o')
            ->select('o.*, u.email, u.nama, u.id_user AS id_user_pelanggan')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('o.status', 'menunggu_verifikasi_dp')
            ->where('o.require_dp', 1)
            ->where('o.reminder_dp_sent', 0)
            ->where('o.batas_upload_dp IS NOT NULL', null, false)
            ->where('o.batas_upload_dp <=', $jam12);

        if ($paidIds !== []) {
            $builder->whereNotIn('o.id_order', $paidIds);
        }

        $orders = $builder->get()->getResultArray();

        foreach ($orders as $order) {
            try {
                $kodeOrder = (string) $order['kode_order'];
                $nama      = (string) $order['nama'];
                $email     = (string) $order['email'];
                $batasFmt  = date('d M Y H:i', strtotime((string) $order['batas_upload_dp'])) . ' WIB';

                $judulReminderDp = 'Pengingat Upload Bukti DP';
                $pesanReminderDp = "Pesanan {$kodeOrder}: unggah bukti DP sebelum {$batasFmt}.";
                $detailUrlReminderDp = pelangganOrderDetailUrl($kodeOrder, $judulReminderDp);

                sendNotifEmail(
                    $email,
                    "Pengingat: Upload Bukti DP Pesanan {$kodeOrder}",
                    '<p>Halo <strong>' . esc($nama) . '</strong>,</p>'
                        . "<p>Pesanan <strong>{$kodeOrder}</strong> belum ada bukti DP-nya.</p>"
                        . '<p>Segera unggah bukti transfer sebelum batas waktu: '
                        . '<strong>' . esc($batasFmt) . '</strong>.</p>'
                        . '<p>Jika tidak diunggah, pesanan otomatis dibatalkan.</p>'
                        . '<p><a href="' . esc($detailUrlReminderDp) . '">Unggah bukti DP</a></p>'
                );
                sendNotifWaForEmail(
                    $db,
                    $email,
                    buildNotifWaText(
                        "{$judulReminderDp}-{$kodeOrder}",
                        $pesanReminderDp,
                        $detailUrlReminderDp
                    )
                );

                $idUserReminder = (int) ($order['id_user_pelanggan'] ?? 0);
                if ($idUserReminder > 0) {
                    sendNotifInApp(
                        $idUserReminder,
                        (int) $order['id_order'],
                        $judulReminderDp,
                        $pesanReminderDp
                    );
                }

                $db->table('orders')
                    ->where('id_order', (int) $order['id_order'])
                    ->update(['reminder_dp_sent' => 1]);

                CLI::write("[REMINDER] {$kodeOrder} — email terkirim ke {$email}", 'yellow');
            } catch (\Throwable $e) {
                log_message('error', '[CekDpDeadline::reminder] {kode} {msg}', [
                    'kode' => $order['kode_order'] ?? '',
                    'msg'  => $e->getMessage(),
                ]);
                CLI::error('[REMINDER] Gagal: ' . ($order['kode_order'] ?? '') . ' — ' . $e->getMessage());
            }
        }

        if ($orders === []) {
            CLI::write('Tidak ada pesanan untuk reminder jam ke-12.', 'light_gray');
        }
    }

    private function autoBatalJam24(BaseConnection $db): void
    {
        $paidIds = $this->getOrderIdsWithDpPayment($db);
        $now     = date('Y-m-d H:i:s');

        $builder = $db->table('orders o')
            ->select('o.*, u.email, u.nama, u.id_user AS id_user_pelanggan')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('o.status', 'menunggu_verifikasi_dp')
            ->where('o.require_dp', 1)
            ->where('o.batas_upload_dp IS NOT NULL', null, false)
            ->where('o.batas_upload_dp <=', $now);

        if ($paidIds !== []) {
            $builder->whereNotIn('o.id_order', $paidIds);
        }

        $orders = $builder->get()->getResultArray();

        foreach ($orders as $order) {
            try {
                $idOrder   = (int) $order['id_order'];
                $kodeOrder = (string) $order['kode_order'];
                $nama      = (string) $order['nama'];
                $email     = (string) $order['email'];
                $idUser    = (int) $order['id_user_pelanggan'];

                $db->table('orders')
                    ->where('id_order', $idOrder)
                    ->update(['status' => 'dibatalkan']);

                $judulTimeoutDp = 'Pesanan Dibatalkan (Timeout DP)';
                $pesanTimeoutDp = "Pesanan {$kodeOrder} dibatalkan karena batas unggah bukti DP terlewat.";
                $detailUrlTimeoutDp = pelangganOrderDetailUrl($kodeOrder, $judulTimeoutDp);

                sendNotifEmail(
                    $email,
                    "Pesanan {$kodeOrder} Dibatalkan-Timeout Pembayaran DP",
                    '<p>Halo <strong>' . esc($nama) . '</strong>,</p>'
                        . "<p>Pesanan <strong>{$kodeOrder}</strong> telah otomatis dibatalkan "
                        . 'karena bukti DP tidak diterima dalam 24 jam.</p>'
                        . '<p>Anda bisa membuat pesanan baru kapan saja.</p>'
                        . '<p><a href="' . esc($detailUrlTimeoutDp) . '">Lihat detail pesanan</a></p>'
                );
                sendNotifWaForEmail(
                    $db,
                    $email,
                    buildNotifWaText(
                        "Pesanan Dibatalkan-{$kodeOrder}",
                        $pesanTimeoutDp,
                        $detailUrlTimeoutDp
                    )
                );

                sendNotifInApp(
                    $idUser,
                    $idOrder,
                    $judulTimeoutDp,
                    $pesanTimeoutDp
                );

                CLI::write("[BATAL] {$kodeOrder} — dibatalkan, email terkirim", 'red');
            } catch (\Throwable $e) {
                log_message('error', '[CekDpDeadline::batal] {kode} {msg}', [
                    'kode' => $order['kode_order'] ?? '',
                    'msg'  => $e->getMessage(),
                ]);
                CLI::error('[BATAL] Gagal: ' . ($order['kode_order'] ?? '') . ' — ' . $e->getMessage());
            }
        }

        if ($orders === []) {
            CLI::write('Tidak ada pesanan untuk auto batal jam ke-24.', 'light_gray');
        }
    }

    /**
     * Kembalikan id_order yang DP-nya AKTIF (menunggu atau sudah terverifikasi).
     * DP yang ditolak TIDAK dihitung — order tetap bisa auto-cancel.
     *
     * @return list<int>
     */
    private function getOrderIdsWithDpPayment(BaseConnection $db): array
    {
        $rows = $db->table('payments')
            ->select('id_order')
            ->where('jenis', 'dp')
            ->whereIn('status', ['menunggu', 'terverifikasi'])
            ->get()
            ->getResultArray();

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = (int) $row['id_order'];
        }

        return array_values(array_unique($ids));
    }
}
