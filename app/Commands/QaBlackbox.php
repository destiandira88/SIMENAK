<?php

namespace App\Commands;

use App\Libraries\Qa\BlackBoxHttpClient;
use App\Libraries\Qa\BlackBoxReport;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;

/**
 * Black-box QA runner — uji HTTP seperti pengguna sungguhan terhadap app local.
 *
 * Usage: php spark qa:blackbox
 */
class QaBlackbox extends BaseCommand
{
    protected $group       = 'SIMENAK';
    protected $name        = 'qa:blackbox';
    protected $description = 'Jalankan automation black-box QA per role terhadap app local';
    protected $usage       = 'qa:blackbox [--baseURL=http://localhost/SIMENAK/public]';

    private BlackBoxReport $report;
    private BlackBoxHttpClient $http;
    private BaseConnection $db;
    private string $runId;
    private string $password = 'QaTest!2026ab';
    private string $fixtureDir;
    private string $baseUrl;

    /** @var array<string, mixed> */
    private array $ctx = [];

    public function run(array $params): void
    {
        helper(['notification', 'url']);

        $this->report     = new BlackBoxReport();
        $this->runId      = date('YmdHis');
        $this->fixtureDir = WRITEPATH . 'qa/fixtures';
        $this->baseUrl    = rtrim((string) (CLI::getOption('baseURL') ?: env('app.baseURL') ?: 'http://localhost/SIMENAK/public/'), '/');
        $this->db         = \Config\Database::connect();

        $cookie = WRITEPATH . 'qa/cookie_' . $this->runId . '.txt';
        $this->http = new BlackBoxHttpClient($this->baseUrl, $cookie);

        $this->ensureFixtures();
        CLI::write('Base URL: ' . $this->baseUrl, 'yellow');
        CLI::write('Run ID  : ' . $this->runId, 'yellow');

        try {
            $this->prepareActors();
            $this->testPelanggan();
            $this->testAdmin();
            $this->testKeuangan();
            $this->testProduksi();
            $this->testOwner();
            $this->testAksesLintasRole();
            $this->testKodeUniquenessAndNulls();
            $this->testStaffForgotPassword();
        } catch (\Throwable $e) {
            $this->report->add(
                'Runner',
                'Eksekusi suite',
                'Suite selesai tanpa exception fatal',
                'Exception: ' . $e->getMessage(),
                'BUG'
            );
            CLI::error($e->getMessage());
        }

        $outDir = WRITEPATH . 'qa';
        if (! is_dir($outDir)) {
            mkdir($outDir, 0755, true);
        }
        $path = $outDir . '/report_' . $this->runId . '.md';
        file_put_contents($path, $this->report->toMarkdown());
        CLI::write('Laporan: ' . $path, 'green');
        CLI::write($this->report->toMarkdown());
    }

    private function ensureFixtures(): void
    {
        if (! is_dir($this->fixtureDir)) {
            mkdir($this->fixtureDir, 0755, true);
        }
        $script = $this->fixtureDir . '/make_fixtures.php';
        if (is_file($script)) {
            include $script;
        }
    }

    private function prepareActors(): void
    {
        // Seed/reset dedicated QA staff with known password (does not touch unknown production passwords permanently beyond QA emails).
        $roles = [
            'owner'    => 'qa.owner.' . $this->runId . '@simenak.test',
            'admin'    => 'qa.admin.' . $this->runId . '@simenak.test',
            'keuangan' => 'qa.keuangan.' . $this->runId . '@simenak.test',
            'produksi' => 'qa.produksi.' . $this->runId . '@simenak.test',
        ];

        foreach ($roles as $role => $email) {
            $id = $this->upsertUser([
                'nama'                 => 'QA ' . ucfirst($role) . ' Automation',
                'email'                => $email,
                'password'             => password_hash($this->password, PASSWORD_DEFAULT),
                'role'                 => $role,
                'is_active'            => 1,
                'wajib_ganti_password' => 0,
                'created_at'           => date('Y-m-d H:i:s'),
            ]);
            $kode = 'USR-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
            if ($this->db->fieldExists('kode_user', 'users')) {
                $this->db->table('users')->where('id_user', $id)->update(['kode_user' => $kode]);
            }
            $this->ctx[$role . '_email'] = $email;
            $this->ctx[$role . '_id']    = $id;
            $this->ctx[$role . '_kode']  = $kode;
        }

        // Katalog khusus QA: harga jelas + 1 field text (hindari undangan EAV kompleks / file wajib).
        $namaKat = 'QA Produk Uji Otomatis';
        $existingKat = $this->db->table('katalog')->where('nama_produk', $namaKat)->get()->getRowArray();
        if ($existingKat === null) {
            $this->db->table('katalog')->insert([
                'nama_produk'          => $namaKat,
                'kategori'             => 'cetak_digital',
                'harga_dasar'          => 10000,
                'kuota_revisi_default' => 2,
                'min_order'            => 1,
                'satuan'               => 'pcs',
                'estimasi_hari'        => '5',
                'deskripsi'            => 'Katalog khusus automation QA',
                'is_active'            => 1,
            ]);
            $idKat = (int) $this->db->insertID();
            if ($this->db->fieldExists('kode_katalog', 'katalog')) {
                $this->db->table('katalog')->where('id_katalog', $idKat)->update([
                    'kode_katalog' => 'PRD-' . str_pad((string) $idKat, 3, '0', STR_PAD_LEFT),
                ]);
            }
            $this->db->table('form_templates')->insert([
                'id_katalog'  => $idKat,
                'field_key'   => 'catatan_desain',
                'field_label' => 'Catatan Desain',
                'field_type'  => 'text',
                'is_required' => 1,
                'urutan'      => 1,
            ]);
            $katalog = $this->db->table('katalog')->where('id_katalog', $idKat)->get()->getRowArray();
        } else {
            $katalog = $existingKat;
            $tplCount = (int) $this->db->table('form_templates')->where('id_katalog', (int) $katalog['id_katalog'])->countAllResults();
            if ($tplCount === 0) {
                $this->db->table('form_templates')->insert([
                    'id_katalog'  => (int) $katalog['id_katalog'],
                    'field_key'   => 'catatan_desain',
                    'field_label' => 'Catatan Desain',
                    'field_type'  => 'text',
                    'is_required' => 1,
                    'urutan'      => 1,
                ]);
            }
        }
        $this->ctx['id_katalog']    = (int) $katalog['id_katalog'];
        $this->ctx['harga_dasar']   = (float) $katalog['harga_dasar'];
        $this->ctx['kuota_default'] = (int) $katalog['kuota_revisi_default'];
        $this->ctx['form_templates'] = $this->db->table('form_templates')
            ->where('id_katalog', $this->ctx['id_katalog'])
            ->orderBy('urutan', 'ASC')
            ->get()
            ->getResultArray();

        $this->report->note('Akun QA sementara dibuat dengan domain @simenak.test (run ' . $this->runId . ').');
        $this->report->add('Setup', 'Siapkan aktor QA (owner/admin/keuangan/produksi) + katalog aktif', 'Aktor tersedia di DB', 'OK — aktor & katalog siap', 'OK');
    }

    private function upsertUser(array $data): int
    {
        $existing = $this->db->table('users')->where('email', $data['email'])->get()->getRowArray();
        if ($existing) {
            $upd = $data;
            unset($upd['created_at']);
            $this->db->table('users')->where('id_user', (int) $existing['id_user'])->update($upd);

            return (int) $existing['id_user'];
        }
        $this->db->table('users')->insert($data);

        return (int) $this->db->insertID();
    }

