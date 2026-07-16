<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActivityLogs extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('activity_logs')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_user' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'nama_user' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'aksi' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'modul' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('created_at');
        $this->forge->addKey('modul');
        $this->forge->createTable('activity_logs', true);
    }

    public function down()
    {
        if (! $this->db->tableExists('activity_logs')) {
            return;
        }

        $this->forge->dropTable('activity_logs', true);
    }
}
