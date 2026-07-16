-- Flag eskalasi owner konfirmasi harga custom (jalankan sekali di phpMyAdmin)
-- Digunakan oleh: php spark reminder-konfirmasi-harga-custom

ALTER TABLE `orders`
  ADD COLUMN `reminder_harga_eskalasi_sent` TINYINT(1) NOT NULL DEFAULT 0 AFTER `reminder_dp_sent`;
