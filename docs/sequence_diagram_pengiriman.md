# SEQUENCE DIAGRAM PENGIRIMAN

**Use Case:** Pengiriman (Input Resi/Konfirmasi Diambil → Konfirmasi Terima)

**Actors:** Admin, Pelanggan

**Boundary Objects:**
- Halaman Manajemen Pengiriman (Admin)
- Halaman Detail Pesanan

**Control Objects:**
- Kontrol Pengiriman

**Entity Objects:**
- Order
- Pelanggan «entity»
- Pengiriman
- Notification

---

## Detail Messages (Diagram dipecah jadi 2: MAIN + 1 DETAIL)

## DIAGRAM UTAMA — Set Siap Kirim/Diambil

| No | From | To | Label Pesan | Keterangan |
|---|---|---|---|---|
| 1 | Admin | Halaman Manajemen Pengiriman | aksesManajemenPengiriman() | Admin buka halaman manajemen pengiriman |
| 2 | Halaman Manajemen Pengiriman | Admin | tampilkanDaftarPesanan | (return) tampil list pesanan finishing/siap_kirim/siap_diambil/dikirim |
| 3 | Admin | Halaman Manajemen Pengiriman | setSiap(idOrder) | Klik tombol "Set Siap Kirim/Diambil" untuk pesanan finishing |
| 4 | Halaman Manajemen Pengiriman | Kontrol Pengiriman | prosesSiap(idOrder) | Teruskan ke lapisan proses |
| 5 | Kontrol Pengiriman | Order | getById(idOrder) | Ambil data order untuk cek metode_pengiriman |
| 6 | Order | Kontrol Pengiriman | dataOrder | (return) {metode_pengiriman, status, jenis_pelanggan} |
| 7a | - | - | [metode_pengiriman = 'kurir'] | Percabangan 1 |
| 8a | Kontrol Pengiriman | Order | updateStatus(idOrder, 'siap_kirim') | Update status pesanan jadi siap_kirim |
| 9a | Order | Kontrol Pengiriman | statusUpdated | (return) status updated |
| 10a | Kontrol Pengiriman | Notification | kirim(idPelanggan, tipeSiapKirim) | Notifikasi email + in-app ke Pelanggan: pesanan siap dikirim |
| 11a | Notification | Kontrol Pengiriman | notifSent | (return) notifikasi terkirim |
| 12a | - | - | ref: DETAIL Jalur Kurir | Lanjut ke input resi → dikirim → konfirmasi terima |
| 7b | - | - | [metode_pengiriman = 'ambil_sendiri'] | Percabangan 1 |
| 8b | Kontrol Pengiriman | Order | updateStatus(idOrder, 'siap_diambil') | Update status pesanan jadi siap_diambil |
| 9b | Order | Kontrol Pengiriman | statusUpdated | (return) status updated |
| 10b | Kontrol Pengiriman | Notification | kirim(idPelanggan, tipeSiapDiambil) | Notifikasi email + in-app ke Pelanggan: pesanan siap diambil |
| 11b | Notification | Kontrol Pengiriman | notifSent | (return) notifikasi terkirim |
| 12b | - | - | MENUNGGU PELANGGAN AMBIL | Pelanggan datang ke toko untuk ambil barang (di luar sistem) |
| 13b | Admin | Halaman Manajemen Pengiriman | konfirmasiDiambil(idOrder) | Admin klik tombol "Konfirmasi Diambil" saat pelanggan datang ambil |
| 14b | Halaman Manajemen Pengiriman | Kontrol Pengiriman | prosesKonfirmasiDiambil(idOrder) | Teruskan ke lapisan proses |
| 15b | Kontrol Pengiriman | Pelanggan «entity» | isPelunasanSebelumKirim(dataOrder) | Cek timing pelunasan berdasarkan jenis_pelanggan |
| 16b | Pelanggan «entity» | Kontrol Pengiriman | timingPelunasan | (return) boolean (true = sebelum kirim, false = setelah terima) |
| 17ba | - | - | [timingPelunasan = true] | Percabangan 2: Perseorangan — selesai |
| 18ba | Kontrol Pengiriman | Order | updateStatus(idOrder, 'selesai') | Pelunasan sudah dibayar sebelumnya, langsung selesai |
| 19ba | Order | Kontrol Pengiriman | statusUpdated | (return) status updated |
| 20ba | Kontrol Pengiriman | Pengiriman | konfirmasiDiambil(idOrder) | Simpan record pengiriman dengan status_kirim='diambil' |
| 21ba | Pengiriman | Kontrol Pengiriman | pengirimanCreated | (return) data pengiriman |
| 22ba | Kontrol Pengiriman | Notification | kirim(idPelanggan, tipePesananSelesai) | Notifikasi email + in-app ke Pelanggan: pesanan selesai |
| 23ba | Notification | Kontrol Pengiriman | notifSent | (return) notifikasi terkirim |
| 17bb | - | - | [timingPelunasan = false] | Percabangan 2: Kerja Sama — menunggu pelunasan |
| 18bb | Kontrol Pengiriman | Order | updateStatus(idOrder, 'menunggu_verifikasi_lunas') | Barang diambil, aktifkan nota tagihan pelunasan |
| 19bb | Order | Kontrol Pengiriman | statusUpdated | (return) status updated |
| 20bb | Kontrol Pengiriman | Pengiriman | konfirmasiDiambil(idOrder) | Simpan record pengiriman dengan status_kirim='diambil' |
| 21bb | Pengiriman | Kontrol Pengiriman | pengirimanCreated | (return) data pengiriman |
| 22bb | Kontrol Pengiriman | Notification | kirim(idPelanggan, tipeNotaTagihan) | Notifikasi email + in-app ke Pelanggan: silakan upload pelunasan |
| 23bb | Notification | Kontrol Pengiriman | notifSent | (return) notifikasi terkirim |
| 24bb | - | - | LANJUT KE USE CASE PEMBAYARAN PELUNASAN | Pelanggan upload pelunasan di use case terpisah |

