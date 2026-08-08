<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Paksa staf dengan wajib_ganti_password=1 tetap di halaman Buat Password Baru
 * sampai password diganti (tidak bisa skip lewat URL dashboard).
 */
class ForcePasswordChangeFilter implements FilterInterface
{
    /** @var list<string> */
    private const ALLOWED_SUFFIXES = [
        'buat-password-baru',
        'logout',
        'csrf-sync',
        'check-session',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('isLoggedIn') || ! session()->get('wajib_ganti_password')) {
            return;
        }

        $path = $this->normalizedPath($request);

        if ($this->isAllowedPath($path)) {
            return;
        }

        return redirect()->to(site_url('buat-password-baru'))
            ->with('warning', 'Silakan buat password baru sebelum melanjutkan.');
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {
    }

    private function normalizedPath(RequestInterface $request): string
    {
        $path = trim($request->getUri()->getPath(), '/');

        if ($path === 'index.php') {
            return '';
        }

        if (str_starts_with($path, 'index.php/')) {
            $path = substr($path, strlen('index.php/'));
        }

        return strtolower($path);
    }

    private function isAllowedPath(string $path): bool
    {
        foreach (self::ALLOWED_SUFFIXES as $suffix) {
            if ($path === $suffix || str_ends_with($path, '/' . $suffix)) {
                return true;
            }
        }

        return false;
    }
}
