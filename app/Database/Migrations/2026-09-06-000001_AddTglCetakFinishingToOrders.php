<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tanggal mulai cetak & tanggal finishing untuk monitoring produksi (cetak → finishing).
 */
class AddTglCetakFinishingToOrders extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('orders')) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('tgl_mulai_cetak', 'orders')) {
            $fields['tgl_mulai_cetak'] = [
                'type' => 'DATE',
                'null' => true,
            ];
        }

        if (! $this->db->fieldExists('tgl_finishing', 'orders')) {
            $fields['tgl_finishing'] = [
                'type' => 'DATE',
                'null' => true,
            ];
        }

        if ($fields === []) {
            return;
        }

        if (isset($fields['tgl_mulai_cetak']) && $this->db->fieldExists('status', 'orders')) {
            $fields['tgl_mulai_cetak']['after'] = 'status';
        }
        if (isset($fields['tgl_finishing']) && isset($fields['tgl_mulai_cetak'])) {
            $fields['tgl_finishing']['after'] = 'tgl_mulai_cetak';
        } elseif (isset($fields['tgl_finishing']) && $this->db->fieldExists('tgl_mulai_cetak', 'orders')) {
            $fields['tgl_finishing']['after'] = 'tgl_mulai_cetak';
        }

        $this->forge->addColumn('orders', $fields);
    }

    public function down()
    {
        if (! $this->db->tableExists('orders')) {
            return;
        }

        foreach (['tgl_finishing', 'tgl_mulai_cetak'] as $col) {
            if ($this->db->fieldExists($col, 'orders')) {
                $this->forge->dropColumn('orders', $col);
            }
        }
    }
}
