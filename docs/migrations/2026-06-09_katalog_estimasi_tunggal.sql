-- Normalisasi estimasi_hari katalog dari format range (legacy) ke angka tunggal.
-- Contoh: "3-7 hari kerja" → "5 hari kerja" (ambil angka minimum dalam string).
-- Jalankan sekali; produk yang sudah format "6 hari kerja" tidak berubah.

UPDATE `katalog`
SET `estimasi_hari` = CONCAT(
    GREATEST(1, CAST(REGEXP_SUBSTR(`estimasi_hari`, '[0-9]+') AS UNSIGNED)),
    ' hari kerja'
)
WHERE `estimasi_hari` IS NOT NULL
  AND `estimasi_hari` <> ''
  AND `estimasi_hari` NOT REGEXP '^[0-9]+ hari kerja$';
