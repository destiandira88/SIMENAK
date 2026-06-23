<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn')) {
            $path = $this->normalizedPath($request);

            return redirect()->to($this->loginRedirectFor($path))
                ->with('error', 'Silakan login terlebih dahulu.');
        }
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

    private function loginRedirectFor(string $path): string
    {
        if ($this->isPelangganPath($path)) {
            return '/';
        }

        return '/login';
    }

    private function isPelangganPath(string $path): bool
    {
        if ($path === '' || $path === 'dashboard' || $path === 'pelanggan/dashboard') {
            return true;
        }

        $pelangganPrefixes = [
            'katalog',
            'order/',
            'pesanan/',
            'pesanan-saya',
            'revisi/history/',
        ];

        foreach ($pelangganPrefixes as $prefix) {
            if ($path === rtrim($prefix, '/') || str_starts_with($path, $prefix)) {
                return !$this->isInternalKatalogPath($path);
            }
        }

        return false;
    }

    private function isInternalKatalogPath(string $path): bool
    {
        $internalPrefixes = [
            'katalog/kelola',
            'katalog/tambah',
            'katalog/edit/',
            'katalog/detail/',
        ];

        foreach ($internalPrefixes as $prefix) {
            if ($path === rtrim($prefix, '/') || str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
