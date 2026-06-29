-- Tier perusahaan + field verifikasi MOU
-- Jalankan di database SIMENAK
-- CATATAN: tier_perusahaan sudah dihapus oleh 2026-06-09_drop_tier_perusahaan.sql (legacy).
-- File ini disimpan untuk riwayat migrasi; jangan jalankan ulang di DB baru.

ALTER TABLE `pelanggan`
  ADD COLUMN `tier_perusahaan` ENUM('pemula','terpercaya') DEFAULT NULL AFTER `is_verified`,
  ADD COLUMN `is_suspended` TINYINT(4) NOT NULL DEFAULT 0 AFTER `tier_perusahaan`;

ALTER TABLE `verifikasi_perusahaan`
  ADD COLUMN `jabatan_pic` VARCHAR(100) DEFAULT NULL AFTER `no_npwp`,
  ADD COLUMN `wa_perusahaan` VARCHAR(20) DEFAULT NULL AFTER `jabatan_pic`,
  ADD COLUMN `alamat_kantor` VARCHAR(255) DEFAULT NULL AFTER `wa_perusahaan`,
  ADD COLUMN `dokumen_mou` VARCHAR(255) DEFAULT NULL AFTER `dokumen_ktp_pic`;

-- Set tier pemula untuk perusahaan yang sudah terverifikasi
UPDATE `pelanggan`
SET `tier_perusahaan` = 'pemula'
WHERE `is_verified` = 1 AND `tier_perusahaan` IS NULL;
