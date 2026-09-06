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

/**
 * Nama lengkap: huruf, spasi, titik, tanda petik, strip (3–100 karakter).
 */
function isValidNamaLengkap(string $nama): bool
{
    $nama = trim($nama);
    $len  = mb_strlen($nama);

    if ($len < 3 || $len > 100) {
        return false;
    }

    return (bool) preg_match("/^[A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\s.'\-]*$/u", $nama);
}

/**
 * Nama perusahaan: huruf, angka, spasi, titik, koma, & (3–100 karakter).
 */
function isValidNamaPerusahaan(string $nama): bool
{
    $nama = trim($nama);
    $len  = mb_strlen($nama);

    if ($len < 3 || $len > 100) {
        return false;
    }

    return (bool) preg_match('/^[A-Za-z0-9][A-Za-z0-9\s.,&\-]*$/', $nama);
}

/**
 * No. telepon Indonesia: +62..., 08..., atau 022... diikuti 8–13 digit.
 */
function isValidNoTelepon(string $no): bool
{
    $no = trim($no);

    return (bool) preg_match('/^(\+62|08|022)[0-9]{8,13}$/', $no);
}

/**
 * Rules CI4 untuk data akun pelanggan (register / kelola pelanggan).
 *
 * @return array<string, string>
 */
function pelangganAkunValidationRules(?int $excludeUserId = null): array
{
    $emailRule = $excludeUserId === null
        ? 'required|valid_email|is_unique[users.email]'
        : 'required|valid_email|is_unique[users.email,id_user,' . $excludeUserId . ']';

    return [
        'nama'    => 'required|min_length[3]|max_length[100]',
        'email'   => $emailRule,
        'no_telp' => 'required|max_length[20]',
        'alamat'  => 'required|min_length[10]|max_length[150]',
    ];
}

/**
 * Pesan validasi CI4 — selaras dengan form register.
 *
 * @return array<string, array<string, string>>
 */
function pelangganAkunValidationMessages(): array
{
    return [
        'nama' => [
            'required'   => 'Nama lengkap wajib diisi.',
            'min_length' => 'Nama lengkap minimal 3 karakter.',
            'max_length' => 'Nama lengkap maksimal 100 karakter.',
        ],
        'email' => [
            'required'    => 'Email wajib diisi.',
            'valid_email' => 'Format email tidak valid.',
            'is_unique'   => 'Email sudah terdaftar. Gunakan email lain atau masuk ke akun Anda.',
        ],
        'no_telp' => [
            'required'   => 'No. telepon wajib diisi.',
            'max_length' => 'No. telepon terlalu panjang.',
        ],
        'alamat' => [
            'required'   => 'Alamat wajib diisi.',
            'min_length' => 'Alamat minimal 10 karakter.',
            'max_length' => 'Alamat maksimal 150 karakter.',
        ],
        'password' => [
            'required'   => 'Kata sandi wajib diisi.',
            'min_length' => 'Kata sandi minimal 8 karakter.',
        ],
        'password_confirm' => [
            'required' => 'Konfirmasi kata sandi wajib diisi.',
            'matches'  => 'Konfirmasi kata sandi tidak sama.',
        ],
    ];
}

/**
 * Validasi format nama & telepon pelanggan (selaras register).
 * Mengembalikan pesan error pertama, atau null jika valid.
 */
function validatePelangganAkunFormat(string $nama, string $noTelp): ?string
{
    if (!isValidNamaLengkap(trim($nama))) {
        return 'Nama lengkap hanya boleh berisi huruf, spasi, tanda kutip, atau titik (3–100 karakter).';
    }

    if (!isValidNoTelepon(trim($noTelp))) {
        return 'Format no. telepon harus berupa angka dan diawali dengan 08, +62, atau 022 (Contoh: 087778965442) (8–13 digit setelah awalan).';
    }

    return null;
}

const BATAS_ORDER_TANPA_DP = 5000000;

/** Akun dengan status kerja sama perusahaan aktif (ditetapkan Admin). */
function pelangganIsKerjasamaPerusahaan(?array $pelanggan): bool
{
    if ($pelanggan === null || $pelanggan === []) {
        return false;
    }

    return (string) ($pelanggan['jenis'] ?? '') === 'perusahaan'
        && (int) ($pelanggan['is_verified'] ?? 0) === 1;
}

