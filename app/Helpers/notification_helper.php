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

/**
 * Parse input harga rupiah ke integer (tanpa desimal).
 * Aman untuk format "40.000", "40000", atau nilai numeric POST.
 */
function parseRupiahAmount(mixed $value): int
{
    if ($value === null || $value === '') {
        return 0;
    }

    if (is_int($value)) {
        return max(0, $value);
    }

    $str = trim((string) $value);
    if ($str === '') {
        return 0;
    }

    if (preg_match('/^\d{1,3}(?:\.\d{3})+(?:,\d+)?$/', $str)) {
        $str = str_replace('.', '', $str);
        $str = str_replace(',', '.', $str);
    }

    if (is_numeric($str)) {
        return max(0, (int) round((float) $str));
    }

    $digits = preg_replace('/\D/', '', $str);

    return $digits !== '' ? (int) $digits : 0;
}

/**
 * Normalisasi nomor NPWP ke format resmi: XX.XXX.XXX.X-XXX.XXX (15 digit).
 * Menerima input berformat atau hanya angka. Mengembalikan null jika tidak valid.
 */
function normalizeNpwp(string $input): ?string
{
    $digits = preg_replace('/\D/', '', trim($input));

    if ($digits === null || strlen($digits) !== 15 || !ctype_digit($digits)) {
        return null;
    }

    if ($digits === str_repeat('0', 15)) {
        return null;
    }

    return substr($digits, 0, 2) . '.'
        . substr($digits, 2, 3) . '.'
        . substr($digits, 5, 3) . '.'
        . substr($digits, 8, 1) . '-'
        . substr($digits, 9, 3) . '.'
        . substr($digits, 12, 3);
}

function isValidNpwp(string $input): bool
{
    return normalizeNpwp($input) !== null;
}

const BATAS_ORDER_TANPA_DP = 5000000;

function nominalDpFromTotal(int|float $totalHarga): int
{
    return (int) round((float) $totalHarga * 0.5);
}

function nominalPelunasanFromOrder(int|float $totalHarga, int $requireDp): int
{
    if ($requireDp === 1) {
        return nominalDpFromTotal($totalHarga);
    }

    return (int) round((float) $totalHarga);
}

/**
 * @return array{jenisFinal: string, requireDp: int, statusAwal: string, warning: string|null}
 */
function resolveOrderPaymentScheme(array $pelanggan, string $jenisDiminta, int $totalHarga): array
{
    $isVerified  = (int) ($pelanggan['is_verified'] ?? 0) === 1;
    $isSuspended = (int) ($pelanggan['is_suspended'] ?? 0) === 1;
    $tier        = (string) ($pelanggan['tier_perusahaan'] ?? 'pemula');
    $warning     = null;
    $jenisFinal  = 'perseorangan';
    $requireDp   = 1;
    $statusAwal  = 'menunggu_verifikasi_dp';

    if ($jenisDiminta !== 'perusahaan') {
        return compact('jenisFinal', 'requireDp', 'statusAwal', 'warning');
    }

    if (!$isVerified) {
        $warning = 'Akun belum terverifikasi sebagai perusahaan. Diproses sebagai perseorangan (DP 50%).';

        return compact('jenisFinal', 'requireDp', 'statusAwal', 'warning');
    }

    if ($isSuspended) {
        $warning = 'Akun perusahaan sedang disuspend. Diproses sebagai perseorangan (DP 50%).';

        return compact('jenisFinal', 'requireDp', 'statusAwal', 'warning');
    }

    $jenisFinal = 'perusahaan';

    if ($tier === 'pemula' || $totalHarga > BATAS_ORDER_TANPA_DP) {
        $requireDp  = 1;
        $statusAwal = 'menunggu_verifikasi_dp';
    } else {
        $requireDp  = 0;
        $statusAwal = 'terverifikasi';
    }

    return compact('jenisFinal', 'requireDp', 'statusAwal', 'warning');
}

function isPelunasanSebelumKirim(array $order, ?array $pelanggan = null): bool
{
    if (($order['jenis_pelanggan'] ?? '') !== 'perusahaan') {
        return true;
    }

    $tier = (string) ($pelanggan['tier_perusahaan'] ?? $order['tier_perusahaan'] ?? 'pemula');

    return $tier === 'pemula';
}

function isMetodeAmbilSendiri(array $order): bool
{
    return ($order['metode_pengiriman'] ?? '') === 'ambil_sendiri';
}

function statusSiapSebelumPelunasan(array $order): string
{
    return isMetodeAmbilSendiri($order) ? 'siap_diambil' : 'siap_kirim';
}

function revertStatusAfterPelunasanDitolak(array $order, ?array $pelanggan = null): string
{
    if (!isPelunasanSebelumKirim($order, $pelanggan)) {
        return 'menunggu_verifikasi_lunas';
    }

    return statusSiapSebelumPelunasan($order);
}

