<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Status aktif/nonaktif akun staff (dan pengguna internal).
 */
class AddIsActiveToUsers extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('is_active', 'users')) {
            return;
        }

        $fields = [
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 1,
                'null'       => false,
            ],
        ];

        if ($this->db->fieldExists('role', 'users')) {
            $fields['is_active']['after'] = 'role';
        }

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        if (! $this->db->fieldExists('is_active', 'users')) {
            return;
        }

        $this->forge->dropColumn('users', 'is_active');
    }
}