---

## DETAIL — Jalur Kurir (Input Resi → Dikirim → Konfirmasi Terima)

| No | From | To | Label Pesan | Keterangan |
|---|---|---|---|---|
| 13a | Admin | Halaman Manajemen Pengiriman | inputResi(idOrder, noResi, namaEkspedisi) | Admin input nomor resi + ekspedisi untuk pesanan siap_kirim (atau pelunasan_terverifikasi untuk perseorangan) |
| 14a | Halaman Manajemen Pengiriman | Kontrol Pengiriman | prosesInputResi(idOrder, noResi, namaEkspedisi) | Teruskan ke lapisan proses |
| 15a | Kontrol Pengiriman | Order | updateStatus(idOrder, 'dikirim') | Update status pesanan jadi dikirim |
| 16a | Order | Kontrol Pengiriman | statusUpdated | (return) status updated |
| 17a | Kontrol Pengiriman | Pengiriman | inputResi(idOrder, noResi, namaEkspedisi) | Simpan record pengiriman dengan status_kirim='dikirim', no_resi, nama_ekspedisi |
| 18a | Pengiriman | Kontrol Pengiriman | pengirimanCreated | (return) data pengiriman |
| 19a | Kontrol Pengiriman | Notification | kirim(idPelanggan, tipePesananDikirim) | Notifikasi email + in-app ke Pelanggan: pesanan dikirim + resi |
| 20a | Notification | Kontrol Pengiriman | notifSent | (return) notifikasi terkirim |
| 21a | Pelanggan | Halaman Detail Pesanan | konfirmasiDiterima(idOrder) | Pelanggan klik tombol "Konfirmasi Diterima" setelah barang sampai |
| 22a | Halaman Detail Pesanan | Kontrol Pengiriman | prosesKonfirmasiDiterima(idOrder) | Teruskan ke lapisan proses |
| 23a | Kontrol Pengiriman | Pelanggan «entity» | isPelunasanSebelumKirim(dataOrder) | Cek timing pelunasan berdasarkan jenis_pelanggan |
| 24a | Pelanggan «entity» | Kontrol Pengiriman | timingPelunasan | (return) boolean (true = sebelum kirim, false = setelah terima) |
| 25aa | - | - | [timingPelunasan = true] | Percabangan 3: Perseorangan — selesai |
| 26aa | Kontrol Pengiriman | Order | updateStatus(idOrder, 'selesai') | Pelunasan sudah dibayar sebelumnya, langsung selesai |
| 27aa | Order | Kontrol Pengiriman | statusUpdated | (return) status updated |
| 28aa | Kontrol Pengiriman | Pengiriman | konfirmasiDiterima(idOrder) | Update status_kirim='diterima', tgl_diterima |
| 29aa | Pengiriman | Kontrol Pengiriman | resiUpdated | (return) data pengiriman updated |
| 30aa | Kontrol Pengiriman | Notification | kirim(idPelanggan, tipePesananSelesai) | Notifikasi email + in-app ke Pelanggan: pesanan selesai |
| 31aa | Notification | Kontrol Pengiriman | notifSent | (return) notifikasi terkirim |
| 32aa | Kontrol Pengiriman | Halaman Detail Pesanan | infoPesananSelesai() | Kirim info pesanan selesai |
| 33aa | Halaman Detail Pesanan | Pelanggan | tampilkanInfoSelesai | (return) pesan "Terima kasih! Pesanan selesai." tampil |
| 25ab | - | - | [timingPelunasan = false] | Percabangan 3: Kerja Sama — menunggu pelunasan |
| 26ab | Kontrol Pengiriman | Order | updateStatus(idOrder, 'menunggu_verifikasi_lunas') | Barang diterima, aktifkan nota tagihan pelunasan |
| 27ab | Order | Kontrol Pengiriman | statusUpdated | (return) status updated |
| 28ab | Kontrol Pengiriman | Pengiriman | konfirmasiDiterima(idOrder) | Update status_kirim='diterima', tgl_diterima |
| 29ab | Pengiriman | Kontrol Pengiriman | resiUpdated | (return) data pengiriman updated |
| 30ab | Kontrol Pengiriman | Notification | kirim(idPelanggan, tipeNotaTagihan) | Notifikasi email + in-app ke Pelanggan: silakan upload pelunasan |
| 31ab | Notification | Kontrol Pengiriman | notifSent | (return) notifikasi terkirim |
| 32ab | Kontrol Pengiriman | Halaman Detail Pesanan | infoNotaTagihan() | Kirim info nota tagihan aktif |
| 33ab | Halaman Detail Pesanan | Pelanggan | tampilkanInfoNotaTagihan | (return) pesan "Pesanan diterima. Silakan upload bukti pelunasan." tampil |
| 34ab | - | - | LANJUT KE USE CASE PEMBAYARAN PELUNASAN | Pelanggan upload pelunasan di use case terpisah |

