<?php

namespace Tests\Support\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Skema minimal untuk pengujian Laporan Produksi (SQLite in-memory).
 */
class SimenakProduksiSchema extends Migration
{
    protected $DBGroup = 'tests';

    public function up(): void
    {
        $this->forge->addField([
            'id_user'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'password'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'       => ['type' => 'VARCHAR', 'constraint' => 20],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_user', true);
        $this->forge->createTable('users');

        $this->forge->addField([
            'id_pelanggan' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_user'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'jenis'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'perseorangan'],
        ]);
        $this->forge->addKey('id_pelanggan', true);
        $this->forge->createTable('pelanggan');

        $this->forge->addField([
            'id_katalog'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_produk'          => ['type' => 'VARCHAR', 'constraint' => 150],
            'kategori'             => ['type' => 'VARCHAR', 'constraint' => 30],
            'harga_dasar'          => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'kuota_revisi_default' => ['type' => 'INT', 'constraint' => 11, 'default' => 2],
            'min_order'            => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'satuan'               => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'pcs'],
            'is_active'            => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
        ]);
        $this->forge->addKey('id_katalog', true);
        $this->forge->createTable('katalog');

        $this->forge->addField([
            'id_order'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_order'        => ['type' => 'VARCHAR', 'constraint' => 25],
            'id_pelanggan'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_katalog'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'jenis_pelanggan'   => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'perseorangan'],
            'is_custom'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'jumlah_order'      => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'deadline_diajukan' => ['type' => 'DATE', 'null' => true],
            'deadline_produksi' => ['type' => 'DATE', 'null' => true],
            'metode_pengiriman' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'ambil_sendiri'],
            'kuota_revisi'      => ['type' => 'INT', 'constraint' => 11, 'default' => 2],
            'sisa_kuota'        => ['type' => 'INT', 'constraint' => 11, 'default' => 2],
            'total_harga'       => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
            'require_dp'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'status'            => ['type' => 'VARCHAR', 'constraint' => 40],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_order', true);
        $this->forge->createTable('orders');

        $this->forge->addField([
            'id_revisi'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_order'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_produksi'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 1],
            'versi'          => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'file_draft'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'status'         => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'uploaded'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_revisi', true);
        $this->forge->createTable('revisi_desain');
    }

    public function down(): void
    {
        $this->forge->dropTable('revisi_desain', true);
        $this->forge->dropTable('orders', true);
        $this->forge->dropTable('katalog', true);
        $this->forge->dropTable('pelanggan', true);
        $this->forge->dropTable('users', true);
    }
}
