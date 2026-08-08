<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKodeColumnsToUsersPelangganKatalogRevisiDesainPengiriman extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'kode_user' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
        ]);

        $this->forge->addColumn('pelanggan', [
            'kode_pelanggan' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
        ]);

        $this->forge->addColumn('katalog', [
            'kode_katalog' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
        ]);

        $this->forge->addColumn('revisi_desain', [
            'kode_revisi' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
            ],
        ]);

        $this->forge->addColumn('pengiriman', [
            'kode_kirim' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pengiriman', 'kode_kirim');
        $this->forge->dropColumn('revisi_desain', 'kode_revisi');
        $this->forge->dropColumn('katalog', 'kode_katalog');
        $this->forge->dropColumn('pelanggan', 'kode_pelanggan');
        $this->forge->dropColumn('users', 'kode_user');
    }
}

