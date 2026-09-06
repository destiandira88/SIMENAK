<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Penyesuaian posisi/skala draft pada mockup (disimpan per versi revisi).
 * JSON: {"cover":{"ox":0,"oy":0,"scale":1},"dalam":{...}}
 */
class AddMockupAdjustToRevisiDesain extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('revisi_desain')) {
            return;
        }

        if ($this->db->fieldExists('mockup_adjust', 'revisi_desain')) {
            return;
        }

        $this->forge->addColumn('revisi_desain', [
            'mockup_adjust' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'file_draft',
            ],
        ]);
    }

    public function down()
    {
        if (! $this->db->tableExists('revisi_desain')) {
            return;
        }

        if (! $this->db->fieldExists('mockup_adjust', 'revisi_desain')) {
            return;
        }

        $this->forge->dropColumn('revisi_desain', 'mockup_adjust');
    }
}