/** Pelanggan masih punya pesanan yang belum selesai/dibatalkan. */
function pelangganHasActiveOrders(int $idPelanggan): bool
{
    if ($idPelanggan <= 0) {
        return false;
    }

    return \Config\Database::connect()
        ->table('orders')
        ->where('id_pelanggan', $idPelanggan)
        ->whereNotIn('status', ['selesai', 'dibatalkan'])
        ->countAllResults() > 0;
}

/**
 * @param list<int> $idPelanggans
 * @return array<int, true> id_pelanggan yang masih punya pesanan aktif
 */
function pelanggansWithActiveOrdersMap(array $idPelanggans): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $idPelanggans))));
    if ($ids === []) {
        return [];
    }

    $rows = \Config\Database::connect()
        ->table('orders')
        ->select('id_pelanggan')
        ->whereIn('id_pelanggan', $ids)
        ->whereNotIn('status', ['selesai', 'dibatalkan'])
        ->groupBy('id_pelanggan')
        ->get()
        ->getResultArray();

    $map = [];
    foreach ($rows as $row) {
        $id = (int) ($row['id_pelanggan'] ?? 0);
        if ($id > 0) {
            $map[$id] = true;
        }
    }

    return $map;
}

function pelangganCanCreateOrder(?array $pelanggan): bool
{
    return $pelanggan !== null && $pelanggan !== [];
}

/** Jenis skema pembayaran dari profil akun (bukan pilihan manual per pesanan). */
function resolveJenisPelangganFromAkun(?array $pelanggan): string
{
    return pelangganIsKerjasamaPerusahaan($pelanggan) ? 'perusahaan' : 'perseorangan';
}

function getKerjasamaPerusahaanLabel(): string
{
    return 'Kerja Sama Perusahaan';
}

function getLatestVerifikasiPerusahaan(int $idPelanggan): ?array
{
    $row = \Config\Database::connect()
        ->table('verifikasi_perusahaan')
        ->where('id_pelanggan', $idPelanggan)
        ->orderBy('tgl_pengajuan', 'DESC')
        ->get()
        ->getRowArray();

    return $row ?: null;
}

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
    unset($jenisDiminta);

    $warning    = null;
    $jenisFinal = 'perseorangan';
    $requireDp  = 1;
    $statusAwal = 'menunggu_verifikasi_dp';

    if (!pelangganIsKerjasamaPerusahaan($pelanggan)) {
        return compact('jenisFinal', 'requireDp', 'statusAwal', 'warning');
    }

    $jenisFinal = 'perusahaan';

    if ($totalHarga > BATAS_ORDER_TANPA_DP) {
        $requireDp  = 1;
        $statusAwal = 'menunggu_verifikasi_dp';
    } else {
        $requireDp  = 0;
        $statusAwal = 'terverifikasi';
    }

    return compact('jenisFinal', 'requireDp', 'statusAwal', 'warning');
}

/** Perseorangan: pelunasan sebelum kirim. Kerja sama perusahaan: selalu setelah diterima. */
function isPelunasanSebelumKirim(array $order): bool
{
    return ($order['jenis_pelanggan'] ?? '') !== 'perusahaan';
}

function isMetodeAmbilSendiri(array $order): bool
{
    return ($order['metode_pengiriman'] ?? '') === 'ambil_sendiri';
}

function statusSiapSebelumPelunasan(array $order): string
{
    return isMetodeAmbilSendiri($order) ? 'siap_diambil' : 'siap_kirim';
}

function revertStatusAfterPelunasanDitolak(array $order): string
{
    if (!isPelunasanSebelumKirim($order)) {
        return 'menunggu_verifikasi_lunas';
    }

    return statusSiapSebelumPelunasan($order);
}

function canUploadPelunasan(array $order): bool
{
    $status = (string) ($order['status'] ?? '');

    if (isPelunasanSebelumKirim($order)) {
        return in_array($status, ['siap_kirim', 'siap_diambil'], true);
    }

    return $status === 'menunggu_verifikasi_lunas';
}

function canViewNotaTagihan(array $order): bool
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

