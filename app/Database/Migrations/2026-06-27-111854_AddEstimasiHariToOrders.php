<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Snapshot jumlah hari kerja saat admin konfirmasi harga pesanan custom.
 */
class AddEstimasiHariToOrders extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('estimasi_hari', 'orders')) {
            return;
        }

        $fields = [
            'estimasi_hari' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ];

        if ($this->db->fieldExists('estimasi_custom', 'orders')) {
            $fields['estimasi_hari']['after'] = 'estimasi_custom';
        }

        $this->forge->addColumn('orders', $fields);
    }

    public function down()
    {
        if (! $this->db->fieldExists('estimasi_hari', 'orders')) {
            return;
        }

        $this->forge->dropColumn('orders', 'estimasi_hari');
    }
}