function canUploadPelunasan(array $order, ?array $pelanggan = null): bool
{
    $status = (string) ($order['status'] ?? '');

    if (isPelunasanSebelumKirim($order, $pelanggan)) {
        return in_array($status, ['siap_kirim', 'siap_diambil'], true);
    }

    return $status === 'menunggu_verifikasi_lunas';
}

function canViewNotaTagihan(array $order, ?array $pelanggan = null): bool
{
    $totalHarga = (int) round((float) ($order['total_harga'] ?? 0));
    if ($totalHarga <= 0) {
        return false;
    }

    $status = (string) ($order['status'] ?? '');

    return in_array($status, ['siap_kirim', 'siap_diambil', 'menunggu_verifikasi_lunas', 'pelunasan_terverifikasi'], true);
}

function generateKodeNota(string $kodeOrder): string
{
    return 'NOT-' . str_replace('ORD-', '', $kodeOrder);
}

/** Bukti pembayaran resmi pelunasan hanya setelah keuangan ACC. */
function canViewBuktiPembayaranPelunasan(?array $payment): bool
{
    return $payment !== null
        && ($payment['jenis'] ?? '') === 'pelunasan'
        && ($payment['status'] ?? '') === 'terverifikasi';
}

function accPelunasanTargetStatus(array $order, ?array $pelanggan = null): string
{
    return isPelunasanSebelumKirim($order, $pelanggan) ? 'pelunasan_terverifikasi' : 'selesai';
}

function orderHasPendingPelunasanBukti(array $row): bool
{
    $status = (string) ($row['status'] ?? $row['order_status'] ?? '');
    if ($status !== 'menunggu_verifikasi_lunas') {
        return false;
    }

    if (($row['pelunasan_status'] ?? '') === 'menunggu' && !empty($row['pelunasan_bukti_tf'])) {
        return true;
    }

    return ($row['jenis'] ?? '') === 'pelunasan' && ($row['payment_status'] ?? '') === 'menunggu';
}

function countOrderLancarPerusahaan(int $idPelanggan): int
{
    $db = \Config\Database::connect();

    return (int) $db->table('orders o')
        ->join(
            'payments pay',
            'pay.id_order = o.id_order AND pay.jenis = \'pelunasan\' AND pay.status = \'terverifikasi\'',
            'inner'
        )
        ->where('o.id_pelanggan', $idPelanggan)
        ->where('o.jenis_pelanggan', 'perusahaan')
        ->where('o.status', 'selesai')
        ->countAllResults();
}

function canPromotePerusahaanToTerpercaya(array $pelanggan): bool
{
    if ((int) ($pelanggan['is_verified'] ?? 0) !== 1) {
        return false;
    }

    if ((string) ($pelanggan['tier_perusahaan'] ?? '') === 'terpercaya') {
        return false;
    }

    if ((int) ($pelanggan['is_suspended'] ?? 0) === 1) {
        return false;
    }

    return countOrderLancarPerusahaan((int) $pelanggan['id_pelanggan']) >= 3;
}

function getTierPerusahaanLabel(?string $tier): string
{
    return match ($tier) {
        'terpercaya' => 'Terpercaya',
        'pemula'     => 'Pemula',
        default      => '',
    };
}

function getTierPerusahaanBadgeClass(?string $tier): string
{
    return match ($tier) {
        'terpercaya' => 'bg-indigo-100 text-indigo-800',
        'pemula'     => 'bg-amber-100 text-amber-800',
        default      => 'bg-slate-100 text-slate-600',
    };
}