---

## CATATAN AKADEMIS

1. **4 Jalur Pengiriman:** Diagram ini mencakup 4 kombinasi:
   - **Kurir + Perseorangan:** Input resi → dikirim → konfirmasi terima → selesai
   - **Kurir + Kerja Sama:** Input resi → dikirim → konfirmasi terima → menunggu_verifikasi_lunas → use case Pembayaran Pelunasan
   - **Ambil Sendiri + Perseorangan:** Konfirmasi diambil → selesai
   - **Ambil Sendiri + Kerja Sama:** Konfirmasi diambil → menunggu_verifikasi_lunas → use case Pembayaran Pelunasan

2. **Percabangan Bertingkat:**
   - Percabangan 1 (7a/7b): Kurir vs Ambil Sendiri
   - Percabangan 2 (17ba/17bb): Perseorangan vs Kerja Sama (untuk Ambil Sendiri)
   - Percabangan 3 (25aa/25ab): Perseorangan vs Kerja Sama (untuk Kurir)

3. **Kondisi Pelunasan SEBELUM Kirim (Perseorangan):** Untuk perseorangan, pelunasan sudah dibayar sebelum status finishing. Jadi saat konfirmasi terima/diambil, langsung status 'selesai'.

4. **Kondisi Pelunasan SETELAH Terima (Kerja Sama):** Untuk kerja sama perusahaan, pelunasan baru aktif setelah barang diterima/diambil. Status jadi 'menunggu_verifikasi_lunas', lanjut ke use case Pembayaran Pelunasan.

5. **Method Entity:** Semua method yang dipakai sudah ada di class diagram atau sudah diusulkan sebelumnya:
   - `Order.getById()` ✓
   - `Order.updateStatus()` ✓
   - `Pelanggan.isPelunasanSebelumKirim()` — sudah diusulkan di diagram Pembayaran Pelunasan
   - `Pengiriman.inputResi()` ✓
   - `Pengiriman.konfirmasiDiterima()` ✓
   - `Pengiriman.konfirmasiDiambil()` ✓
   - `Notification.kirim()` ✓

6. **Boundary Objects:**
   - Admin: Halaman Manajemen Pengiriman (halaman khusus Admin untuk manajemen pengiriman)
   - Pelanggan: Halaman Detail Pesanan (halaman yang sama dengan use case lain)

---

## SELF-CHECK PENGIRIMAN

| Item | Status | Catatan |
|---|---|---|
| 1. Method entity cocok dengan class diagram | ✅ LULUS | Semua method ada atau sudah diusulkan sebelumnya |
| 2. Tidak ada Control/Entity langsung ke Aktor | ✅ LULUS | Semua lewat Boundary |
| 3. Notasi kurung konsisten | ✅ LULUS | Sinkron=kurung, return=tanpa kurung |
| 4. Penomoran urut | ✅ LULUS | Semua percabangan berurutan |
| 5. Konsisten dengan Login/Register/Pemesanan/Revisi/Tracking/Pembayaran | ✅ LULUS | Pola sama, tidak ada kontradiksi |

---

## KESIMPULAN SEMUA DIAGRAM SELESAI

Semua 7 Sequence Diagram sudah selesai:
1. ✅ Login
2. ✅ Register
3. ✅ Pemesanan (Main + 2 Detail)
4. ✅ Revisi Desain
5. ✅ Tracking Pemesanan
6. ✅ Pembayaran Pelunasan
7. ✅ Pengiriman (Main + 1 Detail)

**Total Method Baru yang Diusulkan untuk Class Diagram:**
1. `Order.updateHargaCustom(idOrder, hargaFinal, deadline)` — dari Pemesanan
2. `Order.konfirmasiHargaCustom(idOrder, keputusan)` — dari Pemesanan
3. `RevisiDesain.getByOrder(idOrder)` (opsional, getter standar) — dari Tracking
4. `Payment.getByOrder(idOrder)` (opsional, getter standar) — dari Tracking
5. `Pengiriman.getByOrder(idOrder)` (opsional, getter standar) — dari Tracking
6. `Pelanggan.isPelunasanSebelumKirim(dataOrder)` (sudah ada di helper, disarankan pindah ke entity) — dari Pembayaran Pelunasan & Pengiriman

Semua diagram sudah melalui self-check dan LULUS semua kriteria 10 aturan akademis yang diberikan.
