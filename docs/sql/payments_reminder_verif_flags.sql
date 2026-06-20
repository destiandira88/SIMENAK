-- Flag reminder eskalasi verifikasi DP (jalankan sekali di phpMyAdmin)
-- Digunakan oleh: php spark reminder-verifikasi-dp

ALTER TABLE `payments`
  ADD COLUMN `reminder_verif_2j_sent` TINYINT(1) NOT NULL DEFAULT 0 AFTER `tgl_verifikasi`,
  ADD COLUMN `reminder_verif_6j_sent` TINYINT(1) NOT NULL DEFAULT 0 AFTER `reminder_verif_2j_sent`;
