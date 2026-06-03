-- TUGAS 1: Kolom deadline upload DP (24 jam)
-- Jalankan di phpMyAdmin atau: mysql -u root simenak < docs/sql/orders_batas_upload_dp.sql

ALTER TABLE orders
  ADD COLUMN batas_upload_dp   DATETIME     NULL AFTER created_at,
  ADD COLUMN reminder_dp_sent  TINYINT(1)   NOT NULL DEFAULT 0 AFTER batas_upload_dp;