function accPelunasanTargetStatus(array $order): string
{
    return isPelunasanSebelumKirim($order) ? 'pelunasan_terverifikasi' : 'selesai';
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
        $config = config('Email');

        if ($config->protocol === 'smtp' && trim((string) $config->SMTPHost) === '') {
            log_message('error', '[sendNotifEmail] SMTPHost belum dikonfigurasi di .env / Config/Email.php');

            return false;
        }

        if (! isSmtpConfigured()) {
            log_message('error', '[sendNotifEmail] SMTP belum lengkap. Jalankan scripts/setup-gmail-smtp.bat atau isi email.SMTPUser & email.SMTPPass di .env');

            return false;
        }

        $email = \Config\Services::email();
        $email->clear(true);

        $fromEmail = trim((string) $config->fromEmail) ?: trim((string) $config->SMTPUser) ?: 'noreply@simenak.local';
        $fromName  = trim((string) $config->fromName) ?: "SIMENAK Z'Plack";
        $email->setFrom($fromEmail, $fromName);
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($htmlBody);
        $email->setMailType($config->mailType);

        $sent = $email->send(false);

        if (! $sent) {
            log_message('error', '[sendNotifEmail] gagal ke {to}: {debug}', [
                'to'    => $to,
                'debug' => $email->printDebugger(['headers', 'subject', 'body']),
            ]);
        }

        return $sent;
    } catch (\Throwable $e) {
        log_message('error', '[sendNotifEmail] {msg}', ['msg' => $e->getMessage()]);

        return false;
    }
}

/** Kirim email setelah respons HTTP dikirim (hanya jika fastcgi_finish_request tersedia). */
function deferNotifEmail(string $to, string $subject, string $htmlBody): void
{
    if (! function_exists('fastcgi_finish_request')) {
        try {
            sendNotifEmail($to, $subject, $htmlBody);
        } catch (\Throwable $e) {
            log_message('error', '[deferNotifEmail] {msg}', ['msg' => $e->getMessage()]);
        }

        return;
    }

    register_shutdown_function(static function () use ($to, $subject, $htmlBody) {
        try {
            sendNotifEmail($to, $subject, $htmlBody);
        } catch (\Throwable $e) {
            log_message('error', '[deferNotifEmail] {msg}', ['msg' => $e->getMessage()]);
        }
    });
}

/**
 * Normalisasi nomor ke format Fonnte: 62xxx (tanpa +).
 */
function normalizeNomorWa(?string $raw): ?string
{
    $digits = preg_replace('/\D+/', '', (string) $raw);
    if ($digits === '') {
        return null;
    }

    if (str_starts_with($digits, '0')) {
        $digits = '62' . substr($digits, 1);
    } elseif (str_starts_with($digits, '8')) {
        $digits = '62' . $digits;
    }

    if (! str_starts_with($digits, '62')) {
        return null;
    }

    $len = strlen($digits);
    if ($len < 11 || $len > 15) {
        return null;
    }

    return $digits;
}

function isFonnteConfigured(): bool
{
    return trim((string) env('fonnte.token', '')) !== '';
}

/**
 * Kirim WhatsApp via Fonnte. Gagal diam-diam (log saja), tidak mengganggu proses utama.
 */
function sendNotifWa(?string $nomor, string $pesan): bool
{
    $nomor = normalizeNomorWa($nomor);
    $pesan = trim($pesan);

    if ($nomor === null || $pesan === '') {
        return false;
    }

    $token = trim((string) env('fonnte.token', ''));
    if ($token === '') {
        log_message('debug', '[sendNotifWa] fonnte.token kosong, skip ke {nomor}', ['nomor' => $nomor]);

        return false;
    }

    try {
        $client   = \Config\Services::curlrequest(['timeout' => 15, 'http_errors' => false]);
        $response = $client->post('https://api.fonnte.com/send', [
            'headers'     => ['Authorization' => $token],
            'form_params' => [
                'target'  => $nomor,
                'message' => $pesan,
            ],
        ]);

        $decoded = json_decode((string) $response->getBody(), true);
        $ok      = is_array($decoded) && ($decoded['status'] ?? false) === true;

        if (! $ok) {
            $reason = is_array($decoded)
                ? (string) ($decoded['reason'] ?? json_encode($decoded))
                : (string) $response->getBody();
            log_message('error', '[sendNotifWa] gagal ke {nomor}: {reason}', [
                'nomor'  => $nomor,
                'reason' => $reason,
            ]);
        }

        return $ok;
    } catch (\Throwable $e) {
        log_message('error', '[sendNotifWa] {msg}', ['msg' => $e->getMessage(), 'nomor' => $nomor]);

        return false;
    }
}

