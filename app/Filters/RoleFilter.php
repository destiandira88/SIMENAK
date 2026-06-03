<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        if ($arguments === null || $arguments === []) {
            return;
        }

        $userRole     = (string) session()->get('role');
        $allowedRoles = array_map('strval', (array) $arguments);

        if (!in_array($userRole, $allowedRoles, true)) {
            return redirect()->to('/dashboard')
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {}
}
