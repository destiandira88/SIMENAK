-- Fase 1: Pisahkan deadline ajuan pelanggan vs deadline produksi (resmi).
-- Kolom lama `deadline` → `deadline_produksi` (data lama tetap).
-- Kolom baru `deadline_diajukan` = histori ajuan pelanggan saat buat pesanan.
--
-- Jalankan sekali di phpMyAdmin atau:
--   c:\xampp\mysql\bin\mysql.exe -u root simenak_db < docs/migrations/2026-06-12_split_deadline_orders.sql
--
-- Setelah jalan, jalankan query verifikasi di bagian bawah file ini.
-- JANGAN lanjut ubah kode PHP sebelum hasil verifikasi OK.

-- ─── 1) Tambah kolom ajuan pelanggan ─────────────────────────────────────────
ALTER TABLE `orders`
  ADD COLUMN `deadline_diajukan` DATE NULL DEFAULT NULL AFTER `detail_pesanan`;

-- ─── 2) Backfill dari nilai deadline lama ───────────────────────────────────
UPDATE `orders`
SET `deadline_diajukan` = `deadline`
WHERE `deadline` IS NOT NULL;

-- ─── 3) Rename deadline → deadline_produksi ─────────────────────────────────
ALTER TABLE `orders`
  CHANGE COLUMN `deadline` `deadline_produksi` DATE NULL DEFAULT NULL;

-- ─── 4) Opsional: custom yang belum dikonfirmasi admin — produksi masih NULL ─
-- Ajuan pelanggan tetap di deadline_diajukan; admin isi deadline_produksi nanti.
UPDATE `orders`
SET `deadline_produksi` = NULL
WHERE `is_custom` = 1
  AND `status` = 'menunggu_konfirmasi_harga';

-- ─── Verifikasi (copy-paste terpisah setelah migration) ─────────────────────
-- SELECT COUNT(*) AS total_orders FROM orders;
--
-- SELECT
--   id_order,
--   kode_order,
--   is_custom,
--   status,
--   deadline_diajukan,
--   deadline_produksi
-- FROM orders
-- ORDER BY id_order DESC
-- LIMIT 20;
--
-- SELECT
--   SUM(deadline_diajukan IS NOT NULL) AS punya_diajukan,
--   SUM(deadline_produksi IS NOT NULL) AS punya_produksi,
--   SUM(is_custom = 1 AND status = 'menunggu_konfirmasi_harga') AS custom_menunggu_harga
-- FROM orders;
