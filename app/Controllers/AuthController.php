<?php

namespace App\Controllers;

class AuthController extends BaseController
{
    protected $helpers = ['form', 'url'];

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
        $result = $this->attemptRegister($this->request->getPost());

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
        if (!$this->request->is('post')) {
            return $this->jsonResponse(false, 'Metode tidak diizinkan.', [], 405);
        }

        $result = $this->attemptRegister($this->request->getPost());

        if (!$result['success']) {
            return $this->jsonResponse(false, $result['message'], [
                'errors' => $result['errors'] ?? [],
            ], 422);
        }

        return $this->jsonResponse(true, $result['message']);
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

            $sessionData = [
                'isLoggedIn' => true,
                'id_user'    => (int) $user['id_user'],
                'nama'       => $user['nama'],
                'role'       => $user['role'],
            ];

            if ($user['role'] === 'pelanggan') {
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
                'success'  => true,
                'message'  => 'Login berhasil.',
                'redirect' => $this->dashboardPathForRole((string) $user['role']),
                'role'     => (string) $user['role'],
                'nama'     => (string) $user['nama'],
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
     * @param array<string, mixed>|null $postData
     * @return array{success: bool, message: string, errors?: array<string, string>}
     */
    private function attemptRegister(?array $postData): array
    {
        $rules = [
            'nama'             => 'required|min_length[3]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'no_telp'          => 'required|regex_match[/^[0-9]{10,13}$/]',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (!$this->validateData($postData ?? [], $rules)) {
            return [
                'success' => false,
                'message' => implode(' ', $this->validator->getErrors()),
                'errors'  => $this->validator->getErrors(),
            ];
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $db->table('users')->insert([
                'nama'       => $postData['nama'],
                'email'      => $postData['email'],
                'password'   => password_hash((string) $postData['password'], PASSWORD_DEFAULT),
                'role'       => 'pelanggan',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $idUser = (int) $db->insertID();

            $db->table('pelanggan')->insert([
                'id_user'     => $idUser,
                'no_telp'     => $postData['no_telp'],
                'jenis'       => 'perseorangan',
                'is_verified' => 0,
            ]);

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

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.',
            ];
        }
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
