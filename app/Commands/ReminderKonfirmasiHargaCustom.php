<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;

class ReminderKonfirmasiHargaCustom extends BaseCommand
{
    protected $group       = 'SIMENAK';
    protected $name        = 'reminder-konfirmasi-harga-custom';
    protected $description = 'Eskalasi ke owner (>=2 hari kerja) jika konfirmasi harga custom belum ditindaklanjuti admin';
    protected $usage       = 'reminder-konfirmasi-harga-custom';

    public function run(array $params): void
    {
        helper(['notification', 'deadline']);

        $db = \Config\Database::connect();

        if (! $this->columnReady($db)) {
            CLI::error('Jalankan migration atau docs/sql/orders_reminder_harga_eskalasi.sql terlebih dahulu.');

            return;
        }

        try {
            $sent = $this->kirimEskalasiOwner($db);
            CLI::write("Selesai. Eskalasi konfirmasi harga custom: {$sent}.", 'green');
        } catch (\Throwable $e) {
            log_message('error', '[ReminderKonfirmasiHargaCustom] {msg}', ['msg' => $e->getMessage()]);
            CLI::error('Error: ' . $e->getMessage());
        }
    }

    private function columnReady(BaseConnection $db): bool
    {
        return $db->fieldExists('reminder_harga_eskalasi_sent', 'orders');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchPendingCustomOrders(BaseConnection $db): array
    {
        return $db->table('orders o')
            ->select('o.*, u.nama AS nama_pelanggan, k.nama_produk')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->join('katalog k', 'k.id_katalog = o.id_katalog')
            ->where('o.is_custom', 1)
            ->where('o.status', 'menunggu_konfirmasi_harga')
            ->where('o.reminder_harga_eskalasi_sent', 0)
            ->get()
            ->getResultArray();
    }

    private function isPastSla2HariKerja(string $createdAt): bool
    {
        $ts = strtotime($createdAt);
        if ($ts === false) {
            return false;
        }

        $createdDate = date('Y-m-d', $ts);
        $slaDeadline = addHariKerja($createdDate, 2);

        return todayYmdApp() > $slaDeadline;
    }

    private function kirimEskalasiOwner(BaseConnection $db): int
    {
        $sent   = 0;
        $orders = $this->fetchPendingCustomOrders($db);

        foreach ($orders as $order) {
            if (! $this->isPastSla2HariKerja((string) ($order['created_at'] ?? ''))) {
                continue;
            }

            try {
                $this->notifyOwnerEskalasi($db, $order);
                $db->table('orders')
                    ->where('id_order', (int) $order['id_order'])
                    ->update(['reminder_harga_eskalasi_sent' => 1]);

                CLI::write("[ESKALASI HARGA] {$order['kode_order']} — eskalasi terkirim ke owner.", 'red');
                $sent++;
            } catch (\Throwable $e) {
                log_message('error', '[ReminderKonfirmasiHargaCustom::eskalasi] {kode} {msg}', [
                    'kode' => $order['kode_order'] ?? '',
                    'msg'  => $e->getMessage(),
                ]);
                CLI::error('[ESKALASI HARGA] Gagal: ' . ($order['kode_order'] ?? '') . ' — ' . $e->getMessage());
            }
        }

        if ($sent === 0) {
            CLI::write('Tidak ada pesanan custom untuk eskalasi konfirmasi harga.', 'light_gray');
        }

        return $sent;
    }

    /**
     * @param array<string, mixed> $order
     */
    private function notifyOwnerEskalasi(BaseConnection $db, array $order): void
    {
        $ownerUsers    = $db->table('users')->where('role', 'owner')->get()->getResultArray();
        $kodeOrder     = (string) $order['kode_order'];
        $namaPelanggan = (string) $order['nama_pelanggan'];
        $namaProduk    = (string) ($order['nama_produk'] ?? '-');
        $listUrl       = site_url('list-pemesanan?tab=menunggu-harga');
        $createdLabel  = formatTanggalId(date('Y-m-d', strtotime((string) ($order['created_at'] ?? 'now'))));

        foreach ($ownerUsers as $own) {
            sendNotifEmail(
                (string) $own['email'],
                "Eskalasi: Konfirmasi Harga {$kodeOrder} Belum Ditindaklanjuti",
                buildNotifEmailHtml(
                    'Eskalasi Konfirmasi Harga Custom',
                    '<p>Halo <strong>' . esc((string) $own['nama']) . '</strong>,</p>'
                    . '<p>Pesanan custom <strong>' . esc($kodeOrder) . '</strong>'
                    . ' dari <strong>' . esc($namaPelanggan) . '</strong>'
                    . ' (<strong>' . esc($namaProduk) . '</strong>)'
                    . ' masih menunggu konfirmasi harga admin selama lebih dari <strong>2 hari kerja</strong>'
                    . ' (diajukan ' . esc($createdLabel) . ').</p>'
                    . '<p>Mohon koordinasikan tim admin untuk segera menindaklanjuti konfirmasi harga agar pelanggan tidak menunggu terlalu lama.</p>',
                    $listUrl,
                    'Buka Daftar Menunggu Harga'
                )
            );
            sendNotifInApp(
                (int) $own['id_user'],
                (int) $order['id_order'],
                'Eskalasi: Konfirmasi Harga Custom',
                'Pesanan ' . $kodeOrder . ' belum dikonfirmasi admin >2 hari kerja. Koordinasikan tim admin.'
            );
        }

        sendNotifWaForRole(
            $db,
            'owner',
            buildNotifWaText(
                "Eskalasi Harga-{$kodeOrder}",
                "Pesanan custom {$kodeOrder} ({$namaPelanggan}) belum dikonfirmasi admin >2 hari kerja. Segera koordinasikan.",
                $listUrl
            )
        );
    }
}
