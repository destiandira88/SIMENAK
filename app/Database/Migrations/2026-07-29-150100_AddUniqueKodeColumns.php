<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueKodeColumns extends Migration
{
    public function up()
    {
        // Safety: UNIQUE tidak boleh dipasang sebelum backfill.
        // Kalau masih ada kode_* yang NULL/empty, hentikan migrasi supaya urutan
        // (add columns -> backfill -> unique) tidak tertukar.
        $kodeChecks = [
            ['users', 'kode_user', 'uq_users_kode_user'],
            ['pelanggan', 'kode_pelanggan', 'uq_pelanggan_kode_pelanggan'],
            ['katalog', 'kode_katalog', 'uq_katalog_kode_katalog'],
            ['revisi_desain', 'kode_revisi', 'uq_revisi_desain_kode_revisi'],
            ['pengiriman', 'kode_kirim', 'uq_pengiriman_kode_kirim'],
        ];

        foreach ($kodeChecks as [$table, $col, $keyName]) {
            $remaining = (int) $this->db->table($table)
                ->groupStart()
                ->where($col, null)
                ->orWhere($col, '')
                ->groupEnd()
                ->countAllResults();

            if ($remaining > 0) {
                throw new \RuntimeException(
                    "UNIQUE belum bisa dipasang: kolom {$table}.{$col} masih memiliki {$remaining} baris NULL/empty. "
                    . "Jalankan: php spark backfill-kode-bisnis"
                );
            }
        }

        // users.kode_user
        $this->forge->addUniqueKey(['kode_user'], 'uq_users_kode_user');
        $this->forge->processIndexes('users');
        $this->forge->reset();

        // pelanggan.kode_pelanggan
        $this->forge->addUniqueKey(['kode_pelanggan'], 'uq_pelanggan_kode_pelanggan');
        $this->forge->processIndexes('pelanggan');
        $this->forge->reset();

        // katalog.kode_katalog
        $this->forge->addUniqueKey(['kode_katalog'], 'uq_katalog_kode_katalog');
        $this->forge->processIndexes('katalog');
        $this->forge->reset();

        // revisi_desain.kode_revisi
        $this->forge->addUniqueKey(['kode_revisi'], 'uq_revisi_desain_kode_revisi');
        $this->forge->processIndexes('revisi_desain');
        $this->forge->reset();

        // pengiriman.kode_kirim
        $this->forge->addUniqueKey(['kode_kirim'], 'uq_pengiriman_kode_kirim');
        $this->forge->processIndexes('pengiriman');
        $this->forge->reset();
    }

    public function down()
    {
        $this->forge->dropKey('pengiriman', 'uq_pengiriman_kode_kirim');
        $this->forge->dropKey('revisi_desain', 'uq_revisi_desain_kode_revisi');
        $this->forge->dropKey('katalog', 'uq_katalog_kode_katalog');
        $this->forge->dropKey('pelanggan', 'uq_pelanggan_kode_pelanggan');
        $this->forge->dropKey('users', 'uq_users_kode_user');
    }
}