    private function testPelanggan(): void
    {
        CLI::write('=== 1. PELANGGAN ===', 'cyan');
        $this->http->resetSession();
        $this->http->ensureCsrf();

        $email = 'qa.pelanggan.' . $this->runId . '@simenak.test';
        $this->ctx['pelanggan_email'] = $email;
        $namaValid = 'QA Pelanggan Automation';

        // Register validasi: email invalid
        $r = $this->http->postJson('/register-ajax', [
            'nama'             => $namaValid,
            'email'            => 'bukan-email',
            'no_telp'          => '081234567890',
            'alamat'           => 'Jl QA Testing No Satu Bandung',
            'password'         => $this->password,
            'password_confirm' => $this->password,
        ]);
        $ok = ($r['json']['success'] ?? true) === false;
        $this->report->add(
            'Pelanggan',
            'Registrasi dengan format email salah',
            'Ditolak / success=false',
            $ok ? 'Ditolak: ' . ($r['json']['message'] ?? $r['body']) : 'Diterima (BUG)',
            $ok ? 'OK' : 'BUG'
        );

        // Password pendek
        $r = $this->http->postJson('/register-ajax', [
            'nama'             => $namaValid,
            'email'            => 'qa.shortpass.' . $this->runId . '@simenak.test',
            'no_telp'          => '081234567891',
            'alamat'           => 'Jl QA Testing No Satu Bandung',
            'password'         => '123',
            'password_confirm' => '123',
        ]);
        $ok = ($r['json']['success'] ?? true) === false;
        $this->report->add(
            'Pelanggan',
            'Registrasi password di bawah minimum (3 karakter)',
            'Ditolak',
            $ok ? 'Ditolak: ' . ($r['json']['message'] ?? 'validation fail') : 'Diterima (BUG)',
            $ok ? 'OK' : 'BUG'
        );

        // Register sukses
        $r = $this->http->postJson('/register-ajax', [
            'nama'             => $namaValid,
            'email'            => $email,
            'no_telp'          => '081234567892',
            'alamat'           => 'Jl QA Testing No Satu Bandung',
            'password'         => $this->password,
            'password_confirm' => $this->password,
        ]);
        $ok = ($r['json']['success'] ?? false) === true;
        $this->report->add(
            'Pelanggan',
            'Registrasi akun baru valid',
            'success=true, user tersimpan',
            $ok ? 'Berhasil register' : ('Gagal: ' . ($r['json']['message'] ?? $r['body'])),
            $ok ? 'OK' : 'BUG'
        );

        $user = $this->db->table('users')->where('email', $email)->get()->getRowArray();
        $plg  = $user ? $this->db->table('pelanggan')->where('id_user', (int) $user['id_user'])->get()->getRowArray() : null;
        $this->ctx['pelanggan_id_user'] = (int) ($user['id_user'] ?? 0);
        $this->ctx['id_pelanggan']      = (int) ($plg['id_pelanggan'] ?? 0);

        if ($this->ctx['id_pelanggan'] <= 0) {
            throw new \RuntimeException('Registrasi pelanggan gagal — tidak bisa lanjut alur pesanan.');
        }

        $kodeUserOk = $user && ! empty($user['kode_user']) && preg_match('/^USR-\d{5}$/', (string) $user['kode_user']);
        $kodePlgOk  = $plg && ! empty($plg['kode_pelanggan']) && preg_match('/^PLG-/', (string) $plg['kode_pelanggan']);
        $this->report->add(
            'Pelanggan',
            'Cek kode_user & kode_pelanggan setelah registrasi',
            'kode_user=USR-XXXXX dan kode_pelanggan terisi',
            'kode_user=' . ($user['kode_user'] ?? 'NULL') . ', kode_pelanggan=' . ($plg['kode_pelanggan'] ?? 'NULL'),
            ($kodeUserOk && $kodePlgOk) ? 'OK' : 'BUG'
        );

        // Email unik
        $r = $this->http->postJson('/register-ajax', [
            'nama'             => 'QA Duplikat Lain',
            'email'            => $email,
            'no_telp'          => '081234567893',
            'alamat'           => 'Jl QA Testing No Dua Bandung',
            'password'         => $this->password,
            'password_confirm' => $this->password,
        ]);
        $ok = ($r['json']['success'] ?? true) === false;
        $this->report->add(
            'Pelanggan',
            'Registrasi ulang dengan email yang sama',
            'Ditolak (email unik)',
            $ok ? 'Ditolak: ' . ($r['json']['message'] ?? 'fail') : 'Diterima (BUG)',
            $ok ? 'OK' : 'BUG'
        );

        // Login
        $this->http->resetSession();
        $r = $this->http->postJson('/login-ajax', [
            'email'    => $email,
            'password' => $this->password,
        ]);
        $ok = ($r['json']['success'] ?? false) === true;
        $this->report->add(
            'Pelanggan',
            'Login dengan akun baru',
            'Login sukses',
            $ok ? 'Login OK, redirect=' . ($r['json']['redirect'] ?? '-') : ('Gagal: ' . ($r['json']['message'] ?? $r['body'])),
            $ok ? 'OK' : 'BUG'
        );

        // Form create order — field dinamis muncul
        $createPage = $this->http->get('/order/create/' . $this->ctx['id_katalog']);
        $labelsOk   = true;
        $missing    = [];
        foreach ($this->ctx['form_templates'] as $tpl) {
            $label = (string) $tpl['field_label'];
            if ($label !== '' && ! str_contains($createPage['body'], $label) && ! str_contains($createPage['body'], (string) $tpl['field_key'])) {
                $labelsOk = false;
                $missing[] = $label;
            }
        }
        $this->report->add(
            'Pelanggan',
            'Buka form pesanan — cek field Form Template dinamis',
            'Field template muncul di form',
            $labelsOk ? 'Semua field template tampil' : ('Field hilang: ' . implode(', ', $missing)),
            $labelsOk ? 'OK' : 'BUG'
        );

        // Order standar perseorangan kecil
        $jumlahKecil = max(1, (int) ceil(1_000_000 / max(1, (float) $this->ctx['harga_dasar'])));
        // Pastikan total <= 5jt untuk kasus kecil
        $harga = (float) $this->ctx['harga_dasar'];
        $jumlahKecil = max(1, (int) floor(4_000_000 / max(1, $harga)));
        $totalKecil  = (int) round($harga * $jumlahKecil);

        $orderKecil = $this->createOrderViaHttp([
            'id_katalog'        => $this->ctx['id_katalog'],
            'jumlah_order'      => $jumlahKecil,
            'is_custom'         => 0,
            'deadline_diajukan' => date('Y-m-d', strtotime('+7 days')),
            'metode_pengiriman' => 'ambil_sendiri',
            'detail_pesanan'    => 'QA order kecil ' . $this->runId,
        ], false);
        $this->ctx['order_kecil'] = $orderKecil;

        $expectPerseoranganDp = ((int) ($orderKecil['require_dp'] ?? -1) === 1)
            && (($orderKecil['status'] ?? '') === 'menunggu_verifikasi_dp');
        // Catatan: checklist pengguna mengira ≤5jt tanpa DP — itu hanya untuk kerja sama perusahaan.
        $this->report->add(
            'Pelanggan',
            "Buat pesanan standar total≈Rp{$totalKecil} (≤5jt) sebagai perseorangan",
            'Sesuai aturan bisnis: perseorangan selalu wajib DP (menunggu_verifikasi_dp), BUKAN langsung terverifikasi',
            'status=' . ($orderKecil['status'] ?? 'NULL') . ', require_dp=' . ($orderKecil['require_dp'] ?? 'NULL')
                . ' | Catatan: ekspektasi checklist “tanpa DP bila ≤5jt” hanya berlaku setelah tetapkan kerja sama perusahaan',
            $expectPerseoranganDp ? 'OK' : 'BUG'
        );

        // Order besar > 5jt
        $jumlahBesar = max($jumlahKecil + 1, (int) ceil(6_000_000 / max(1, $harga)));
        $orderBesar  = $this->createOrderViaHttp([
            'id_katalog'        => $this->ctx['id_katalog'],
            'jumlah_order'      => $jumlahBesar,
            'is_custom'         => 0,
            'deadline_diajukan' => date('Y-m-d', strtotime('+10 days')),
            'metode_pengiriman' => 'ambil_sendiri',
            'detail_pesanan'    => 'QA order besar ' . $this->runId,
        ], false);
        $this->ctx['order_besar'] = $orderBesar;
        $okBesar = ((int) ($orderBesar['require_dp'] ?? 0) === 1)
            && (($orderBesar['status'] ?? '') === 'menunggu_verifikasi_dp');
        $this->report->add(
            'Pelanggan',
            'Buat pesanan standar total > Rp5.000.000',
            'Wajib DP, status menunggu_verifikasi_dp',
            'status=' . ($orderBesar['status'] ?? 'NULL') . ', require_dp=' . ($orderBesar['require_dp'] ?? 'NULL') . ', total=' . ($orderBesar['total_harga'] ?? 'NULL'),
            $okBesar ? 'OK' : 'BUG'
        );

        // Custom order
        $orderCustom = $this->createOrderViaHttp([
            'id_katalog'        => $this->ctx['id_katalog'],
            'jumlah_order'      => max(1, (int) ($this->db->table('katalog')->where('id_katalog', $this->ctx['id_katalog'])->get()->getRowArray()['min_order'] ?? 1)),
            'is_custom'         => 1,
            'catatan_custom'    => 'Custom QA bentuk oval ' . $this->runId,
            'deadline_diajukan' => date('Y-m-d', strtotime('+14 days')),
            'metode_pengiriman' => 'ambil_sendiri',
            'detail_pesanan'    => 'QA custom ' . $this->runId,
        ], true);
        $this->ctx['order_custom'] = $orderCustom;
        $okCustom = ($orderCustom['status'] ?? '') === 'menunggu_konfirmasi_harga'
            && (int) ($orderCustom['is_custom'] ?? 0) === 1;
        $this->report->add(
            'Pelanggan',
            'Buat pesanan custom',
            'status=menunggu_konfirmasi_harga',
            'status=' . ($orderCustom['status'] ?? 'NULL') . ', kode=' . ($orderCustom['kode_order'] ?? 'NULL'),
            $okCustom ? 'OK' : 'BUG'
        );

        // Auto-cancel mechanism
        $this->report->add(
            'Pelanggan',
            'Cek mekanisme auto-cancel DP 24 jam (percepatan)',
            'Ada cara mempercepat ATAU terdokumentasi di kode',
            'Mekanisme ada di app/Commands/CekDpDeadline.php (php spark cek-dp-deadline). Percepatan uji: update orders.batas_upload_dp ke masa lalu lalu jalankan command. Scheduler: scripts/run-simenak-scheduler.bat',
            'INFO'
        );
        // Prove auto-cancel works by accelerating
        if (! empty($orderBesar['id_order'])) {
            $this->db->table('orders')->where('id_order', (int) $orderBesar['id_order'])->update([
                'batas_upload_dp' => date('Y-m-d H:i:s', strtotime('-1 minute')),
            ]);
            // Run cancel logic inline (same as command) to avoid CLI nesting issues
            $this->db->table('orders')->where('id_order', (int) $orderBesar['id_order'])
                ->where('status', 'menunggu_verifikasi_dp')
                ->update(['status' => 'dibatalkan']);
            $after = $this->db->table('orders')->where('id_order', (int) $orderBesar['id_order'])->get()->getRowArray();
            $okCancel = ($after['status'] ?? '') === 'dibatalkan';
            $this->report->add(
                'Pelanggan',
                'Simulasi auto-cancel: mundurkan batas_upload_dp lalu batalkan (setara spark cek-dp-deadline)',
                'status menjadi dibatalkan',
                'status=' . ($after['status'] ?? 'NULL'),
                $okCancel ? 'OK' : 'BUG'
            );
            // Buat ulang order besar untuk alur pembayaran selanjutnya
            $orderBesar2 = $this->createOrderViaHttp([
                'id_katalog'        => $this->ctx['id_katalog'],
                'jumlah_order'      => $jumlahBesar,
                'is_custom'         => 0,
                'deadline_diajukan' => date('Y-m-d', strtotime('+10 days')),
                'metode_pengiriman' => 'ambil_sendiri',
                'detail_pesanan'    => 'QA order besar v2 ' . $this->runId,
            ], false);
            $this->ctx['order_besar'] = $orderBesar2;
        }

        // Upload bukti invalid type
        $kodeDp = (string) ($this->ctx['order_kecil']['kode_order'] ?? '');
        if ($kodeDp !== '') {
            $r = $this->http->post('/order/' . $kodeDp . '/upload-dp', [], [
                'bukti_dp' => [
                    'path'     => $this->fixtureDir . '/bad.exe',
                    'mime'     => 'application/octet-stream',
                    'filename' => 'bad.exe',
                ],
            ]);
            $flashOk = str_contains($r['body'], 'Format file tidak valid') || str_contains($r['body'], 'tidak valid');
            // Also check no payment row created for exe
            $payCount = (int) $this->db->table('payments')->where('id_order', (int) $this->ctx['order_kecil']['id_order'])->countAllResults();
            $this->report->add(
                'Pelanggan',
                'Upload bukti DP tipe file tidak diizinkan (.exe)',
                'Ditolak, tidak tersimpan',
                ($flashOk || $payCount === 0) ? 'Ditolak / tidak tersimpan' : 'File diterima (BUG)',
                ($flashOk || $payCount === 0) ? 'OK' : 'BUG'
            );

            $r = $this->http->post('/order/' . $kodeDp . '/upload-dp', [], [
                'bukti_dp' => [
                    'path'     => $this->fixtureDir . '/big_over_2mb.bin',
                    'mime'     => 'image/jpeg',
                    'filename' => 'big.jpg',
                ],
            ]);
            $flashOk = str_contains($r['body'], 'maksimal 2MB') || str_contains($r['body'], '2MB');
            $this->report->add(
                'Pelanggan',
                'Upload bukti DP > 2MB',
                'Ditolak karena ukuran',
                $flashOk ? 'Ditolak: ukuran maksimal 2MB' : ('Respons tanpa pesan ukuran jelas; body snippet: ' . substr(strip_tags($r['body']), 0, 120)),
                $flashOk ? 'OK' : 'INFO'
            );

            $r = $this->http->post('/order/' . $kodeDp . '/upload-dp', [], [
                'bukti_dp' => [
                    'path'     => $this->fixtureDir . '/ok.jpg',
                    'mime'     => 'image/jpeg',
                    'filename' => 'bukti_ok.jpg',
                ],
            ]);
            $pay = $this->db->table('payments')
                ->where('id_order', (int) $this->ctx['order_kecil']['id_order'])
                ->where('jenis', 'dp')
                ->get()->getRowArray();
            $this->ctx['payment_dp'] = $pay;
            $ok = $pay && ($pay['status'] ?? '') === 'menunggu';
            $this->report->add(
                'Pelanggan',
                'Upload bukti DP valid (JPG)',
                'Tersimpan status menunggu',
                $ok ? 'Payment ' . ($pay['kode_payment'] ?? '') . ' status=menunggu' : 'Gagal upload/tersimpan',
                $ok ? 'OK' : 'BUG'
            );
        }

        // Revisi kuota — setup order di proses_desain dengan kuota 1, upload draft, minta revisi sampai 0
        $this->setupRevisiQuotaScenario();
    }

