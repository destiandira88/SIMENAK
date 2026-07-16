<?php

namespace App\Controllers;

use Config\GoogleOAuth;
use League\OAuth2\Client\Provider\Google;

class AuthController extends BaseController
{
    protected $helpers = ['form', 'url', 'notification'];

    public function index()
    {
        return redirect()->to('/');
    }

    public function login()
    {
        if (session()->get('isLoggedIn')) {
            $role = (string) session()->get('role');

            if ($role === 'pelanggan') {
                return redirect()->to(site_url('/'));
            }

            return redirect()->to($this->dashboardPathForRole($role));
        }

        return view('auth/login');
    }

    public function loginProcess()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email dan password wajib diisi dengan benar.');
        }

        $result = $this->attemptLogin(
            (string) $this->request->getPost('email'),
            (string) $this->request->getPost('password')
        );

        if (!$result['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', $result['message']);
        }

        if ($result['role'] === 'pelanggan') {
            session()->destroy();

            return redirect()->to(site_url('/'))
                ->with('error', 'Akun pelanggan silakan masuk melalui halaman utama.');
        }

        return redirect()->to($result['redirect']);
    }

    public function loginAjax()
    {
        if (!$this->request->is('post')) {
            return $this->jsonResponse(false, 'Metode tidak diizinkan.', [], 405);
        }

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->jsonResponse(false, 'Email dan password wajib diisi dengan benar.', [
                'errors' => $this->validator->getErrors(),
            ], 422);
        }

        $result = $this->attemptLogin(
            (string) $this->request->getPost('email'),
            (string) $this->request->getPost('password')
        );

        if (!$result['success']) {
            return $this->jsonResponse(false, $result['message'], [], 401);
        }

        if ($this->isInternalRole($result['role'])) {
            session()->destroy();

            return $this->jsonResponse(false, 'Akun internal silakan masuk melalui Portal Login.', [
                'redirect' => site_url('login'),
            ], 403);
        }

        return $this->jsonResponse(true, 'Login berhasil.', [
            'redirect' => $result['redirect'],
            'role'     => $result['role'],
            'nama'     => $result['nama'],
        ]);
    }

    public function redirectToGoogle()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(site_url('/'));
        }

        /** @var GoogleOAuth $googleConfig */
        $googleConfig = config('GoogleOAuth');

        if (! $googleConfig->isConfigured()) {
            return redirect()->to(site_url('/'))
                ->with('error', 'Login Google belum dikonfigurasi. Hubungi administrator.')
                ->with('open_modal', 'loginModal');
        }

        try {
            $provider = $this->googleProvider($googleConfig);
            $authUrl  = $provider->getAuthorizationUrl([
                'scope' => ['openid', 'email', 'profile'],
            ]);

            session()->set('oauth2state', $provider->getState());

            return redirect()->to($authUrl);
        } catch (\Throwable $e) {
            log_message('error', 'Google OAuth redirect failed: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('/'))
                ->with('error', 'Login Google gagal dimulai. Silakan coba lagi.')
                ->with('open_modal', 'loginModal');
        }
    }

    public function googleCallback()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(site_url('/'));
        }

        if ($this->request->getGet('error') !== null) {
            return redirect()->to(site_url('/'))
                ->with('error', 'Login Google dibatalkan.')
                ->with('open_modal', 'loginModal');
        }

        /** @var GoogleOAuth $googleConfig */
        $googleConfig = config('GoogleOAuth');

        if (! $googleConfig->isConfigured()) {
            return redirect()->to(site_url('/'))
                ->with('error', 'Login Google belum dikonfigurasi.')
                ->with('open_modal', 'loginModal');
        }

        $expectedState = (string) session()->get('oauth2state');
        session()->remove('oauth2state');

        $state = (string) $this->request->getGet('state');
        $code  = (string) $this->request->getGet('code');

        if ($expectedState === '' || $state === '' || ! hash_equals($expectedState, $state)) {
            return redirect()->to(site_url('/'))
                ->with('error', 'Sesi login Google tidak valid. Silakan coba lagi.')
                ->with('open_modal', 'loginModal');
        }

        if ($code === '') {
            return redirect()->to(site_url('/'))
                ->with('error', 'Kode otorisasi Google tidak ditemukan.')
                ->with('open_modal', 'loginModal');
        }

        try {
            $provider   = $this->googleProvider($googleConfig);
            $accessToken = $provider->getAccessToken('authorization_code', ['code' => $code]);
            $googleUser = $provider->getResourceOwner($accessToken);
        } catch (\Throwable $e) {
            log_message('error', 'Google OAuth callback failed: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('/'))
                ->with('error', 'Login Google gagal. Silakan coba lagi.')
                ->with('open_modal', 'loginModal');
        }

        $email    = strtolower(trim((string) $googleUser->getEmail()));
        $googleId = trim((string) $googleUser->getId());

        if ($email === '' || $googleId === '') {
            return redirect()->to(site_url('/'))
                ->with('error', 'Data akun Google tidak lengkap.')
                ->with('open_modal', 'loginModal');
        }

        try {
            $db   = \Config\Database::connect();
            $user = $db->table('users')
                ->where('email', $email)
                ->get()
                ->getRowArray();

            if ($user === null) {
                return redirect()->to(site_url('/'))
                    ->with('error', 'Akun belum terdaftar. Silakan daftar terlebih dahulu.')
                    ->with('open_modal', 'registerModal');
            }

            if ((string) ($user['role'] ?? '') !== 'pelanggan') {
                return redirect()->to(site_url('/'))
                    ->with('error', 'Akun internal silakan masuk melalui Portal Login.')
                    ->with('open_modal', 'loginModal');
            }

            if (array_key_exists('is_active', $user) && (int) $user['is_active'] !== 1) {
                return redirect()->to(site_url('/'))
                    ->with('error', 'Akun nonaktif. Hubungi administrator.')
                    ->with('open_modal', 'loginModal');
            }

            if (
                $db->fieldExists('google_id', 'users')
                && ! empty($user['google_id'])
                && (string) $user['google_id'] !== $googleId
            ) {
                return redirect()->to(site_url('/'))
                    ->with('error', 'Akun Google tidak cocok dengan data terdaftar.')
                    ->with('open_modal', 'loginModal');
            }

            if ($db->fieldExists('google_id', 'users')) {
                $db->table('users')
                    ->where('id_user', (int) $user['id_user'])
                    ->update(['google_id' => $googleId]);
            }

            $sessionInfo = $this->establishUserSession($user);

            return redirect()->to($sessionInfo['redirect'])
                ->with('success', 'Login dengan Google berhasil.');
        } catch (\Throwable $e) {
            log_message('error', 'Google OAuth session error: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('/'))
                ->with('error', 'Terjadi kesalahan saat login Google. Silakan coba lagi.')
                ->with('open_modal', 'loginModal');
        }
    }

    public function register()
    {
        if (session()->get('isLoggedIn')) {
            $role = (string) session()->get('role');

            if ($role === 'pelanggan') {
                return redirect()->to(site_url('/'));
            }

            return redirect()->to($this->dashboardPathForRole($role));
        }

        return redirect()->to('/');
    }

    public function registerProcess()
    {
        $result = $this->attemptRegister();

        if (!$result['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', $result['message']);
        }

        return redirect()->to('/')
            ->with('success', $result['message']);
    }

    public function registerAjax()
    {
        try {
            if (!$this->request->is('post')) {
                return $this->jsonResponse(false, 'Metode tidak diizinkan.', [], 405);
            }

            $result = $this->attemptRegister();

            if (!$result['success']) {
                $message = trim((string) ($result['message'] ?? ''));
                if ($message === '') {
                    $message = 'Registrasi gagal. Periksa kembali semua field wajib.';
                }

                return $this->jsonResponse(false, $message, [
                    'errors' => $result['errors'] ?? [],
                ], 422);
            }

            return $this->jsonResponse(true, $result['message']);
        } catch (\Throwable $e) {
            log_message('error', 'registerAjax: {message}', ['message' => $e->getMessage()]);

            $message = 'Terjadi kesalahan server saat registrasi. Silakan coba lagi.';
            if (
                $e instanceof \CodeIgniter\Database\Exceptions\DatabaseException
                || str_contains($e->getMessage(), 'Unable to connect to the database')
            ) {
                $message = $this->databaseUnavailableMessage($e);
            }

            return $this->jsonResponse(false, $message, [], 500);
        }
    }

    public function checkSession()
    {
        $isLoggedIn = (bool) session()->get('isLoggedIn');
        $role       = (string) session()->get('role');
        $nama       = (string) session()->get('nama');

        $data = [
            'isLoggedIn' => $isLoggedIn,
            'role'       => $isLoggedIn ? $role : null,
            'nama'       => $isLoggedIn ? $nama : null,
        ];

        if ($isLoggedIn) {
            $data['redirect'] = $this->dashboardPathForRole($role);
        }

        return $this->response->setJSON($data);
    }

    /** Token CSRF segar untuk sinkronisasi form/AJAX tanpa refresh halaman penuh. */
    public function csrfSync()
    {
        return $this->response->setJSON([
            'tokenName' => config('Security')->tokenName,
            'token'     => csrf_hash(),
        ]);
    }

    public function logout()
    {
        $wasInternal = $this->isInternalRole((string) session()->get('role'));

        session()->destroy();

        $redirectTo = $wasInternal ? site_url('login') : site_url('/');

        if ($this->request->isAJAX()) {
            return $this->jsonResponse(true, 'Anda telah keluar dari akun.', [
                'redirect' => $redirectTo,
            ]);
        }

        return redirect()->to($redirectTo)
            ->with('success', 'Anda telah keluar dari akun.');
    }

    public function showForgotPasswordForm()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }

        return redirect()->to(site_url('/?open=forgotPasswordModal'));
    }

    public function processForgotPassword()
    {
        if (!$this->request->is('post')) {
            return $this->respondForgotPassword(false, 'Metode tidak diizinkan.', 405);
        }

        $rules = [
            'email' => 'required|valid_email',
        ];

        if (!$this->validate($rules)) {
            return $this->respondForgotPassword(false, 'Format email tidak valid.', 422);
        }

        $email   = trim((string) $this->request->getPost('email'));
        $message = forgotPasswordGenericMessage();
        $emailSent = null;

        try {
            $db   = \Config\Database::connect();
            $user = $db->table('users')
                ->where('email', $email)
                ->get()
                ->getRowArray();

            if ($user !== null) {
                $userEmail  = (string) $user['email'];
                $plainToken = generatePasswordResetToken();
                $tokenHash  = hashPasswordResetToken($plainToken);
                $expiredAt  = date('Y-m-d H:i:s', time() + (30 * 60));

                $db->table('password_resets')->where('email', $userEmail)->delete();

                $db->table('password_resets')->insert([
                    'email'      => $userEmail,
                    'token'      => $tokenHash,
                    'expired_at' => $expiredAt,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                $resetUrl = site_url('atur-ulang-sandi?token=' . urlencode($plainToken));

                try {
                    $emailSent = sendNotifEmail(
                        $userEmail,
                        'Reset Kata Sandi-SIMENAK Z\'Plack',
                        buildResetPasswordEmailHtml((string) $user['nama'], $resetUrl)
                    );
                    sendNotifWaForEmail(
                        $db,
                        $userEmail,
                        buildNotifWaText(
                            'Reset Kata Sandi SIMENAK',
                            'Permintaan reset kata sandi diterima. Gunakan link berikut (berlaku 1 jam).',
                            $resetUrl
                        )
                    );
                } catch (\Throwable $mailError) {
                    $emailSent = false;
                    log_message('error', 'Forgot password email gagal: {message}', [
                        'message' => $mailError->getMessage(),
                        'email'   => $userEmail,
                    ]);
                }

                if ($emailSent === false) {
                    log_message('error', 'Forgot password: email tidak terkirim ke {email}. Periksa SMTP di .env', [
                        'email' => $userEmail,
                    ]);

                    if (ENVIRONMENT === 'development') {
                        log_message('info', '[DEV RESET LINK] {email} => {url}', [
                            'email' => $userEmail,
                            'url'   => $resetUrl,
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'processForgotPassword: {message}', ['message' => $e->getMessage()]);
        }

        return $this->respondForgotPassword(true, $message, 200, $emailSent);
    }

    /**
     * JSON untuk AJAX lama, redirect untuk submit form biasa (lebih andal di browser).
     *
     * @param bool|null $emailSent null = email tidak dicoba (email tidak terdaftar), true/false = hasil kirim
     */
    private function respondForgotPassword(bool $success, string $message, int $status = 200, ?bool $emailSent = null)
    {
        if ($this->request->isAJAX()) {
            return $this->jsonResponse($success, $message, [], $success ? 200 : $status);
        }

        $redirect = redirect()->to(site_url('/?open=forgotPasswordModal'));

        if ($success) {
            if (ENVIRONMENT === 'development' && $emailSent === false) {
                $message .= ' Catatan dev: email belum terkirim — jalankan scripts\\setup-gmail-smtp.bat dan isi Gmail App Password.';
            }

            return $redirect->with('forgot_password_notice', $message);
        }

        return $redirect->with('forgot_password_error', $message);
    }

    public function showResetPasswordForm()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }

        $token = trim((string) $this->request->getGet('token'));
        if ($token === '') {
            return redirect()->to('/')
                ->with('error', 'Link reset password tidak valid.')
                ->with('open_modal', 'forgotPasswordModal');
        }

        if (!$this->findValidPasswordReset($token)) {
            return redirect()->to('/')
                ->with('error', 'Link reset password tidak valid atau sudah kedaluwarsa. Silakan minta link baru.')
                ->with('open_modal', 'forgotPasswordModal');
        }

        return redirect()->to(site_url('/?reset_token=' . rawurlencode($token)));
    }

    public function processResetPassword()
    {
        if (!$this->request->is('post')) {
            return $this->jsonResponse(false, 'Metode tidak diizinkan.', [], 405);
        }

        $rules = [
            'token'            => 'required',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        $messages = [
            'password' => [
                'required'   => 'Kata sandi wajib diisi.',
                'min_length' => 'Kata sandi minimal 8 karakter.',
            ],
            'password_confirm' => [
                'required' => 'Konfirmasi kata sandi wajib diisi.',
                'matches'  => 'Konfirmasi kata sandi tidak sama.',
            ],
        ];

        if (!$this->validateData($this->request->getPost(), $rules, $messages)) {
            return $this->jsonResponse(false, $this->formatValidationErrorsMessage($this->validator->getErrors()), [
                'errors' => $this->validator->getErrors(),
            ], 422);
        }

        $plainToken = trim((string) $this->request->getPost('token'));
        $resetRow   = $this->findValidPasswordReset($plainToken);

        if ($resetRow === null) {
            return $this->jsonResponse(false, 'Link reset password tidak valid atau sudah kedaluwarsa. Silakan minta link baru.', [], 410);
        }

        $email    = (string) $resetRow['email'];
        $password = (string) $this->request->getPost('password');

        try {
            $db = \Config\Database::connect();
            $db->transStart();

            $db->table('users')
                ->where('email', $email)
                ->update([
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                ]);

            $db->table('password_resets')->where('email', $email)->delete();

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi reset password gagal.');
            }
        } catch (\Throwable $e) {
            log_message('error', 'processResetPassword: {message}', ['message' => $e->getMessage()]);

            return $this->jsonResponse(false, 'Terjadi kesalahan saat mengubah kata sandi. Silakan coba lagi.', [], 500);
        }

        $successMessage = 'Password berhasil diubah. Silakan masuk dengan kata sandi baru.';

        if ($this->request->isAJAX()) {
            return $this->jsonResponse(true, $successMessage, [
                'redirect' => site_url('/?open=loginModal&reset_success=1'),
            ]);
        }

        return redirect()->to(site_url('/?open=loginModal&reset_success=1'))
            ->with('success', $successMessage);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findValidPasswordReset(string $plainToken): ?array
    {
        if ($plainToken === '') {
            return null;
        }

        try {
            $db = \Config\Database::connect();

            return $db->table('password_resets')
                ->where('token', hashPasswordResetToken($plainToken))
                ->where('expired_at >', date('Y-m-d H:i:s'))
                ->get()
                ->getRowArray() ?: null;
        } catch (\Throwable $e) {
            log_message('error', 'findValidPasswordReset: {message}', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @return array{success: bool, message: string, redirect?: string, role?: string, nama?: string}
     */
    private function attemptLogin(string $email, string $password): array
    {
        try {
            $db   = \Config\Database::connect();
            $user = $db->table('users')
                ->where('email', $email)
                ->get()
                ->getRowArray();

            if (!$user || !password_verify($password, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Email atau password salah.',
                ];
            }

            if (array_key_exists('is_active', $user) && (int) $user['is_active'] !== 1) {
                return [
                    'success' => false,
                    'message' => 'Akun nonaktif. Hubungi administrator.',
                ];
            }

            $sessionInfo = $this->establishUserSession($user);

            return [
                'success'  => true,
                'message'  => 'Login berhasil.',
                'redirect' => $sessionInfo['redirect'],
                'role'     => $sessionInfo['role'],
                'nama'     => $sessionInfo['nama'],
            ];
        } catch (\Throwable $e) {
            log_message('error', 'Login error: {message}', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat login. Silakan coba lagi.',
            ];
        }
    }

    /**
     * @param array<string, mixed> $user
     *
     * @return array{redirect: string, role: string, nama: string}
     */
    private function establishUserSession(array $user): array
    {
        $sessionData = [
            'isLoggedIn' => true,
            'id_user'    => (int) $user['id_user'],
            'nama'       => (string) $user['nama'],
            'role'       => (string) $user['role'],
        ];

        if ($user['role'] === 'pelanggan') {
            $db = \Config\Database::connect();
            $pelanggan = $db->table('pelanggan')
                ->where('id_user', $user['id_user'])
                ->get()
                ->getRowArray();

            if ($pelanggan) {
                $sessionData['id_pelanggan'] = (int) $pelanggan['id_pelanggan'];
            }
        }

        session()->set($sessionData);

        return [
            'redirect' => $this->dashboardPathForRole((string) $user['role']),
            'role'     => (string) $user['role'],
            'nama'     => (string) $user['nama'],
        ];
    }

    private function googleProvider(GoogleOAuth $googleConfig): Google
    {
        $redirectUri = $googleConfig->redirectUri !== ''
            ? $googleConfig->redirectUri
            : site_url('auth/google/callback');

        return new Google([
            'clientId'     => $googleConfig->clientId,
            'clientSecret' => $googleConfig->clientSecret,
            'redirectUri'  => $redirectUri,
        ]);
    }

    /**
     * @return array{success: bool, message: string, errors?: array<string, string>}
     */
    private function attemptRegister(): array
    {
        $postData      = $this->request->getPost();
        $contentLength = (int) $this->request->getHeaderLine('Content-Length');

        if (
            $this->request->getMethod() === 'POST'
            && $contentLength > 0
            && ($postData === null || $postData === [])
        ) {
            return [
                'success' => false,
                'message' => 'Ukuran data terlalu besar. Pastikan setiap dokumen maks. 2MB dan coba unggah ulang.',
            ];
        }

        $rules = array_merge(pelangganAkunValidationRules(), [
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ]);

        try {
            $validated = $this->validateData($postData ?? [], $rules, array_merge(
                pelangganAkunValidationMessages(),
                $this->registerValidationMessagesPerusahaanOnly()
            ));
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            log_message('error', 'Register DB validation: {message}', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $this->databaseUnavailableMessage($e),
            ];
        }

        if (!$validated) {
            return $this->registerValidationFailResponse();
        }

        $nama = trim((string) $postData['nama']);
        if ($formatError = validatePelangganAkunFormat($nama, trim((string) $postData['no_telp']))) {
            return [
                'success' => false,
                'message' => $formatError,
            ];
        }

        $noTelp = trim((string) $postData['no_telp']);

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $db->table('users')->insert([
                'nama'       => trim((string) $postData['nama']),
                'email'      => trim((string) $postData['email']),
                'password'   => password_hash((string) $postData['password'], PASSWORD_DEFAULT),
                'role'       => 'pelanggan',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $idUser = (int) $db->insertID();

            $pelangganData = [
                'id_user'     => $idUser,
                'no_telp'     => trim((string) $postData['no_telp']),
                'jenis'       => 'perseorangan',
                'is_verified' => 0,
                'alamat'      => $this->truncateAlamatPelanggan((string) ($postData['alamat'] ?? '')),
            ];

            $db->table('pelanggan')->insert($pelangganData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi registrasi gagal.');
            }

            return [
                'success' => true,
                'message' => 'Akun berhasil dibuat. Silakan login.',
            ];
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Register error: {message}', ['message' => $e->getMessage()]);

            $message = 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.';
            if (str_contains(strtolower($e->getMessage()), 'duplicate') || str_contains(strtolower($e->getMessage()), 'unique')) {
                $message = 'Email sudah terdaftar. Gunakan email lain atau masuk ke akun Anda.';
            }

            return [
                'success' => false,
                'message' => $message,
            ];
        }
    }

    /**
     * Pesan validasi field perusahaan (register perusahaan).
     *
     * @return array<string, array<string, string>>
     */
    private function registerValidationMessagesPerusahaanOnly(): array
    {
        return [
            'jenis_akun' => [
                'required' => 'Pilih jenis akun: Perseorangan atau Perusahaan.',
                'in_list'  => 'Jenis akun tidak valid.',
            ],
            'nama_perusahaan' => [
                'required'   => 'Nama perusahaan wajib diisi.',
                'min_length' => 'Nama perusahaan minimal 3 karakter.',
                'max_length' => 'Nama perusahaan maksimal 100 karakter.',
            ],
            'jabatan_pic' => [
                'required'   => 'Jabatan PIC wajib diisi.',
                'min_length' => 'Jabatan PIC minimal 2 karakter.',
                'max_length' => 'Jabatan PIC maksimal 100 karakter.',
            ],
            'wa_perusahaan' => [
                'required'   => 'No. HP/WA perusahaan wajib diisi.',
                'max_length' => 'No. HP/WA perusahaan terlalu panjang.',
            ],
            'alamat_kantor' => [
                'required'   => 'Alamat kantor wajib diisi.',
                'min_length' => 'Alamat kantor minimal 10 karakter.',
                'max_length' => 'Alamat kantor maksimal 255 karakter.',
            ],
            'no_npwp' => [
                'max_length' => 'No. NPWP terlalu panjang.',
            ],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function registerValidationMessages(): array
    {
        return array_merge(
            pelangganAkunValidationMessages(),
            $this->registerValidationMessagesPerusahaanOnly()
        );
    }

    /**
     * @return array{success: false, message: string, errors: array<string, string>}
     */
    private function registerValidationFailResponse(): array
    {
        $errors  = $this->validator->getErrors();
        $message = $this->formatValidationErrorsMessage($errors);

        if ($message === '') {
            $message = 'Data registrasi tidak valid. Periksa kembali semua field bertanda merah (*).';
        }

        return [
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ];
    }

    /**
     * @param array<string, string> $errors
     */
    private function formatValidationErrorsMessage(array $errors): string
    {
        $messages = [];

        foreach ($errors as $error) {
            $error = trim((string) $error);
            if ($error !== '') {
                $messages[] = $error;
            }
        }

        return implode(' ', array_unique($messages));
    }

    private function truncateAlamatPelanggan(string $alamat): string
    {
        return mb_substr(trim($alamat), 0, 150);
    }

    private function dashboardPathForRole(string $role): string
    {
        return match ($role) {
            'pelanggan' => site_url('dashboard'),
            'admin'     => site_url('admin/dashboard'),
            'keuangan'  => site_url('keuangan/dashboard'),
            'produksi'  => site_url('produksi/dashboard'),
            'owner'     => site_url('owner/dashboard'),
            default     => site_url('dashboard'),
        };
    }

    private function isInternalRole(string $role): bool
    {
        return in_array($role, ['admin', 'keuangan', 'produksi', 'owner'], true);
    }

    private function databaseUnavailableMessage(\Throwable $e): string
    {
        $detail = strtolower($e->getMessage());

        if (
            str_contains($detail, 'actively refused')
            || str_contains($detail, "can't connect")
            || str_contains($detail, '10061')
        ) {
            return 'MySQL belum berjalan. Buka XAMPP Control Panel, klik Start pada MySQL, lalu coba daftar lagi.';
        }

        if (str_contains($detail, 'crashed') || str_contains($detail, 'corruption')) {
            return 'MySQL gagal start karena data sistem rusak. Perbaiki lewat XAMPP (repair mysql) atau lihat C:\\xampp\\mysql\\data\\mysql_error.log.';
        }

        return 'Koneksi database gagal. Pastikan MySQL/XAMPP aktif dan database simenak_db ada di .env.';
    }

    /**
     * @param array<string, mixed> $data
     */
    private function jsonResponse(bool $success, string $message, array $data = [], int $status = 200)
    {
        return $this->response
            ->setStatusCode($status)
            ->setJSON(array_merge([
                'success' => $success,
                'message' => $message,
            ], $data));
    }
}