/**
 * @return list<string>
 */
function collectWaNumbersForRole(\CodeIgniter\Database\BaseConnection $db, string $role): array
{
    $numbers = [];
    $envKey  = 'fonnte.notify.' . $role;
    $envList = trim((string) env($envKey, ''));

    if ($envList !== '') {
        foreach (explode(',', $envList) as $part) {
            $normalized = normalizeNomorWa(trim($part));
            if ($normalized !== null) {
                $numbers[$normalized] = true;
            }
        }
    }

    $rows = $db->table('users u')
        ->select('p.no_telp')
        ->join('pelanggan p', 'p.id_user = u.id_user', 'left')
        ->where('u.role', $role)
        ->get()
        ->getResultArray();

    foreach ($rows as $row) {
        $normalized = normalizeNomorWa((string) ($row['no_telp'] ?? ''));
        if ($normalized !== null) {
            $numbers[$normalized] = true;
        }
    }

    return array_keys($numbers);
}

function sendNotifWaForRole(\CodeIgniter\Database\BaseConnection $db, string $role, string $pesan): void
{
    foreach (collectWaNumbersForRole($db, $role) as $nomor) {
        sendNotifWa($nomor, $pesan);
    }
}

function resolveWaForEmail(\CodeIgniter\Database\BaseConnection $db, string $email): ?string
{
    $row = $db->table('users u')
        ->select('p.no_telp')
        ->join('pelanggan p', 'p.id_user = u.id_user', 'left')
        ->where('u.email', $email)
        ->get()
        ->getRowArray();

    return normalizeNomorWa((string) ($row['no_telp'] ?? ''));
}

function resolveWaForUserId(\CodeIgniter\Database\BaseConnection $db, int $idUser): ?string
{
    $row = $db->table('users u')
        ->select('p.no_telp')
        ->join('pelanggan p', 'p.id_user = u.id_user', 'left')
        ->where('u.id_user', $idUser)
        ->get()
        ->getRowArray();

    return normalizeNomorWa((string) ($row['no_telp'] ?? ''));
}

function sendNotifWaForEmail(\CodeIgniter\Database\BaseConnection $db, string $email, string $pesan): void
{
    sendNotifWa(resolveWaForEmail($db, $email), $pesan);
}

function sendNotifWaForUserId(\CodeIgniter\Database\BaseConnection $db, int $idUser, string $pesan): void
{
    sendNotifWa(resolveWaForUserId($db, $idUser), $pesan);
}

function buildNotifWaText(string $judul, string $isi, ?string $url = null): string
{
    $text = trim($judul) . "\n\n" . trim($isi);
    if ($url !== null && $url !== '') {
        $text .= "\n\n" . $url;
    }

    return $text;
}

/**
 * Wrapper HTML email notifikasi SIMENAK (konsisten antar template).
 */
function buildNotifEmailHtml(string $title, string $bodyHtml, ?string $ctaUrl = null, ?string $ctaLabel = null): string
{
    $ctaBlock = '';
    if ($ctaUrl !== null && $ctaUrl !== '' && $ctaLabel !== null && $ctaLabel !== '') {
        $ctaBlock = '<p style="margin:28px 0 0;">'
            . '<a href="' . esc($ctaUrl) . '" '
            . 'style="display:inline-block;background:#051747;color:#ffffff;text-decoration:none;'
            . 'font-weight:700;font-size:14px;padding:12px 28px;border-radius:999px;">'
            . esc($ctaLabel)
            . '</a></p>';
    }

    return '<div style="font-family:\'Segoe UI\',Arial,sans-serif;background:#F0F2F8;padding:32px 16px;">'
        . '<div style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #E2E8F0;border-radius:20px;overflow:hidden;">'
        . '<div style="background:#051747;padding:24px 28px;">'
        . '<p style="margin:0;font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:rgba(255,255,255,0.72);">SIMENAK Z\'Plack</p>'
        . '<h1 style="margin:8px 0 0;font-size:22px;font-weight:800;color:#ffffff;line-height:1.3;">' . esc($title) . '</h1>'
        . '</div>'
        . '<div style="padding:28px;color:#4A5568;font-size:14px;line-height:1.65;">'
        . $bodyHtml
        . $ctaBlock
        . '<p style="margin:28px 0 0;font-size:12px;color:#94A3B8;line-height:1.6;">'
        . 'Email ini dikirim otomatis oleh SIMENAK Z\'Plack. Jika Anda tidak merasa meminta email ini, abaikan pesan ini.'
        . '</p>'
        . '</div></div></div>';
}

