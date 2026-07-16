<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Flag eskalasi owner untuk pesanan custom menunggu konfirmasi harga admin (>2 hari kerja).
 */
class AddReminderHargaEskalasiToOrders extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('reminder_harga_eskalasi_sent', 'orders')) {
            return;
        }

        $fields = [
            'reminder_harga_eskalasi_sent' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
        ];

        if ($this->db->fieldExists('reminder_dp_sent', 'orders')) {
            $fields['reminder_harga_eskalasi_sent']['after'] = 'reminder_dp_sent';
        }

        $this->forge->addColumn('orders', $fields);
    }

    public function down()
    {
        if (! $this->db->fieldExists('reminder_harga_eskalasi_sent', 'orders')) {
            return;
        }

        $this->forge->dropColumn('orders', 'reminder_harga_eskalasi_sent');
    }
}