function orderTotalFromKatalog(int|float $hargaDasar, int $jumlah): int
{
    return (int) round((float) $hargaDasar * $jumlah);
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

function getStatusLabel(string $status, bool $hasBuktiDp = false, ?string $viewerRole = null): string
{
    $role        = $viewerRole ?? (string) (session()->get('role') ?? '');
    $isPelanggan = $role === 'pelanggan';

    $labels = [
        'menunggu_konfirmasi_harga'     => 'Menunggu Konfirmasi Harga',
        'menunggu_konfirmasi_pelanggan' => $isPelanggan
            ? 'Penawaran Menunggu Konfirmasi Anda'
            : 'Menunggu Konfirmasi Pelanggan',
        'menunggu_verifikasi_dp'        => $hasBuktiDp
            ? 'Bukti DP Diunggah-Menunggu Verifikasi'
            : 'Menunggu Pembayaran DP',
        'terverifikasi'                 => 'DP Terverifikasi',
        'proses_desain'                 => 'Proses Desain',
        'proses_revisi'                 => 'Proses Revisi',
        'proses_cetak'                  => 'Proses Cetak',
        'finishing'                     => 'Finishing',
        'siap_kirim'                    => 'Siap Dikirim',
        'siap_diambil'                  => 'Siap Diambil',
        'dikirim'                       => $isPelanggan ? 'Dalam Pengiriman' : 'Dikirim',
        'pesanan_diterima'              => 'Pesanan Diterima',
        'menunggu_verifikasi_lunas'     => 'Menunggu Pembayaran Pelunasan',
        'pelunasan_terverifikasi'       => 'Pelunasan Terverifikasi',
        'selesai'                       => 'Selesai',
        'dibatalkan'                    => 'Dibatalkan',
    ];

    return $labels[$status] ?? ucwords(str_replace('_', ' ', $status));
}

/**
 * Subquery SELECT untuk status & bukti DP terbaru per order.
 */
function sqlLatestDpPaymentFields(string $orderIdColumn): string
{
    return "(SELECT py.status FROM payments py WHERE py.id_order = {$orderIdColumn} AND py.jenis = 'dp' ORDER BY py.tgl_upload DESC LIMIT 1) AS dp_status, "
        . "(SELECT py.bukti_tf FROM payments py WHERE py.id_order = {$orderIdColumn} AND py.jenis = 'dp' ORDER BY py.tgl_upload DESC LIMIT 1) AS dp_bukti_tf";
}

/**
 * Subquery SELECT untuk status & bukti pelunasan terbaru per order.
 */
function sqlLatestPelunasanPaymentFields(string $orderIdColumn): string
{
    return "(SELECT py.status FROM payments py WHERE py.id_order = {$orderIdColumn} AND py.jenis = 'pelunasan' ORDER BY py.tgl_upload DESC LIMIT 1) AS pelunasan_status, "
        . "(SELECT py.bukti_tf FROM payments py WHERE py.id_order = {$orderIdColumn} AND py.jenis = 'pelunasan' ORDER BY py.tgl_upload DESC LIMIT 1) AS pelunasan_bukti_tf";
}

/** DP + pelunasan terbaru — untuk label status di halaman list. */
function sqlLatestOrderStatusPaymentFields(string $orderIdColumn): string
{
    return sqlLatestDpPaymentFields($orderIdColumn) . ', ' . sqlLatestPelunasanPaymentFields($orderIdColumn);
}

function orderHasPendingDpBukti(array $row): bool
{
    $status = (string) ($row['status'] ?? $row['order_status'] ?? '');
    if ($status !== 'menunggu_verifikasi_dp') {
        return false;
    }

    if (($row['dp_status'] ?? '') === 'menunggu' && !empty($row['dp_bukti_tf'])) {
        return true;
    }

    return ($row['jenis'] ?? '') === 'dp' && ($row['payment_status'] ?? '') === 'menunggu';
}

function getOrderStatusLabel(array $row, ?string $viewerRole = null): string
{
    $status = (string) ($row['status'] ?? $row['order_status'] ?? '');

    if ($status === 'menunggu_verifikasi_lunas') {
        if (orderHasPendingPelunasanBukti($row)) {
            return 'Bukti Pelunasan Diunggah-Menunggu Verifikasi';
        }

        $orderCtx = [
            'jenis_pelanggan' => (string) ($row['jenis_pelanggan'] ?? ''),
            'tier_perusahaan' => $row['tier_perusahaan'] ?? null,
        ];
        $pelangganCtx = ['tier_perusahaan' => $row['tier_perusahaan'] ?? null];

        if (isPelunasanSebelumKirim($orderCtx, $pelangganCtx)) {
            return 'Menunggu Pembayaran Pelunasan';
        }

        return 'Nota Tagihan-Menunggu Pembayaran';
    }

    return getStatusLabel($status, orderHasPendingDpBukti($row), $viewerRole);
}

function getPaymentRiwayatStatusLabel(array $row, ?string $viewerRole = null): string
{
    $paymentStatus = (string) ($row['status'] ?? $row['payment_status'] ?? '');

    if ($paymentStatus === 'terverifikasi') {
        return 'Terverifikasi';
    }

    if ($paymentStatus === 'ditolak') {
        return 'Ditolak';
    }

    if ($paymentStatus !== 'menunggu') {
        return $paymentStatus !== '' ? ucfirst($paymentStatus) : '-';
    }

    $jenis       = (string) ($row['jenis'] ?? '');
    $orderStatus = (string) ($row['order_status'] ?? '');

    if ($jenis === 'dp' && $orderStatus === 'menunggu_verifikasi_dp') {
        return getOrderStatusLabel([
            'status'         => $orderStatus,
            'order_status'   => $orderStatus,
            'jenis'          => $jenis,
            'payment_status' => $paymentStatus,
            'dp_status'      => $paymentStatus,
            'dp_bukti_tf'    => $row['bukti_tf'] ?? '',
        ], $viewerRole);
    }

    if ($jenis === 'pelunasan' && $orderStatus === 'menunggu_verifikasi_lunas') {
        return 'Bukti Pelunasan Diunggah-Menunggu Verifikasi';
    }

    return 'Menunggu Verifikasi';
}

function getPaymentRiwayatStatusBadgeClass(array $row): string
{
    $paymentStatus = (string) ($row['status'] ?? $row['payment_status'] ?? '');

    if ($paymentStatus === 'terverifikasi') {
        return 'bg-emerald-100 text-emerald-800';
    }

    if ($paymentStatus === 'ditolak') {
        return 'bg-red-100 text-red-800';
    }

    if ($paymentStatus === 'menunggu') {
        $orderStatus = (string) ($row['order_status'] ?? '');

        return $orderStatus !== '' ? getStatusBadgeClass($orderStatus) : 'bg-yellow-100 text-yellow-800';
    }

    return 'bg-slate-100 text-slate-600';
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
        if ($role === 'admin') {
            return site_url('verifikasi-perusahaan');
        }
        if ($role === 'pelanggan') {
            return site_url('dashboard?profil=1');
        }

        return null;
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
        'pelunasan_terverifikasi' => 'bg-[#DBEAFE] text-[#1E40AF]',
        'pesanan_diterima'        => 'bg-[#DCFCE7] text-[#166534]',
        'selesai'                 => 'bg-[#DCFCE7] text-[#166534]',
        'dibatalkan'              => 'bg-[#FEE2E2] text-[#991B1B]',
        default            => 'bg-slate-100 text-slate-600',
    };
}

