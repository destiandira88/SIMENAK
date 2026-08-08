<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Migration;

/**
 * Hapus nilai ENUM 'pending' dari verifikasi_perusahaan.status.
 * Alur kerja sama perusahaan kini ditetapkan/dicabut Admin (verified/rejected),
 * tanpa antrian pengajuan pelanggan.
 *
 * Migration::$db di-type sebagai ConnectionInterface; tableExists/fieldExists
 * ada di BaseConnection (runtime selalu instance itu).
 *
 * @property BaseConnection $db
 */
class RemovePendingFromVerifikasiPerusahaanStatus extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('verifikasi_perusahaan')) {
            return;
        }

        if (! $this->db->fieldExists('status', 'verifikasi_perusahaan')) {
            return;
        }

        $type = $this->currentStatusType();
        if ($type !== null && ! str_contains($type, "'pending'")) {
            return;
        }

        // Amankan data lama (jika ada) sebelum ENUM dipersempit.
        $this->db->table('verifikasi_perusahaan')
            ->where('status', 'pending')
            ->update([
                'status'         => 'rejected',
                'catatan_admin'  => 'Migrasi: status pending dihapus (alur pengajuan pelanggan tidak dipakai).',
                'tgl_verifikasi' => date('Y-m-d H:i:s'),
            ]);

        $this->db->query(
            "ALTER TABLE `verifikasi_perusahaan`
             MODIFY `status` ENUM('verified','rejected') NOT NULL DEFAULT 'verified'"
        );
    }

    public function down()
    {
        if (! $this->db->tableExists('verifikasi_perusahaan')) {
            return;
        }

        if (! $this->db->fieldExists('status', 'verifikasi_perusahaan')) {
            return;
        }

        $type = $this->currentStatusType();
        if ($type !== null && str_contains($type, "'pending'")) {
            return;
        }

        $this->db->query(
            "ALTER TABLE `verifikasi_perusahaan`
             MODIFY `status` ENUM('pending','verified','rejected') NULL DEFAULT 'pending'"
        );
    }

    private function currentStatusType(): ?string
    {
        $row = $this->db->query("SHOW COLUMNS FROM `verifikasi_perusahaan` LIKE 'status'")->getRowArray();

        return isset($row['Type']) ? strtolower((string) $row['Type']) : null;
    }
}