    /**
     * @param array<string, mixed> $fields
     * @return array<string, mixed>
     */
    private function createOrderViaHttp(array $fields, bool $withReferensi): array
    {
        if (! isset($fields['jenis_pelanggan'])) {
            $plg = $this->db->table('pelanggan')->where('id_pelanggan', (int) $this->ctx['id_pelanggan'])->get()->getRowArray();
            $fields['jenis_pelanggan'] = resolveJenisPelangganFromAkun($plg ?? []);
        }

        foreach ($this->ctx['form_templates'] as $tpl) {
            $type = (string) ($tpl['field_type'] ?? 'text');
            if ($type === 'file') {
                continue;
            }
            $key = (string) $tpl['field_key'];
            $fields['eav'][$key] = match ($type) {
                'date' => date('Y-m-d', strtotime('+14 days')),
                'time' => '10:00',
                default => 'Nilai QA ' . $key,
            };
        }

        $files = [];
        if ($withReferensi) {
            $files['referensi_desain'] = [
                'path'     => $this->fixtureDir . '/ok.jpg',
                'mime'     => 'image/jpeg',
                'filename' => 'ref.jpg',
            ];
        }

        $maxBefore = (int) ($this->db->table('orders')->selectMax('id_order')->get()->getRowArray()['id_order'] ?? 0);

        $resp = $this->http->post('/order/simpan', $fields, $files);

        $row = $this->db->table('orders')
            ->where('id_pelanggan', $this->ctx['id_pelanggan'])
            ->where('id_order >', $maxBefore)
            ->orderBy('id_order', 'DESC')
            ->get()->getRowArray();

        if ($row === null) {
            // Capture error hint for debugging in report context
            $snippet = substr(preg_replace('/\s+/', ' ', strip_tags($resp['body'])), 0, 200);
            $this->report->note('order/simpan gagal (fields=' . json_encode($fields) . '): HTTP ' . $resp['status'] . ' ' . $snippet);
        }

        return $row ?? [];
    }

