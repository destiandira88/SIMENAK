-- Hapus kolom legacy tier_perusahaan (pemula/terpercaya).
-- Skema aktif: Kerja Sama Perusahaan via jenis='perusahaan' AND is_verified=1.
-- Jalankan sekali di phpMyAdmin atau:
--   c:\xampp\mysql\bin\mysql.exe -u root simenak_db < docs/migrations/2026-06-09_drop_tier_perusahaan.sql

ALTER TABLE `pelanggan`
  DROP COLUMN `tier_perusahaan`;