/**
 * Apakah produksi boleh mengunggah draft baru untuk pesanan ini?
 *
 * @param array<string, mixed>      $order
 * @param array<string, mixed>|null $lastRevisi
 */
function canProduksiUploadDraft(array $order, ?array $lastRevisi = null): bool
{
    $status = (string) ($order['status'] ?? '');
    if (!in_array($status, ['terverifikasi', 'proses_desain', 'proses_revisi'], true)) {
        return false;
    }

    if ($lastRevisi === null) {
        return true;
    }

    return (string) ($lastRevisi['status'] ?? '') === 'diajukan_revisi';
}

/**
 * @return array{start: string, end: string}
 */
function metricPeriodBounds(string $dariTgl, string $sampaiTgl): array
{
    return [
        'start' => $dariTgl . ' 00:00:00',
        'end'   => $sampaiTgl . ' 23:59:59',
    ];
}

/**
 * Total pemasukan kas: payment terverifikasi dalam rentang tgl_verifikasi.
 */
function getTotalPendapatanPeriode(string $dariTgl, string $sampaiTgl): float
{
    $bounds = metricPeriodBounds($dariTgl, $sampaiTgl);
    $row    = \Config\Database::connect()
        ->table('payments')
        ->selectSum('nominal', 'total')
        ->where('status', 'terverifikasi')
        ->where('tgl_verifikasi >=', $bounds['start'])
        ->where('tgl_verifikasi <=', $bounds['end'])
        ->get()
        ->getRowArray();

    return (float) ($row['total'] ?? 0);
}

/**
 * Pesanan selesai dalam periode (tanggal selesai, bukan tanggal dibuat).
 * Perseorangan: pengiriman.tgl_diterima · Perusahaan: tgl verifikasi pelunasan.
 */
function getPesananSelesaiPeriode(string $dariTgl, string $sampaiTgl): int
{
    $bounds = metricPeriodBounds($dariTgl, $sampaiTgl);
    $db     = \Config\Database::connect();

    $perseorangan = (int) $db->table('orders o')
        ->join('pengiriman pg', 'pg.id_order = o.id_order', 'inner')
        ->where('o.status', 'selesai')
        ->where('o.jenis_pelanggan !=', 'perusahaan')
        ->where('pg.tgl_diterima >=', $bounds['start'])
        ->where('pg.tgl_diterima <=', $bounds['end'])
        ->countAllResults();

    $rows = $db->table('orders o')
        ->select('o.id_order')
        ->join('payments p', 'p.id_order = o.id_order', 'inner')
        ->where('o.status', 'selesai')
        ->where('o.jenis_pelanggan', 'perusahaan')
        ->where('p.jenis', 'pelunasan')
        ->where('p.status', 'terverifikasi')
        ->where('p.tgl_verifikasi >=', $bounds['start'])
        ->where('p.tgl_verifikasi <=', $bounds['end'])
        ->groupBy('o.id_order')
        ->get()
        ->getResultArray();

    $perusahaan = count($rows);

    return $perseorangan + $perusahaan;
}
