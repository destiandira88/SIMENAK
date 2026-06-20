-- ============================================================
-- MIGRASI + SEED: Uji Pengiriman & Pelunasan
-- Jalankan sekali di phpMyAdmin (database SIMENAK)
-- Prasyarat: 2026-06-06_tier_perusahaan.sql sudah dijalankan
-- ============================================================

-- ── BAGIAN 1: PERBAIKAN ENUM ─────────────────────────────────

-- Tambah status pelunasan_terverifikasi ke orders.status
ALTER TABLE `orders`
  MODIFY `status` ENUM(
    'menunggu_konfirmasi_harga',
    'menunggu_konfirmasi_pelanggan',
    'menunggu_verifikasi_dp',
    'terverifikasi',
    'proses_desain',
    'proses_revisi',
    'proses_cetak',
    'finishing',
    'siap_kirim',
    'siap_diambil',
    'dikirim',
    'pesanan_diterima',
    'menunggu_verifikasi_lunas',
    'pelunasan_terverifikasi',
    'selesai',
    'dibatalkan'
  ) NOT NULL DEFAULT 'menunggu_verifikasi_dp';

-- Tambah nilai 'diambil' ke pengiriman.status_kirim
ALTER TABLE `pengiriman`
  MODIFY `status_kirim` ENUM(
    'siap_kirim',
    'siap_diambil',
    'dikirim',
    'diterima',
    'diambil'
  ) DEFAULT 'siap_kirim';


-- ── BAGIAN 2: SETUP PELANGGAN TERPERCAYA (SKN-003) ───────────

-- Jadikan John Doe (id_pelanggan=4) sebagai perusahaan terpercaya
UPDATE `pelanggan`
SET
  `jenis`           = 'perusahaan',
  `nama_perusahaan` = 'CV. Maju Bersama',
  `is_verified`     = 1,
  `tier_perusahaan` = 'terpercaya'
WHERE `id_pelanggan` = 4;


-- ── BAGIAN 3: DATA SKENARIO ───────────────────────────────────

-- Hapus data lama jika dijalankan ulang (aman untuk dev)
DELETE FROM `payments`  WHERE `kode_payment` IN ('PAY-20260605-0091','PAY-20260606-0092','PAY-20260607-0093');
DELETE FROM `pengiriman` WHERE `id_order` IN (SELECT `id_order` FROM `orders` WHERE `kode_order` IN ('ORD-20260605-0091','ORD-20260606-0092','ORD-20260607-0093'));
DELETE FROM `orders`    WHERE `kode_order` IN ('ORD-20260605-0091','ORD-20260606-0092','ORD-20260607-0093');
-- Legacy test codes (jika masih ada dari seed lama)
DELETE FROM `payments`  WHERE `kode_payment` IN ('PAY-SKN001-DP','PAY-SKN002-DP','PAY-SKN003-DP');
DELETE FROM `pengiriman` WHERE `id_order` IN (SELECT `id_order` FROM `orders` WHERE `kode_order` IN ('SKN-TEST-001','SKN-TEST-002','SKN-TEST-003'));
DELETE FROM `orders`    WHERE `kode_order` IN ('SKN-TEST-001','SKN-TEST-002','SKN-TEST-003');


-- ============================================================
-- SKENARIO 1
-- Perseorangan · Kurir · Status: finishing
-- Pelanggan : id=1 (Pelanggan Test — pelanggan@test.com)
-- Titik mulai: Admin klik "Set Siap" → siap_kirim
--              Pelanggan upload pelunasan → menunggu_verifikasi_lunas
--              Keuangan ACC → pelunasan_terverifikasi
--              Admin input resi → dikirim
--              Pelanggan konfirmasi → selesai
-- ============================================================
INSERT INTO `orders`
  (`kode_order`, `id_pelanggan`, `id_katalog`, `jenis_pelanggan`, `is_custom`,
   `jumlah_order`, `metode_pengiriman`, `alamat_kirim`,
   `kuota_revisi`, `sisa_kuota`, `total_harga`, `require_dp`, `status`, `created_at`)
