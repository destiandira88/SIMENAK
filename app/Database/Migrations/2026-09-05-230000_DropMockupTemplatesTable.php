<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Hapus tabel mockup_templates (override Admin). Mapping mockup hanya via folder assets.
 * Migration id_katalog sebelumnya tidak di-revert — tabel di-drop karena tidak dipakai.
 */
class DropMockupTemplatesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('mockup_templates')) {
            $this->forge->dropTable('mockup_templates', true);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('mockup_templates')) {
            return;
        }

        $this->forge->addField([
            'id_mockup' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_katalog' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'sudut' => [
                'type'       => 'ENUM',
                'constraint' => ['depan', 'samping', 'atas'],
                'null'       => false,
            ],
            'gambar_background' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'slot_top' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => false,
                'default'    => 20.00,
            ],
            'slot_left' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => false,
                'default'    => 20.00,
            ],
            'slot_width' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => false,
                'default'    => 60.00,
            ],
            'slot_height' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => false,
                'default'    => 60.00,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_mockup', true);
        $this->forge->addUniqueKey(['id_katalog', 'sudut']);
        $this->forge->addKey('id_katalog');
        $this->forge->createTable('mockup_templates', true);
    }
}