function buildResetPasswordEmailHtml(string $nama, string $resetUrl): string
{
    $body = '<p>Halo <strong>' . esc($nama) . '</strong>,</p>'
        . '<p>Kami menerima permintaan untuk mengatur ulang kata sandi akun SIMENAK Z\'Plack Anda.</p>'
        . '<p>Klik tombol di bawah untuk membuat kata sandi baru. Link ini hanya berlaku selama <strong>30 menit</strong>.</p>'
        . '<p style="font-size:12px;color:#94A3B8;word-break:break-all;">'
        . 'Jika tombol tidak berfungsi, salin tautan berikut ke browser:<br>'
        . '<a href="' . esc($resetUrl) . '" style="color:#2E5CE6;">' . esc($resetUrl) . '</a>'
        . '</p>';

    return buildNotifEmailHtml('Reset Kata Sandi', $body, $resetUrl, 'Reset Kata Sandi');
}

/** Pesan generik forgot-password — mencegah enumerasi email. */
function forgotPasswordGenericMessage(): string
{
    return 'Permintaan berhasil diproses. Jika alamat email terdaftar, tautan untuk mengatur ulang kata sandi telah dikirim. Silakan periksa kotak masuk atau folder Spam.';
}

/** Apakah SMTP Gmail/kredensial sudah diisi di .env? */
function isSmtpConfigured(): bool
{
    $config = config('Email');

    if ((string) $config->protocol !== 'smtp') {
        return false;
    }

    if (trim((string) $config->SMTPHost) === '') {
        return false;
    }

    $host = strtolower(trim((string) $config->SMTPHost));

    // Mailpit / SMTP lokal tanpa auth
    if (in_array($host, ['127.0.0.1', 'localhost'], true)) {
        return true;
    }

    return trim((string) $config->SMTPUser) !== '' && trim((string) $config->SMTPPass) !== '';
}

function hashPasswordResetToken(string $plainToken): string
{
    return hash('sha256', $plainToken);
}

function generatePasswordResetToken(): string
{
    return bin2hex(random_bytes(32));
}

