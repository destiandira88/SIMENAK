<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGoogleIdToUsers extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('google_id', 'users')) {
            return;
        }

        $fields = [
            'google_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'unique'     => true,
            ],
        ];

        if ($this->db->fieldExists('email', 'users')) {
            $fields['google_id']['after'] = 'email';
        }

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        if (! $this->db->fieldExists('google_id', 'users')) {
            return;
        }

        $this->forge->dropColumn('users', 'google_id');
    }
}
