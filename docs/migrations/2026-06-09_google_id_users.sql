-- Kolom google_id untuk login pelanggan via Google OAuth
ALTER TABLE `users`
    ADD COLUMN `google_id` VARCHAR(100) NULL UNIQUE AFTER `email`;
