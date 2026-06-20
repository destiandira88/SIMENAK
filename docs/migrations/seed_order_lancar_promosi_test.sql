-- =============================================================================
-- SEED UJI CEPAT: 3 order perusahaan "lancar" untuk tombol Promosikan (3/3)
-- Database: simenak_db
-- Jalankan via phpMyAdmin (tab SQL) atau:
--   c:\xampp\mysql\bin\mysql.exe -u root simenak_db -e "SOURCE c:/xampp/htdocs/SIMENAK/docs/migrations/seed_order_lancar_promosi_test.sql"
--
-- GANTI id_pelanggan di bawah sesuai akun yang mau diuji (lihat query cek).
-- =============================================================================

-- 1) Cek pelanggan terverifikasi (opsional)
-- SELECT p.id_pelanggan, u.nama, u.email, p.is_verified, p.tier_perusahaan, p.nama_perusahaan
-- FROM pelanggan p JOIN users u ON u.id_user = p.id_user;

SET @id_pelanggan = 3;   -- << GANTI: id_pelanggan yang tier Pemula & is_verified=1

-- 2) Pastikan akun siap uji promosi (tier Pemula, terverifikasi, tidak suspend)
UPDATE pelanggan
SET is_verified     = 1,
    jenis           = 'perusahaan',
    tier_perusahaan = 'pemula',
    is_suspended    = 0,
    nama_perusahaan = COALESCE(NULLIF(nama_perusahaan, ''), 'PT Uji Promosi SIMENAK')
WHERE id_pelanggan = @id_pelanggan;

-- 3) Tiga order dummy selesai (jenis perusahaan)
-- Catatan: kode_order max 25 karakter — pakai kode pendek
INSERT INTO orders (
    kode_order, id_pelanggan, id_katalog, jenis_pelanggan, jumlah_order,
    detail_pesanan, deadline, metode_pengiriman,
    kuota_revisi, sisa_kuota, total_harga, require_dp, status, created_at
) VALUES
(
    CONCAT('TST-L', DATE_FORMAT(NOW(), '%m%d'), '01'),
    @id_pelanggan, 1, 'perusahaan', 100,
    'Order uji promosi tier 1/3', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'ambil_sendiri',
    2, 2, 2500000.00, 1, 'selesai', DATE_SUB(NOW(), INTERVAL 30 DAY)
),
(
    CONCAT('TST-L', DATE_FORMAT(NOW(), '%m%d'), '02'),
    @id_pelanggan, 2, 'perusahaan', 50,
    'Order uji promosi tier 2/3', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'ambil_sendiri',
    2, 2, 1800000.00, 1, 'selesai', DATE_SUB(NOW(), INTERVAL 20 DAY)
),
(
    CONCAT('TST-L', DATE_FORMAT(NOW(), '%m%d'), '03'),
    @id_pelanggan, 3, 'perusahaan', 10,
    'Order uji promosi tier 3/3', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'kurir',
    2, 2, 3200000.00, 1, 'selesai', DATE_SUB(NOW(), INTERVAL 10 DAY)
);

SET @kode1 = CONCAT('TST-L', DATE_FORMAT(NOW(), '%m%d'), '01');
SET @kode2 = CONCAT('TST-L', DATE_FORMAT(NOW(), '%m%d'), '02');
SET @kode3 = CONCAT('TST-L', DATE_FORMAT(NOW(), '%m%d'), '03');

SET @id_order_1 = (SELECT id_order FROM orders WHERE kode_order = @kode1 LIMIT 1);
SET @id_order_2 = (SELECT id_order FROM orders WHERE kode_order = @kode2 LIMIT 1);
SET @id_order_3 = (SELECT id_order FROM orders WHERE kode_order = @kode3 LIMIT 1);

-- 4) Pelunasan terverifikasi untuk masing-masing order (syarat "order lancar")
INSERT INTO payments (
    kode_payment, id_order, jenis, nominal, bukti_tf, status,
    id_verifikator, tgl_upload, tgl_verifikasi
) VALUES
(
    CONCAT('PAY-L', DATE_FORMAT(NOW(), '%m%d'), '01'),
    @id_order_1, 'pelunasan', 1250000.00, 'dummy_bukti_test.jpg', 'terverifikasi',
    NULL, DATE_SUB(NOW(), INTERVAL 29 DAY), DATE_SUB(NOW(), INTERVAL 28 DAY)
),
(
    CONCAT('PAY-L', DATE_FORMAT(NOW(), '%m%d'), '02'),
    @id_order_2, 'pelunasan', 900000.00, 'dummy_bukti_test.jpg', 'terverifikasi',
    NULL, DATE_SUB(NOW(), INTERVAL 19 DAY), DATE_SUB(NOW(), INTERVAL 18 DAY)
),
(
    CONCAT('PAY-L', DATE_FORMAT(NOW(), '%m%d'), '03'),
    @id_order_3, 'pelunasan', 1600000.00, 'dummy_bukti_test.jpg', 'terverifikasi',
    NULL, DATE_SUB(NOW(), INTERVAL 9 DAY), DATE_SUB(NOW(), INTERVAL 8 DAY)
);

-- 5) Verifikasi hitungan (harus = 3)
SELECT
    p.id_pelanggan,
    u.nama,
    p.nama_perusahaan,
    p.tier_perusahaan,
    COUNT(DISTINCT o.id_order) AS order_lancar
FROM pelanggan p
JOIN users u ON u.id_user = p.id_user
JOIN orders o ON o.id_pelanggan = p.id_pelanggan
    AND o.jenis_pelanggan = 'perusahaan'
    AND o.status = 'selesai'
JOIN payments pay ON pay.id_order = o.id_order
    AND pay.jenis = 'pelunasan'
    AND pay.status = 'terverifikasi'
WHERE p.id_pelanggan = @id_pelanggan
GROUP BY p.id_pelanggan, u.nama, p.nama_perusahaan, p.tier_perusahaan;

-- Setelah ini: login Admin → Pengguna → tombol "Promosikan" harus muncul.

-- =============================================================================
-- OPSIONAL: Hapus data uji (jalan terpisah jika mau reset)
-- =============================================================================
-- DELETE pay FROM payments pay
-- JOIN orders o ON o.id_order = pay.id_order
-- WHERE o.kode_order LIKE 'TST-L%';
-- DELETE FROM orders WHERE kode_order LIKE 'TST-L%';
-- DELETE FROM payments WHERE kode_payment LIKE 'PAY-L%';
