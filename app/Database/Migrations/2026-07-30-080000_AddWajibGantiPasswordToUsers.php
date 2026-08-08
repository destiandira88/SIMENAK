<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWajibGantiPasswordToUsers extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('wajib_ganti_password', 'users')) {
            return;
        }

        $fields = [
            'wajib_ganti_password' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
        ];

        if ($this->db->fieldExists('is_active', 'users')) {
            $fields['wajib_ganti_password']['after'] = 'is_active';
        }

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        if (! $this->db->fieldExists('wajib_ganti_password', 'users')) {
            return;
        }

        $this->forge->dropColumn('users', 'wajib_ganti_password');
    }
}
