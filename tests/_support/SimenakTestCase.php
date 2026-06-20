<?php

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Base test case SIMENAK — helper session role.
 */
abstract class SimenakTestCase extends CIUnitTestCase
{
    /**
     * @return array<string, mixed>
     */
    protected function sessionForRole(string $role, int $idUser = 1, string $nama = 'Test User'): array
    {
        return [
            'isLoggedIn' => true,
            'role'       => $role,
            'id_user'    => $idUser,
            'nama'       => $nama,
        ];
    }

    protected function ownerSession(): array
    {
        return $this->sessionForRole('owner', 1, 'Owner Test');
    }
}
