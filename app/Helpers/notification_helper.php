<?php

function generateKodeOrder(): string
{
    $db     = \Config\Database::connect();
    $prefix = 'ORD-' . date('Ymd') . '-';
    $count  = $db->table('orders')
        ->like('kode_order', $prefix, 'after')
        ->countAllResults();

    return $prefix . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

function generateKodePayment(): string
{
    $db     = \Config\Database::connect();
    $prefix = 'PAY-' . date('Ymd') . '-';
    $count  = $db->table('payments')
        ->like('kode_payment', $prefix, 'after')
        ->countAllResults();

    return $prefix . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

function sendNotifInApp(int $idUser, ?int $idOrder, string $judul, string $pesan): void
{
    $db = \Config\Database::connect();
    $db->table('notifications')->insert([
        'id_user'    => $idUser,
        'id_order'   => $idOrder,
        'judul'      => $judul,
        'pesan'      => $pesan,
        'is_read'    => 0,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}

function sendNotifEmail(string $to, string $subject, string $htmlBody): bool
{
    try {
        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($htmlBody);

        return $email->send();
    } catch (\Throwable $e) {
        log_message('error', '[sendNotifEmail] {msg}', ['msg' => $e->getMessage()]);

        return false;
    }
}

function getStatusLabel(string $status, bool $hasBuktiDp = false): string
{
    $labels = [
        'menunggu_konfirmasi_harga'     => 'Menunggu Konfirmasi Harga',
        'menunggu_konfirmasi_pelanggan' => 'Penawaran Menunggu Konfirmasimu',
        'menunggu_verifikasi_dp'        => $hasBuktiDp
            ? 'Bukti DP Diunggah — Menunggu Verifikasi'
            : 'Menunggu Pembayaran DP',
        'terverifikasi'                 => 'DP Terverifikasi',
        'proses_desain'                 => 'Proses Desain',
        'proses_revisi'                 => 'Proses Revisi',
        'proses_cetak'                  => 'Proses Cetak',
        'finishing'                     => 'Finishing',
        'siap_kirim'                    => 'Siap Dikirim',
        'siap_diambil'                  => 'Siap Diambil',
        'dikirim'                       => 'Dalam Pengiriman',
        'pesanan_diterima'              => 'Pesanan Diterima',
        'menunggu_verifikasi_lunas'     => 'Menunggu Verifikasi Pelunasan',
        'selesai'                       => 'Selesai',
        'dibatalkan'                    => 'Dibatalkan',
    ];

    return $labels[$status] ?? ucwords(str_replace('_', ' ', $status));
}

/**
 * @return array{icon: string, class: string}
 */
function getNotifIconMeta(string $judul): array
{
    $j = mb_strtolower($judul);

    if (str_contains($j, 'dibatalkan') || str_contains($j, 'ditolak') || str_contains($j, 'revisi')) {
        return ['icon' => '↺', 'class' => 'ni-red'];
    }

    if (str_contains($j, 'acc') || str_contains($j, 'cetak') || str_contains($j, 'siap')) {
        return ['icon' => '✓', 'class' => 'ni-green'];
    }

    if (str_contains($j, 'custom') || str_contains($j, 'harga') || str_contains($j, 'penawaran')) {
        return ['icon' => '★', 'class' => 'ni-amber'];
    }

    if (str_contains($j, 'verifikasi') || str_contains($j, 'perusahaan')) {
        return ['icon' => '🏢', 'class' => 'ni-amber'];
    }

    if (str_contains($j, 'dp') || str_contains($j, 'pelunasan') || str_contains($j, 'bayar')) {
        return ['icon' => '💳', 'class' => 'ni-blue'];
    }

    return ['icon' => '📋', 'class' => 'ni-blue'];
}

function formatNotifTimeAgo(string $datetime): string
{
    if ($datetime === '') {
        return '';
    }

    $ts = strtotime($datetime);
    if ($ts === false) {
        return $datetime;
    }

    $diff = time() - $ts;
    if ($diff < 60) {
        return 'Baru saja';
    }
    if ($diff < 3600) {
        $m = (int) floor($diff / 60);

        return $m . ' menit yang lalu';
    }
    if ($diff < 86400) {
        $h = (int) floor($diff / 3600);

        return $h . ' jam yang lalu';
    }
    if ($diff < 604800) {
        $d = (int) floor($diff / 86400);

        return $d . ' hari yang lalu';
    }

    return date('d M Y, H:i', $ts);
}

function getNotifActionUrl(
    string $role,
    ?string $kodeOrder,
    ?int $idOrder,
    string $judul
): ?string {
    $j = mb_strtolower($judul);

    if (str_contains($j, 'verifikasi') && str_contains($j, 'perusahaan')) {
        return $role === 'admin' ? site_url('verifikasi-perusahaan') : null;
    }

    if (str_contains($j, 'dp') || str_contains($j, 'pelunasan')) {
        if ($role === 'keuangan') {
            return str_contains($j, 'pelunasan')
                ? site_url('verifikasi-pelunasan')
                : site_url('verifikasi-dp');
        }
    }

    if ($kodeOrder === null || $kodeOrder === '') {
        return null;
    }

    $orderId = (int) ($idOrder ?? 0);

    return match ($role) {
        'pelanggan' => site_url('order/detail/' . $kodeOrder),
        'admin'     => $orderId > 0
            ? site_url('list-pemesanan/' . $orderId)
            : site_url('list-pemesanan'),
        'produksi'  => $orderId > 0
            ? site_url('manajemen-desain/' . $orderId)
            : site_url('antrian-desain'),
        'keuangan'  => site_url('verifikasi-dp'),
        default     => site_url('dashboard'),
    };
}

function getStatusBadgeClass(string $status): string
{
    if (str_starts_with($status, 'menunggu')) {
        return 'bg-[#FEF3C7] text-[#92400E]';
    }

    return match ($status) {
        'terverifikasi'    => 'bg-[#DBEAFE] text-[#1E40AF]',
        'proses_desain',
        'proses_revisi',
        'proses_cetak'     => 'bg-[#EDE9FE] text-[#5B21B6]',
        'finishing'        => 'bg-[#CFFAFE] text-[#164E63]',
        'siap_kirim',
        'siap_diambil'     => 'bg-[#CCFBF1] text-[#065F46]',
        'dikirim'          => 'bg-[#CFFAFE] text-[#164E63]',
        'pesanan_diterima' => 'bg-[#DCFCE7] text-[#166534]',
        'selesai'          => 'bg-[#DCFCE7] text-[#166534]',
        'dibatalkan'       => 'bg-[#FEE2E2] text-[#991B1B]',
        default            => 'bg-slate-100 text-slate-600',
    };
}