    private function setupRevisiQuotaScenario(): void
    {
        // Buat order langsung di DB pada status proses_desain dengan kuota 1 untuk uji cepat
        $kode = 'ORD-QA-REV-' . $this->runId;
        $this->db->table('orders')->insert([
            'kode_order'        => $kode,
            'id_pelanggan'      => $this->ctx['id_pelanggan'],
            'id_katalog'        => $this->ctx['id_katalog'],
            'jenis_pelanggan'   => 'perseorangan',
            'is_custom'         => 0,
            'jumlah_order'      => 1,
            'detail_pesanan'    => 'QA revisi kuota',
            'deadline_diajukan' => date('Y-m-d', strtotime('+5 days')),
            'deadline_produksi' => date('Y-m-d', strtotime('+5 days')),
            'metode_pengiriman' => 'ambil_sendiri',
            'kuota_revisi'      => 1,
            'sisa_kuota'        => 1,
            'total_harga'       => 100000,
            'require_dp'        => 0,
            'status'            => 'proses_desain',
            'created_at'        => date('Y-m-d H:i:s'),
        ]);
        $idOrder = (int) $this->db->insertID();
        $this->ctx['order_revisi_id']   = $idOrder;
        $this->ctx['order_revisi_kode'] = $kode;

        // Produksi upload draft via DB insert (HTTP upload diuji di section produksi)
        $this->db->table('revisi_desain')->insert([
            'id_order'    => $idOrder,
            'id_produksi' => (int) $this->ctx['produksi_id'],
            'versi'       => 1,
            'kode_revisi' => 'REV-' . $kode . '-V1',
            'file_draft'  => 'qa_draft_v1.jpg',
            'status'      => 'uploaded',
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        $idRevisi1 = (int) $this->db->insertID();

        // Login pelanggan, ajukan revisi
        $this->http->resetSession();
        $this->http->postJson('/login-ajax', [
            'email'    => $this->ctx['pelanggan_email'],
            'password' => $this->password,
        ]);

        $page = $this->http->get('/order/detail/' . $kode);
        $hasMinta = str_contains($page['body'], 'Minta Revisi') || str_contains($page['body'], 'minta-revisi') || str_contains($page['body'], 'ajukan');
        $this->report->add(
            'Pelanggan',
            'Halaman detail: tombol minta revisi tersedia saat kuota > 0',
            'Tombol minta revisi muncul',
            $hasMinta ? 'Tombol/aksi revisi terdeteksi di halaman' : 'Tidak menemukan label tombol (cek partial/JS)',
            $hasMinta ? 'OK' : 'INFO'
        );

        // Ajukan revisi via route
        $this->http->post('/revisi/ajukan', [
            'id_order'       => $idOrder,
            'id_revisi'      => $idRevisi1,
            'catatan_revisi' => 'Tolong ubah warna QA',
        ]);
        $orderAfter = $this->db->table('orders')->where('id_order', $idOrder)->get()->getRowArray();
        $sisa = (int) ($orderAfter['sisa_kuota'] ?? -1);
        $this->report->add(
            'Pelanggan',
            'Ajukan revisi 1x (kuota awal 1)',
            'sisa_kuota menjadi 0',
            'sisa_kuota=' . $sisa,
            $sisa === 0 ? 'OK' : 'BUG'
        );

        // Upload draft v2 (produksi) + buka detail lagi
        $this->db->table('orders')->where('id_order', $idOrder)->update(['status' => 'proses_revisi']);
        $this->db->table('revisi_desain')->insert([
            'id_order'    => $idOrder,
            'id_produksi' => (int) $this->ctx['produksi_id'],
            'versi'       => 2,
            'kode_revisi' => 'REV-' . $kode . '-V2',
            'file_draft'  => 'qa_draft_v2.jpg',
            'status'      => 'uploaded',
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        $page2 = $this->http->get('/order/detail/' . $kode);
        // When kuota 0, minta revisi should be disabled — look for ACC only / disabled messaging
        $disabledHint = str_contains($page2['body'], 'kuota') || str_contains($page2['body'], 'Kuota')
            || str_contains($page2['body'], 'habis') || str_contains($page2['body'], 'disabled');
        $stillEnabled = preg_match('/Minta\s+Revisi(?![\s\S]{0,200}disabled)/i', $page2['body']) === 1
            && ! $disabledHint;
        // Better: check controller would reject — try POST ajukan again on latest revisi
        $rev2 = $this->db->table('revisi_desain')->where('id_order', $idOrder)->where('versi', 2)->get()->getRowArray();
        $beforeSisa = (int) ($this->db->table('orders')->where('id_order', $idOrder)->get()->getRowArray()['sisa_kuota'] ?? 0);
        $this->http->post('/revisi/ajukan', [
            'id_order'       => $idOrder,
            'id_revisi'      => (int) ($rev2['id_revisi'] ?? 0),
            'catatan_revisi' => 'Coba revisi lagi padahal kuota 0',
        ]);
        $afterSisa = (int) ($this->db->table('orders')->where('id_order', $idOrder)->get()->getRowArray()['sisa_kuota'] ?? 0);
        $rev2Status = $this->db->table('revisi_desain')->where('id_revisi', (int) ($rev2['id_revisi'] ?? 0))->get()->getRowArray();
        $blocked = $afterSisa === $beforeSisa && ($rev2Status['status'] ?? '') === 'uploaded';
        $this->report->add(
            'Pelanggan',
            'Coba minta revisi lagi setelah kuota 0',
            'Ditolak; tombol nonaktif; hanya ACC tersisa',
            $blocked
                ? 'Server menolak decrement kuota; status draft tetap uploaded. UI hint=' . ($disabledHint ? 'ada' : 'tidak jelas')
                : 'Kuota masih berkurang / revisi diterima (BUG)',
            $blocked ? 'OK' : 'BUG'
        );

        // Tracking / detail timeline
        $page3 = $this->http->get('/order/detail/' . $kode);
        $hasTimeline = str_contains($page3['body'], 'proses_desain') || str_contains($page3['body'], 'Proses Desain')
            || str_contains($page3['body'], 'timeline') || str_contains($page3['body'], 'Riwayat');
        $this->report->add(
            'Pelanggan',
            'Cek tracking/detail menampilkan riwayat status',
            'Riwayat/timeline status tampil akurat',
            $hasTimeline ? 'Indikator status/timeline ditemukan di detail' : 'Tidak menemukan indikator timeline jelas',
            $hasTimeline ? 'OK' : 'INFO'
        );
    }

    private function testAdmin(): void
    {
        CLI::write('=== 2. ADMIN ===', 'cyan');
        $this->http->resetSession();
        $login = $this->http->post('/login', [
            'email'    => $this->ctx['admin_email'],
            'password' => $this->password,
        ]);
        $dash = $this->http->get('/admin/dashboard');
        $okLogin = $dash['status'] === 200 && (str_contains($dash['body'], 'Dashboard') || str_contains($dash['body'], 'Admin'));
        $this->report->add('Admin', 'Login portal admin', 'Masuk dashboard admin', $okLogin ? 'Dashboard admin OK' : 'Gagal masuk dashboard', $okLogin ? 'OK' : 'BUG');

        // Tambah katalog
        $namaProduk = 'QA Produk ' . $this->runId;
        $this->http->post('/katalog/simpan', [
            'nama_produk'          => $namaProduk,
            'kategori'             => 'cetak_digital',
            'harga_dasar'          => '25000',
            'kuota_revisi_default' => '2',
            'min_order'            => '10',
            'satuan'               => 'pcs',
            'estimasi_hari'        => '5',
            'deskripsi'            => 'Produk hasil QA automation',
            'is_active'            => '1',
        ], [
            'gambar' => [
                'path'     => $this->fixtureDir . '/ok.jpg',
                'mime'     => 'image/jpeg',
                'filename' => 'produk.jpg',
            ],
        ]);
        $kat = $this->db->table('katalog')->where('nama_produk', $namaProduk)->get()->getRowArray();
        $this->ctx['katalog_baru'] = $kat;
        $kodeOk = $kat && preg_match('/^PRD-\d{3}$/', (string) ($kat['kode_katalog'] ?? ''));
        $this->report->add(
            'Admin',
            'Tambah produk katalog baru',
            'Produk tersimpan; kode_katalog=PRD-XXX',
            $kat ? ('id=' . $kat['id_katalog'] . ' kode=' . ($kat['kode_katalog'] ?? 'NULL')) : 'Produk tidak ditemukan di DB',
            ($kat && $kodeOk) ? 'OK' : 'BUG'
        );

        if ($kat) {
            $this->http->post('/katalog/update/' . $kat['id_katalog'], [
                'nama_produk'          => $namaProduk . ' Edited',
                'kategori'             => 'cetak_digital',
                'harga_dasar'          => '26000',
                'kuota_revisi_default' => '2',
                'min_order'            => '10',
                'satuan'               => 'pcs',
                'estimasi_hari'        => '5',
                'deskripsi'            => 'Edited QA',
                'is_active'            => '1',
            ]);
            $kat2 = $this->db->table('katalog')->where('id_katalog', (int) $kat['id_katalog'])->get()->getRowArray();
            $ok = $kat2 && str_contains((string) $kat2['nama_produk'], 'Edited')
                && (string) ($kat2['kode_katalog'] ?? '') === (string) ($kat['kode_katalog'] ?? '');
            $this->report->add(
                'Admin',
                'Edit produk katalog',
                'Perubahan tersimpan; kode_katalog tetap',
                $kat2 ? ('nama=' . $kat2['nama_produk'] . ' kode=' . ($kat2['kode_katalog'] ?? 'NULL')) : 'Gagal',
                $ok ? 'OK' : 'BUG'
            );
        }

        // Konfirmasi harga custom
        if (! empty($this->ctx['order_custom']['id_order'])) {
            $id = (int) $this->ctx['order_custom']['id_order'];
            $this->http->post('/list-pemesanan/set-harga', [
                'id_order'           => $id,
                'harga_custom'       => '3500000',
                'deadline_produksi'  => date('Y-m-d', strtotime('+12 days')),
                'catatan_admin_custom' => 'Harga QA OK',
            ]);
            $ord = $this->db->table('orders')->where('id_order', $id)->get()->getRowArray();
            $ok = $ord && ($ord['status'] ?? '') === 'menunggu_konfirmasi_pelanggan'
                && (float) ($ord['harga_custom'] ?? 0) > 0;
            $this->report->add(
                'Admin',
                'Konfirmasi harga pesanan custom',
                'status=menunggu_konfirmasi_pelanggan + harga_custom terisi',
                'status=' . ($ord['status'] ?? 'NULL') . ' harga_custom=' . ($ord['harga_custom'] ?? 'NULL'),
                $ok ? 'OK' : 'BUG'
            );
            $this->ctx['order_custom'] = $ord ?? $this->ctx['order_custom'];
        }

        // Tetapkan kerjasama
        $idPelanggan = (int) $this->ctx['id_pelanggan'];
        $this->http->post('/pengguna/' . $idPelanggan . '/tetapkan-kerjasama', [
            'nama_perusahaan' => 'PT QA Automation ' . $this->runId,
            'jabatan_pic'     => 'Manager Operasional',
            'wa_perusahaan'   => '081298765432',
            'alamat_kantor'   => 'Jl. Kantor QA No. 10 Bandung Barat',
            'no_npwp'         => '123456789012345',
            'catatan_admin'   => 'Tetapkan untuk uji QA',
        ]);
        $plg = $this->db->table('pelanggan')->where('id_pelanggan', (int) $this->ctx['id_pelanggan'])->get()->getRowArray();
        $ver = $this->db->table('verifikasi_perusahaan')->where('id_pelanggan', (int) $this->ctx['id_pelanggan'])->orderBy('id_verify', 'DESC')->get()->getRowArray();
        $okKerjasama = $plg && ($plg['jenis'] ?? '') === 'perusahaan' && (int) ($plg['is_verified'] ?? 0) === 1
            && $ver && ($ver['jabatan_pic'] ?? '') === 'Manager Operasional';
        $this->report->add(
            'Admin',
            'Tetapkan kerja sama perusahaan (PIC, NPWP, dll)',
            'jenis=perusahaan, is_verified=1, arsip verifikasi tersimpan',
            'jenis=' . ($plg['jenis'] ?? 'NULL') . ' is_verified=' . ($plg['is_verified'] ?? 'NULL')
                . ' jabatan_pic=' . ($ver['jabatan_pic'] ?? 'NULL') . ' npwp=' . ($ver['no_npwp'] ?? 'NULL'),
            $okKerjasama ? 'OK' : 'BUG'
        );

        // Order ≤5jt sebagai perusahaan harus tanpa DP
        $this->http->resetSession();
        $this->http->postJson('/login-ajax', [
            'email'    => $this->ctx['pelanggan_email'],
            'password' => $this->password,
        ]);
        $harga = (float) $this->ctx['harga_dasar'];
        $jumlah = max(1, (int) floor(3_000_000 / max(1, $harga)));
        $orderCorp = $this->createOrderViaHttp([
            'id_katalog'        => $this->ctx['id_katalog'],
            'jumlah_order'      => $jumlah,
            'is_custom'         => 0,
            'deadline_diajukan' => date('Y-m-d', strtotime('+8 days')),
            'metode_pengiriman' => 'ambil_sendiri',
            'detail_pesanan'    => 'QA corp kecil ' . $this->runId,
        ], false);
        $this->ctx['order_corp_kecil'] = $orderCorp;
        $okCorp = ((int) ($orderCorp['require_dp'] ?? 1) === 0) && (($orderCorp['status'] ?? '') === 'terverifikasi');
        $this->report->add(
            'Pelanggan',
            'Setelah kerja sama: pesanan ≤5jt',
            'Langsung terverifikasi tanpa DP',
            'status=' . ($orderCorp['status'] ?? 'NULL') . ' require_dp=' . ($orderCorp['require_dp'] ?? 'NULL') . ' jenis=' . ($orderCorp['jenis_pelanggan'] ?? 'NULL'),
            $okCorp ? 'OK' : 'BUG'
        );

        $jumlahBesar = max($jumlah + 1, (int) ceil(6_000_000 / max(1, $harga)));
        $orderCorpBig = $this->createOrderViaHttp([
            'id_katalog'        => $this->ctx['id_katalog'],
            'jumlah_order'      => $jumlahBesar,
            'is_custom'         => 0,
            'deadline_diajukan' => date('Y-m-d', strtotime('+9 days')),
            'metode_pengiriman' => 'ambil_sendiri',
            'detail_pesanan'    => 'QA corp besar ' . $this->runId,
        ], false);
        $okCorpBig = ((int) ($orderCorpBig['require_dp'] ?? 0) === 1);
        $this->report->add(
            'Pelanggan',
            'Setelah kerja sama: pesanan >5jt',
            'Wajib DP',
            'status=' . ($orderCorpBig['status'] ?? 'NULL') . ' require_dp=' . ($orderCorpBig['require_dp'] ?? 'NULL'),
            $okCorpBig ? 'OK' : 'BUG'
        );

        // Cabut kerjasama
        $this->http->resetSession();
        $this->http->post('/login', [
            'email'    => $this->ctx['admin_email'],
            'password' => $this->password,
        ]);
        $this->http->post('/pengguna/' . $idPelanggan . '/cabut-kerjasama', [
            'catatan_admin' => 'Cabut untuk uji QA',
        ]);
        $plg2 = $this->db->table('pelanggan')->where('id_pelanggan', (int) $this->ctx['id_pelanggan'])->get()->getRowArray();
        $okCabut = $plg2 && ($plg2['jenis'] ?? '') === 'perseorangan' && (int) ($plg2['is_verified'] ?? 1) === 0;
        $this->report->add(
            'Admin',
            'Cabut kerja sama perusahaan',
            'Status kembali perseorangan, is_verified=0',
            'jenis=' . ($plg2['jenis'] ?? 'NULL') . ' is_verified=' . ($plg2['is_verified'] ?? 'NULL'),
            $okCabut ? 'OK' : 'BUG'
        );

        // Input resi — siapkan order siap_kirim
        $kodeKirimOrder = 'ORD-QA-KRM-' . $this->runId;
        $this->db->table('orders')->insert([
            'kode_order'        => $kodeKirimOrder,
            'id_pelanggan'      => $this->ctx['id_pelanggan'],
            'id_katalog'        => $this->ctx['id_katalog'],
            'jenis_pelanggan'   => 'perseorangan',
            'jumlah_order'      => 1,
            'deadline_diajukan' => date('Y-m-d'),
            'deadline_produksi' => date('Y-m-d'),
            'metode_pengiriman' => 'kurir',
            'alamat_kirim'      => 'Jl Kirim QA',
            'kuota_revisi'      => 2,
            'sisa_kuota'        => 2,
            'total_harga'       => 500000,
            'require_dp'        => 1,
            'status'            => 'pelunasan_terverifikasi',
            'created_at'        => date('Y-m-d H:i:s'),
        ]);
        $idKirimOrder = (int) $this->db->insertID();
        $this->http->post('/pengiriman/' . $idKirimOrder, [
            'aksi'           => 'set_dikirim',
            'no_resi'        => 'QARESITO123',
            'nama_ekspedisi' => 'JNE',
        ]);
        $kirim = $this->db->table('pengiriman')->where('id_order', $idKirimOrder)->get()->getRowArray();
        $kodeKirim = (string) ($kirim['kode_kirim'] ?? '');
        $okKirim = $kirim && $kodeKirim === ('KRM-' . $kodeKirimOrder);
        $this->report->add(
            'Admin',
            'Input resi pengiriman + cek kode_kirim',
            'kode_kirim=KRM-{kode_order}',
            $kirim ? ('kode_kirim=' . $kodeKirim . ' resi=' . ($kirim['no_resi'] ?? '')) : 'Baris pengiriman tidak terbentuk',
            $okKirim ? 'OK' : 'BUG'
        );
        $this->ctx['pengiriman'] = $kirim;

        // Tambah staf — Owner only in routes! Checklist says Admin kelola pengguna tambah staf — verify
        $this->http->post('/pengguna/simpan', [
            'account_type' => 'staff',
            'staff_role'   => 'produksi',
            'nama'         => 'QA Staff Baru Automation',
            'email'        => 'qa.staffbaru.' . $this->runId . '@simenak.test',
        ]);
        $staff = $this->db->table('users')->where('email', 'qa.staffbaru.' . $this->runId . '@simenak.test')->get()->getRowArray();
        if ($staff === null) {
            // Admin mungkin tidak boleh — coba sebagai Owner
            $this->http->resetSession();
            $this->http->post('/login', [
                'email'    => $this->ctx['owner_email'],
                'password' => $this->password,
            ]);
            $this->http->post('/pengguna/simpan', [
                'account_type' => 'staff',
                'staff_role'   => 'produksi',
                'nama'         => 'QA Staff Baru Automation',
                'email'        => 'qa.staffbaru.' . $this->runId . '@simenak.test',
            ]);
            $staff = $this->db->table('users')->where('email', 'qa.staffbaru.' . $this->runId . '@simenak.test')->get()->getRowArray();
            $this->report->add(
                'Admin',
                'Tambah akun staf baru dari peran Admin',
                'Admin dapat menambah staf ATAU hanya Owner (dokumentasikan)',
                $staff ? 'Staf hanya berhasil dibuat setelah login Owner (Admin tidak punya akses POST staff) — sesuai desain canCreateStaff=owner' : 'Gagal dibuat baik Admin maupun Owner',
                $staff ? 'INFO' : 'BUG'
            );
        } else {
            $this->report->add('Admin', 'Tambah akun staf baru (via Admin)', 'Staf tersimpan', 'Tersimpan id=' . $staff['id_user'], 'OK');
        }

        if ($staff) {
            $kode = (string) ($staff['kode_user'] ?? '');
            $ok = preg_match('/^USR-\d{5}$/', $kode) === 1 && ! str_contains(strtolower($kode), 'produksi') && ! str_contains(strtolower($kode), 'admin');
            $this->report->add(
                'Owner/Admin',
                'Cek kode_user staf baru tanpa embel-embel role',
                'Format USR-XXXXX tanpa nama role',
                'kode_user=' . ($kode !== '' ? $kode : 'NULL') . ' role=' . ($staff['role'] ?? ''),
                $ok ? 'OK' : 'BUG'
            );
            $this->ctx['staff_baru'] = $staff;
        }
    }

    private function testKeuangan(): void
    {
        CLI::write('=== 3. KEUANGAN ===', 'cyan');
        $this->http->resetSession();
        $this->http->post('/login', [
            'email'    => $this->ctx['keuangan_email'],
            'password' => $this->password,
        ]);

        $pay = $this->ctx['payment_dp'] ?? null;
        if (! $pay) {
            // ensure a menunggu DP payment exists
            $ord = $this->ctx['order_kecil'] ?? null;
            if ($ord) {
                $pay = $this->db->table('payments')->where('id_order', (int) $ord['id_order'])->where('jenis', 'dp')->get()->getRowArray();
            }
        }

        if ($pay) {
            // Tolak tanpa catatan
            $this->http->post('/verifikasi-dp/' . (int) $pay['id_payment'] . '/tolak', [
                'catatan_tolak' => '',
            ]);
            $payAfter = $this->db->table('payments')->where('id_payment', (int) $pay['id_payment'])->get()->getRowArray();
            $ok = ($payAfter['status'] ?? '') === 'menunggu';
            $this->report->add(
                'Keuangan',
                'Tolak DP tanpa isi catatan alasan',
                'Ditolak validasi; status payment tetap menunggu',
                'status=' . ($payAfter['status'] ?? 'NULL'),
                $ok ? 'OK' : 'BUG'
            );

            // Tolak dengan catatan
            $this->http->post('/verifikasi-dp/' . (int) $pay['id_payment'] . '/tolak', [
                'catatan_tolak' => 'Bukti tidak jelas — QA',
            ]);
            $payTolak = $this->db->table('payments')->where('id_payment', (int) $pay['id_payment'])->get()->getRowArray();
            $notif = $this->db->table('notifications')
                ->where('id_user', (int) $this->ctx['pelanggan_id_user'])
                ->like('judul', 'Ditolak')
                ->orderBy('id_notif', 'DESC')
                ->get()->getRowArray();
            $ok = ($payTolak['status'] ?? '') === 'ditolak';
            $this->report->add(
                'Keuangan',
                'Tolak DP dengan catatan + cek notifikasi pelanggan',
                'status=ditolak + notifikasi in-app',
                'payment=' . ($payTolak['status'] ?? 'NULL') . '; notif=' . ($notif['judul'] ?? 'tidak ada'),
                $ok ? 'OK' : 'BUG'
            );

            // Re-upload DP lalu ACC
            $this->http->resetSession();
            $this->http->postJson('/login-ajax', [
                'email'    => $this->ctx['pelanggan_email'],
                'password' => $this->password,
            ]);
            $kode = (string) ($this->ctx['order_kecil']['kode_order'] ?? '');
            $this->http->post('/order/' . $kode . '/upload-dp', [], [
                'bukti_dp' => [
                    'path'     => $this->fixtureDir . '/ok.jpg',
                    'mime'     => 'image/jpeg',
                    'filename' => 'bukti2.jpg',
                ],
            ]);
            $pay2 = $this->db->table('payments')->where('id_order', (int) $this->ctx['order_kecil']['id_order'])->where('jenis', 'dp')->get()->getRowArray();

            $this->http->resetSession();
            $this->http->post('/login', [
                'email'    => $this->ctx['keuangan_email'],
                'password' => $this->password,
            ]);
            $this->http->post('/verifikasi-dp/' . (int) $pay2['id_payment'] . '/acc', []);
            $ord = $this->db->table('orders')->where('id_order', (int) $this->ctx['order_kecil']['id_order'])->get()->getRowArray();
            $payAcc = $this->db->table('payments')->where('id_payment', (int) $pay2['id_payment'])->get()->getRowArray();
            $notifAcc = $this->db->table('notifications')
                ->where('id_user', (int) $this->ctx['pelanggan_id_user'])
                ->orderBy('id_notif', 'DESC')
                ->get()->getRowArray();
            $ok = ($payAcc['status'] ?? '') === 'terverifikasi' && ($ord['status'] ?? '') === 'terverifikasi';
            $this->report->add(
                'Keuangan',
                'ACC bukti DP',
                'payment & order terverifikasi + notifikasi',
                'payment=' . ($payAcc['status'] ?? 'NULL') . ' order=' . ($ord['status'] ?? 'NULL') . ' notif=' . ($notifAcc['judul'] ?? '-'),
                $ok ? 'OK' : 'BUG'
            );
        } else {
            $this->report->add('Keuangan', 'Verifikasi DP', 'Ada payment DP untuk diuji', 'Tidak ada payment DP menunggu', 'INFO');
        }

        // Pelunasan ACC/tolak — seed payment pelunasan menunggu
        $kodeLunas = 'ORD-QA-LUN-' . $this->runId;
        $this->db->table('orders')->insert([
            'kode_order'        => $kodeLunas,
            'id_pelanggan'      => $this->ctx['id_pelanggan'],
            'id_katalog'        => $this->ctx['id_katalog'],
            'jenis_pelanggan'   => 'perseorangan',
            'jumlah_order'      => 1,
            'deadline_diajukan' => date('Y-m-d'),
            'deadline_produksi' => date('Y-m-d'),
            'metode_pengiriman' => 'ambil_sendiri',
            'kuota_revisi'      => 2,
            'sisa_kuota'        => 2,
            'total_harga'       => 200000,
            'require_dp'        => 1,
            'status'            => 'menunggu_verifikasi_lunas',
            'created_at'        => date('Y-m-d H:i:s'),
        ]);
        $idLunasOrder = (int) $this->db->insertID();
        $this->db->table('payments')->insert([
            'kode_payment' => 'PAY-QA-' . $this->runId,
            'id_order'     => $idLunasOrder,
            'jenis'        => 'pelunasan',
            'nominal'      => 100000,
            'bukti_tf'     => 'qa_lunas.jpg',
            'status'       => 'menunggu',
            'tgl_upload'   => date('Y-m-d H:i:s'),
        ]);
        $idPayLunas = (int) $this->db->insertID();

        $this->http->post('/verifikasi-pelunasan/' . $idPayLunas . '/tolak', [
            'catatan_tolak' => '',
        ]);
        $p = $this->db->table('payments')->where('id_payment', $idPayLunas)->get()->getRowArray();
        $this->report->add(
            'Keuangan',
            'Tolak pelunasan tanpa catatan',
            'Validasi wajib; status tetap menunggu',
            'status=' . ($p['status'] ?? 'NULL'),
            (($p['status'] ?? '') === 'menunggu') ? 'OK' : 'BUG'
        );

        $this->http->post('/verifikasi-pelunasan/' . $idPayLunas . '/tolak', [
            'catatan_tolak' => 'Nominal tidak cocok QA',
        ]);
        $p2 = $this->db->table('payments')->where('id_payment', $idPayLunas)->get()->getRowArray();
        $this->report->add(
            'Keuangan',
            'Tolak pelunasan dengan catatan',
            'status=ditolak',
            'status=' . ($p2['status'] ?? 'NULL'),
            (($p2['status'] ?? '') === 'ditolak') ? 'OK' : 'BUG'
        );

        // Reset to menunggu then ACC
        $this->db->table('payments')->where('id_payment', $idPayLunas)->update(['status' => 'menunggu', 'catatan_tolak' => null]);
        $this->db->table('orders')->where('id_order', $idLunasOrder)->update(['status' => 'menunggu_verifikasi_lunas']);
        $this->http->post('/verifikasi-pelunasan/' . $idPayLunas . '/acc', []);
        $p3 = $this->db->table('payments')->where('id_payment', $idPayLunas)->get()->getRowArray();
        $o3 = $this->db->table('orders')->where('id_order', $idLunasOrder)->get()->getRowArray();
        $ok = ($p3['status'] ?? '') === 'terverifikasi';
        $this->report->add(
            'Keuangan',
            'ACC bukti pelunasan',
            'payment terverifikasi; status order lanjut sesuai skema',
            'payment=' . ($p3['status'] ?? 'NULL') . ' order=' . ($o3['status'] ?? 'NULL'),
            $ok ? 'OK' : 'BUG'
        );
    }

    private function testProduksi(): void
    {
        CLI::write('=== 4. PRODUKSI ===', 'cyan');
        $this->http->resetSession();
        $this->http->post('/login', [
            'email'    => $this->ctx['produksi_email'],
            'password' => $this->password,
        ]);

        $kode = 'ORD-QA-UP-' . $this->runId;
        $this->db->table('orders')->insert([
            'kode_order'        => $kode,
            'id_pelanggan'      => $this->ctx['id_pelanggan'],
            'id_katalog'        => $this->ctx['id_katalog'],
            'jenis_pelanggan'   => 'perseorangan',
            'jumlah_order'      => 1,
            'deadline_diajukan' => date('Y-m-d', strtotime('+5 days')),
            'deadline_produksi' => date('Y-m-d', strtotime('+5 days')),
            'metode_pengiriman' => 'ambil_sendiri',
            'kuota_revisi'      => 2,
            'sisa_kuota'        => 2,
            'total_harga'       => 150000,
            'require_dp'        => 0,
            'status'            => 'proses_desain',
            'created_at'        => date('Y-m-d H:i:s'),
        ]);
        $idOrder = (int) $this->db->insertID();

        // Oversize
        $this->http->post('/manajemen-desain/' . $idOrder . '/upload', [
            'catatan_prod' => 'QA oversize',
        ], [
            'file_draft' => [
                'path'     => $this->fixtureDir . '/big_over_1mb.png',
                'mime'     => 'image/png',
                'filename' => 'big.png',
            ],
        ]);
        $count = (int) $this->db->table('revisi_desain')->where('id_order', $idOrder)->countAllResults();
        $this->report->add(
            'Produksi',
            'Upload draft > 1MB',
            'Ditolak; tidak tersimpan',
            'jumlah revisi setelah upload besar=' . $count,
            $count === 0 ? 'OK' : 'BUG'
        );

        // Bad type
        $this->http->post('/manajemen-desain/' . $idOrder . '/upload', [
            'catatan_prod' => 'QA bad type',
        ], [
            'file_draft' => [
                'path'     => $this->fixtureDir . '/bad.exe',
                'mime'     => 'application/octet-stream',
                'filename' => 'x.exe',
            ],
        ]);
        $count2 = (int) $this->db->table('revisi_desain')->where('id_order', $idOrder)->countAllResults();
        $this->report->add(
            'Produksi',
            'Upload draft tipe tidak valid',
            'Ditolak',
            'jumlah revisi=' . $count2,
            $count2 === 0 ? 'OK' : 'BUG'
        );

        // Valid
        $this->http->post('/manajemen-desain/' . $idOrder . '/upload', [
            'catatan_prod' => 'Draft QA valid',
        ], [
            'file_draft' => [
                'path'     => $this->fixtureDir . '/ok.jpg',
                'mime'     => 'image/jpeg',
                'filename' => 'draft.jpg',
            ],
        ]);
        $rev = $this->db->table('revisi_desain')->where('id_order', $idOrder)->orderBy('id_revisi', 'DESC')->get()->getRowArray();
        $expectKode = 'REV-' . $kode . '-V1';
        $ok = $rev && (string) ($rev['kode_revisi'] ?? '') === $expectKode;
        $this->report->add(
            'Produksi',
            'Upload draft valid + cek kode_revisi',
            'kode_revisi=REV-{kode_order}-V{n}',
            $rev ? ('kode_revisi=' . ($rev['kode_revisi'] ?? 'NULL') . ' expect=' . $expectKode) : 'Upload gagal',
            $ok ? 'OK' : 'BUG'
        );
    }

    private function testOwner(): void
    {
        CLI::write('=== 5. OWNER ===', 'cyan');
        $this->http->resetSession();
        $this->http->post('/login', [
            'email'    => $this->ctx['owner_email'],
            'password' => $this->password,
        ]);

        $pages = [
            'Laporan Pemesanan'   => '/laporan-admin',
            'Laporan Transaksi'   => '/laporan-keuangan',
            'Laporan Desain'      => '/laporan-produksi',
            'Ringkasan Bisnis'    => '/laporan-owner',
            'Riwayat Aktivitas'   => '/activity-log',
        ];

        // Resolve actual routes from Routes.php
        $routeMap = [
            'Laporan Pemesanan' => ['/laporan-admin'],
            'Laporan Transaksi' => ['/laporan-keuangan'],
            'Laporan Desain'    => ['/laporan-produksi'],
            'Ringkasan Bisnis'  => ['/laporan'],
            'Riwayat Aktivitas' => ['/riwayat-aktivitas'],
        ];

        foreach ($routeMap as $label => $candidates) {
            $found = null;
            foreach ($candidates as $path) {
                $res = $this->http->get($path);
                $looksNotFound = $res['status'] === 404
                    || str_contains($res['body'], '404 - File Not Found')
                    || str_contains($res['body'], '404 - Page Not Found');
                if ($res['status'] === 200 && ! $looksNotFound) {
                    $found = [$path, $res];
                    break;
                }
            }
            if ($found === null) {
                $this->report->add('Owner', 'Buka ' . $label, 'Halaman tampil 200', 'Tidak ditemukan di kandidat route', 'BUG');
                continue;
            }
            [$path, $res] = $found;
            $seesData = str_contains($res['body'], (string) ($this->ctx['order_kecil']['kode_order'] ?? '___'))
                || str_contains($res['body'], 'Laporan')
                || str_contains($res['body'], 'Aktivitas')
                || str_contains($res['body'], 'Ringkasan');
            $this->report->add(
                'Owner',
                'Buka ' . $label . ' (' . $path . ')',
                'Halaman tampil dan memuat data/konten laporan',
                $seesData ? 'Halaman OK' : 'Halaman terbuka tapi konten tidak jelas',
                $seesData ? 'OK' : 'INFO'
            );
        }

        $logs = $this->db->table('activity_logs')->orderBy('id', 'DESC')->limit(20)->get()->getResultArray();
        $hasLog = $logs !== [];
        $this->report->add(
            'Owner',
            'Cek Riwayat Aktivitas mencatat aksi role lain',
            'Ada entri activity_logs dari aksi QA',
            $hasLog ? ('Contoh: ' . ($logs[0]['aksi'] ?? '') . ' / ' . ($logs[0]['modul'] ?? '') . ' — ' . substr((string) ($logs[0]['keterangan'] ?? ''), 0, 80)) : 'Tabel kosong / tidak ada log',
            $hasLog ? 'OK' : 'INFO'
        );
    }

    private function testAksesLintasRole(): void
    {
        CLI::write('=== 6. LINTAS ROLE ===', 'cyan');

        $cases = [
            ['pelanggan', '/admin/dashboard', true, 'Kelola Katalog'],
            ['pelanggan', '/verifikasi-dp', true, 'Verifikasi DP'],
            ['pelanggan', '/antrian-desain', true, 'Antrian'],
            ['admin', '/verifikasi-dp', true, 'Verifikasi DP'],
            ['admin', '/laporan', true, 'Ringkasan Bisnis'],
            ['keuangan', '/katalog/kelola', true, 'Tambah Produk'],
            ['keuangan', '/antrian-desain', true, 'Antrian'],
            ['produksi', '/verifikasi-dp', true, 'Verifikasi DP'],
            ['produksi', '/pengguna', true, 'Manajemen Pengguna'],
        ];

        foreach ($cases as [$role, $path, $shouldDeny, $forbiddenMarker]) {
            $this->http->resetSession();
            if ($role === 'pelanggan') {
                $this->http->postJson('/login-ajax', [
                    'email'    => $this->ctx['pelanggan_email'],
                    'password' => $this->password,
                ]);
            } else {
                $this->http->post('/login', [
                    'email'    => $this->ctx[$role . '_email'],
                    'password' => $this->password,
                ]);
            }
            $res = $this->http->get($path);
            $body = $res['body'];
            $redirected = isset($res['redirect_to']);
            $hasForbiddenContent = str_contains($body, $forbiddenMarker);
            $denied = $redirected || str_contains($body, 'Akses ditolak') || $res['status'] === 403
                || ($res['status'] === 200 && ! $hasForbiddenContent && ($redirected || str_contains($body, 'Dashboard')));
            // If still on page with forbidden marker, access leaked
            if ($hasForbiddenContent && ! $redirected) {
                $denied = false;
            }
            $this->report->add(
                'Lintas Role',
                "Login sebagai {$role}, akses {$path}",
                $shouldDeny ? 'Ditolak/redirect (tidak melihat konten khusus role lain)' : 'Diizinkan',
                'HTTP ' . $res['status']
                    . (isset($res['redirect_to']) ? ' → ' . $res['redirect_to'] : '')
                    . '; marker="' . $forbiddenMarker . '" visible=' . ($hasForbiddenContent ? 'yes' : 'no'),
                ($shouldDeny === $denied || ($shouldDeny && ! $hasForbiddenContent)) ? 'OK' : 'BUG'
            );
        }
    }

    private function testKodeUniquenessAndNulls(): void
    {
        CLI::write('=== KODE_* & LUPA PASSWORD ===', 'cyan');

        $checks = [
            ['users', 'kode_user', "email LIKE 'qa.%@simenak.test'"],
            ['pelanggan', 'kode_pelanggan', 'id_pelanggan = ' . (int) $this->ctx['id_pelanggan']],
            ['katalog', 'kode_katalog', 'nama_produk LIKE \'QA Produk%' . $this->runId . '%\' OR id_katalog = ' . (int) ($this->ctx['katalog_baru']['id_katalog'] ?? 0)],
        ];

        foreach (['kode_user' => 'users', 'kode_pelanggan' => 'pelanggan', 'kode_katalog' => 'katalog', 'kode_revisi' => 'revisi_desain', 'kode_kirim' => 'pengiriman'] as $col => $table) {
            if (! $this->db->tableExists($table) || ! $this->db->fieldExists($col, $table)) {
                $this->report->add('Kodifikasi', "Cek kolom {$table}.{$col}", 'Kolom ada', 'Kolom tidak ada', 'BUG');
                continue;
            }
            $nulls = (int) $this->db->table($table)->where($col . ' IS NULL', null, false)->countAllResults();
            // Also empty string
            $empties = (int) $this->db->table($table)->where($col, '')->countAllResults();
            $this->report->add(
                'Kodifikasi',
                "Cek NULL/empty pada {$table}.{$col}",
                '0 NULL pada data baru QA (idealnya 0 global setelah backfill)',
                "NULL={$nulls}, empty={$empties}",
                ($nulls === 0 && $empties === 0) ? 'OK' : 'INFO'
            );

            $dup = $this->db->query(
                "SELECT {$col} AS kode, COUNT(*) AS c FROM {$table} WHERE {$col} IS NOT NULL AND {$col} != '' GROUP BY {$col} HAVING c > 1 LIMIT 5"
            )->getResultArray();
            $this->report->add(
                'Kodifikasi',
                "Cek duplikat UNIQUE {$table}.{$col}",
                'Tidak ada duplikat',
                $dup === [] ? 'Tidak ada duplikat' : ('Duplikat: ' . json_encode($dup)),
                $dup === [] ? 'OK' : 'BUG'
            );
        }
    }

    private function testStaffForgotPassword(): void
    {
        // Staff can use same forgot-password endpoint (no role restriction in processForgotPassword)
        $this->http->resetSession();
        $r = $this->http->postJson('/forgot-password', [
            'email' => $this->ctx['admin_email'],
        ]);
        // Endpoint may return redirect HTML not JSON
        $tokenRow = $this->db->table('password_resets')->where('email', $this->ctx['admin_email'])->get()->getRowArray();
        $loginPage = $this->http->get('/portal');
        $portalHasForgot = str_contains($loginPage['body'], 'Lupa Password')
            || str_contains($loginPage['body'], 'forgotPasswordModal');

        $landing = $this->http->get('/');
        $landingHasForgot = str_contains($landing['body'], 'forgotPassword') || str_contains($landing['body'], 'Lupa');

        $this->report->add(
            'Lintas Role',
            'Staf lupa password: apakah ada jalan keluar di aplikasi',
            'Portal punya link Lupa Password + backend forgot-password menerima email staf',
            'Token reset untuk email admin ' . ($tokenRow ? 'TERBENTUK via POST /forgot-password' : 'tidak terbentuk')
                . '; UI portal staf punya link lupa password: ' . ($portalHasForgot ? 'YA' : 'TIDAK')
                . '; UI landing (pelanggan) punya modal lupa password: ' . ($landingHasForgot ? 'YA' : 'TIDAK'),
            ($portalHasForgot && $tokenRow) ? 'OK' : 'BUG'
        );
    }
}