function getStatusLabel(string $status, bool $hasBuktiDp = false, ?string $viewerRole = null): string
{
    $role        = $viewerRole ?? (string) (session()->get('role') ?? '');
    $isPelanggan = $role === 'pelanggan';

    $labels = [
        'menunggu_konfirmasi_harga'     => 'Menunggu Konfirmasi Harga',
        'menunggu_konfirmasi_pelanggan' => $isPelanggan
            ? 'Menunggu Konfirmasi Anda'
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
        ];

        if (isPelunasanSebelumKirim($orderCtx)) {
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

    if (str_contains($j, 'custom') || str_contains($j, 'harga') || str_contains($j, 'konfirmasi')) {
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

/**
 * Anchor section di order/detail berdasarkan judul notifikasi (pelanggan).
 * Mapping via keyword judul — sama pola dengan getNotifIconMeta / getNotifActionUrl.
 */
function resolveNotifDetailAnchor(string $judul): string
{
    $j = mb_strtolower($judul);

    if (str_contains($j, 'kerja sama') || (str_contains($j, 'kerjasama') && str_contains($j, 'perusahaan'))) {
        return '';
    }

    // Dibatalkan / timeout harus ke timeline status, meski judul menyebut DP.
    if (str_contains($j, 'dibatalkan') || str_contains($j, 'timeout')) {
        return 'status-pesanan';
    }

    if (
        str_contains($j, 'dp')
        || str_contains($j, 'pelunasan')
        || str_contains($j, 'bayar')
        || str_contains($j, 'bukti')
    ) {
        return 'pembayaran';
    }

    if (
        str_contains($j, 'draft')
        || str_contains($j, 'revisi')
        || (str_contains($j, 'acc') && str_contains($j, 'desain'))
        || str_contains($j, 'desain di-acc')
        || str_contains($j, 'desain diacc')
    ) {
        return 'revisi-desain';
    }

    if (
        str_contains($j, 'siap dikirim')
        || str_contains($j, 'siap diambil')
        || str_contains($j, 'dikirim')
        || str_contains($j, 'resi')
        || str_contains($j, 'pengiriman')
        || (str_contains($j, 'diterima') && ! str_contains($j, 'pesanan selesai'))
        || (str_contains($j, 'diambil') && ! str_contains($j, 'pesanan selesai'))
    ) {
        return 'info-pengiriman';
    }

    return 'status-pesanan';
}

/** URL detail pesanan pelanggan + hash section sesuai judul notifikasi. */
function pelangganOrderDetailUrl(string $kodeOrder, string $judul = ''): string
{
    $url = site_url('order/detail/' . $kodeOrder);
    if ($kodeOrder === '' || $judul === '') {
        return $url;
    }

    $anchor = resolveNotifDetailAnchor($judul);
    if ($anchor === '') {
        return $url;
    }

    return $url . '#' . $anchor;
}

function getNotifActionUrl(
    string $role,
    ?string $kodeOrder,
    ?int $idOrder,
    string $judul
): ?string {
    $j = mb_strtolower($judul);

    if (str_contains($j, 'kerja sama') || (str_contains($j, 'kerjasama') && str_contains($j, 'perusahaan'))) {
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
        'pelanggan' => pelangganOrderDetailUrl($kodeOrder, $judul),
        'admin'     => $orderId > 0
            ? site_url('list-pemesanan/' . $orderId)
            : site_url('list-pemesanan'),
        'produksi'  => $orderId > 0
            ? site_url('manajemen-desain/' . $orderId)
            : site_url('dashboard'),
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

/**
 * Format tanggal/waktu untuk email pelanggan (contoh: 02 Sep 2026, 18:30).
 */
function formatEmailDatetime(?string $datetime): string
{
    if ($datetime === null || trim($datetime) === '') {
        return '';
    }

    $ts = strtotime($datetime);

    return $ts === false ? (string) $datetime : date('d M Y, H:i', $ts);
}

/**
 * Format tanggal saja untuk email (contoh: 08 Sep 2026).
 */
function formatEmailDate(?string $datetime): string
{
    if ($datetime === null || trim($datetime) === '') {
        return '';
    }

    $ts = strtotime($datetime);

    return $ts === false ? (string) $datetime : date('d M Y', $ts);
}

/**
 * Absolute URL gambar katalog untuk email; null jika kosong/file tidak ada.
 */
function resolveKatalogGambarEmailUrl(?string $gambar): ?string
{
    $gambarRaw = trim((string) $gambar);
    if ($gambarRaw === '') {
        return null;
    }

    $normalized = ltrim(str_replace('\\', '/', $gambarRaw), '/');

    if (str_starts_with($normalized, 'uploads/katalog/')) {
        $gambarRelPath = substr($normalized, strlen('uploads/katalog/'));
    } elseif (str_starts_with($normalized, 'katalog/')) {
        $gambarRelPath = substr($normalized, strlen('katalog/'));
    } else {
        $gambarRelPath = $normalized;
    }

    $gambarRelPath = ltrim($gambarRelPath, '/');
    if ($gambarRelPath === '' || ! is_file(FCPATH . 'uploads/katalog/' . $gambarRelPath)) {
        return null;
    }

    return base_url('uploads/katalog/' . $gambarRelPath);
}

/**
 * Absolute URL thumbnail draft desain; null jika kosong/file tidak ada.
 */
function resolveDraftGambarEmailUrl(?string $fileDraft): ?string
{
    $name = trim((string) $fileDraft);
    if ($name === '') {
        return null;
    }

    $name = ltrim(str_replace('\\', '/', $name), '/');
    if (str_starts_with($name, 'uploads/draft_desain/')) {
        $name = substr($name, strlen('uploads/draft_desain/'));
    }

    $name = ltrim($name, '/');
    if ($name === '' || ! is_file(FCPATH . 'uploads/draft_desain/' . $name)) {
        return null;
    }

    return base_url('uploads/draft_desain/' . $name);
}

/**
 * Label kategori katalog (sama mapping dengan order/detail).
 */
function emailKategoriLabel(string $kategoriKey): string
{
    return match ($kategoriKey) {
        'desain_grafis' => 'Desain Grafis',
        'cetak_digital' => 'Cetak Digital',
        'cetak_offset'  => 'Cetak Offset',
        'media_promosi' => 'Media Promosi',
        default         => $kategoriKey !== ''
            ? ucwords(str_replace('_', ' ', $kategoriKey))
            : '',
    };
}

/**
 * Subteks kartu produk di email — pola detail pesanan:
 * "Media Promosi Standar" (± " · 1 pcs").
 *
 * @param array<string, mixed> $order
 */
function buildEmailProdukSubteks(array $order): string
{
    $parts = [];
    $kategoriKey = trim((string) ($order['kategori'] ?? $order['nama_kategori'] ?? ''));
    $kategoriLabel = emailKategoriLabel($kategoriKey);

    if ($kategoriLabel !== '') {
        $jenisPesanan = ((int) ($order['is_custom'] ?? 0) === 1) ? 'Custom' : 'Standar';
        $parts[]      = $kategoriLabel . ' ' . $jenisPesanan;
    }

    $jumlah = (int) ($order['jumlah_order'] ?? 0);
    $satuan = trim((string) ($order['satuan'] ?? ''));
    if ($jumlah > 0) {
        $parts[] = $jumlah . ($satuan !== '' ? ' ' . $satuan : ' pcs');
    }

    return implode(' · ', $parts);
}

/**
 * Data bersama Rincian Pesanan untuk email pelanggan.
 *
 * @param array<string, mixed> $order
 * @return array<string, mixed>
 */
function buildEmailOrderViewData(array $order): array
{
    $deadlineRaw = (string) ($order['deadline_produksi'] ?? $order['deadline_diajukan'] ?? '');

    return [
        'kodeOrder'     => (string) ($order['kode_order'] ?? ''),
        'namaProduk'    => (string) ($order['nama_produk'] ?? 'Produk Custom'),
        'gambarUrl'     => resolveKatalogGambarEmailUrl($order['gambar_katalog'] ?? null),
        'produkSubteks' => buildEmailProdukSubteks($order),
        'tglOrderLabel' => formatEmailDatetime($order['created_at'] ?? null),
        'deadlineLabel' => formatEmailDate($deadlineRaw !== '' ? $deadlineRaw : null),
    ];
}

/**
 * Label metode pengiriman untuk email.
 */
function emailMetodePengirimanLabel(array $order): string
{
    return isMetodeAmbilSendiri($order) ? 'Ambil Sendiri' : 'Kurir';
}

/**
 * Highlight kode pesanan dengan accent brand #2E5CE6.
 */
function emailHighlightKodeOrder(string $kodeOrder): string
{
    return '<strong style="color:#2E5CE6;">' . esc($kodeOrder) . '</strong>';
}

/**
 * Render email HTML terstruktur pelanggan (Shopee-style).
 *
 * @param array<string, mixed> $data
 */
function renderNotifEmail(string $jenis, array $data): string
{
    $map = [
        'dp_terverifikasi'       => 'emails/dp_terverifikasi',
        'draft_siap'             => 'emails/draft_siap',
        'bukti_ditolak'          => 'emails/bukti_ditolak',
        'pelunasan_terverifikasi'=> 'emails/pelunasan_terverifikasi',
        'konfirmasi_harga_custom'=> 'emails/konfirmasi_harga_custom',
        'siap_pengiriman'        => 'emails/siap_pengiriman',
        'pesanan_selesai'        => 'emails/pesanan_selesai',
        'pesanan_dibatalkan'     => 'emails/pesanan_dibatalkan',
        'finishing'              => 'emails/finishing',
    ];

    if (! isset($map[$jenis])) {
        log_message('error', '[renderNotifEmail] jenis tidak dikenal: {jenis}', ['jenis' => $jenis]);

        return buildNotifEmailHtml(
            (string) ($data['title'] ?? 'Notifikasi'),
            (string) ($data['pesanHtml'] ?? '<p>Ada pembaruan pada pesanan Anda.</p>'),
            $data['ctaUrl'] ?? null,
            $data['ctaLabel'] ?? null
        );
    }

    $renderer = \Config\Services::renderer();
    $renderer->resetData();

    $html = view($map[$jenis], $data, ['saveData' => false, 'debug' => false]);

    // Hapus marker Debug Toolbar agar HTML email bersih.
    return (string) preg_replace('/<!-- DEBUG-VIEW (?:START|ENDED) .*?-->\s*/', '', $html);
}
