<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMockupTemplates extends Migration
{
    public function up()
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
            'kategori' => [
                'type'       => 'ENUM',
                'constraint' => ['desain_grafis', 'cetak_digital', 'cetak_offset', 'media_promosi'],
                'null'       => false,
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
                'comment'    => 'Persen dari tinggi background',
            ],
            'slot_left' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => false,
                'default'    => 20.00,
                'comment'    => 'Persen dari lebar background',
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
        $this->forge->addUniqueKey(['kategori', 'sudut']);
        $this->forge->createTable('mockup_templates', true);
    }

    public function down()
    {
        if (! $this->db->tableExists('mockup_templates')) {
            return;
        }

        $this->forge->dropTable('mockup_templates', true);
    }
}