VALUES
  ('ORD-20260605-0091', 1, 3, 'perseorangan', 0,
   1, 'kurir', 'Jl. Test No. 1, Bandung Barat',
   2, 2, 500000.00, 1, 'finishing', NOW() - INTERVAL 3 DAY);

INSERT INTO `payments`
  (`kode_payment`, `id_order`, `jenis`, `nominal`, `bukti_tf`, `status`, `tgl_upload`, `tgl_verifikasi`, `id_verifikator`)
VALUES
  ('PAY-20260605-0091',
   (SELECT `id_order` FROM `orders` WHERE `kode_order` = 'ORD-20260605-0091'),
   'dp', 250000.00, 'dummy_bukti_dp.jpg', 'terverifikasi',
   NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 2 DAY, 3);


-- ============================================================
-- SKENARIO 2
-- Perseorangan · Ambil Sendiri · Status: siap_diambil
-- Pelanggan : id=3 (OldMoneySuccess — oldmoneysuccesswoman@gmail.com)
-- Titik mulai: Pelanggan upload pelunasan → menunggu_verifikasi_lunas
--              Keuangan ACC → pelunasan_terverifikasi
--              Admin klik "Konfirmasi Diambil" → selesai
-- ============================================================
INSERT INTO `orders`
  (`kode_order`, `id_pelanggan`, `id_katalog`, `jenis_pelanggan`, `is_custom`,
   `jumlah_order`, `metode_pengiriman`, `alamat_kirim`,
   `kuota_revisi`, `sisa_kuota`, `total_harga`, `require_dp`, `status`, `created_at`)
VALUES
  ('ORD-20260606-0092', 3, 5, 'perseorangan', 0,
   100, 'ambil_sendiri', NULL,
   2, 2, 300000.00, 1, 'siap_diambil', NOW() - INTERVAL 2 DAY);

INSERT INTO `payments`
  (`kode_payment`, `id_order`, `jenis`, `nominal`, `bukti_tf`, `status`, `tgl_upload`, `tgl_verifikasi`, `id_verifikator`)
VALUES
  ('PAY-20260606-0092',
   (SELECT `id_order` FROM `orders` WHERE `kode_order` = 'ORD-20260606-0092'),
   'dp', 150000.00, 'dummy_bukti_dp.jpg', 'terverifikasi',
   NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 1 DAY, 3);


-- ============================================================
-- SKENARIO 3
-- Perusahaan Terpercaya · Kurir · Status: dikirim (resi sudah ada)
-- Pelanggan : id=4 (John Doe / CV. Maju Bersama — john@gmail.com)
-- Titik mulai: Pelanggan klik "Konfirmasi Pesanan Diterima"
--              → menunggu_verifikasi_lunas
--              Pelanggan upload pelunasan → diverifikasi keuangan
--              Keuangan ACC → pelunasan_terverifikasi → selesai
-- Catatan    : require_dp=0 (terpercaya, tanpa DP)
-- ============================================================
INSERT INTO `orders`
  (`kode_order`, `id_pelanggan`, `id_katalog`, `jenis_pelanggan`, `is_custom`,
   `jumlah_order`, `metode_pengiriman`, `alamat_kirim`,
   `kuota_revisi`, `sisa_kuota`, `total_harga`, `require_dp`, `status`, `created_at`)
VALUES
  ('ORD-20260607-0093', 4, 1, 'perusahaan', 0,
   200, 'kurir', 'Jl. Bisnis No. 5, Jakarta Selatan',
   2, 2, 1500000.00, 0, 'dikirim', NOW() - INTERVAL 5 DAY);

-- Tidak ada record DP (require_dp = 0)
-- Sudah ada data pengiriman (resi sudah diinput admin)
INSERT INTO `pengiriman`
  (`id_order`, `no_resi`, `nama_ekspedisi`, `status_kirim`, `tgl_kirim`)
VALUES
  ((SELECT `id_order` FROM `orders` WHERE `kode_order` = 'ORD-20260607-0093'),
   'JNE2026123456', 'JNE', 'dikirim', NOW() - INTERVAL 1 DAY);
