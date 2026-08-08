# SEQUENCE DIAGRAM PEMESANAN - DETAIL-1: Percabangan Jenis Pesanan

**Konteks:** Dipanggil dari Diagram Utama setelah message 8b (getPaymentScheme selesai)

**Actors:** Pelanggan, Admin

**Boundary Objects:**
- Halaman Detail Pesanan
- Halaman Konfirmasi Harga (Admin)

**Control Objects:**
- Kontrol Pemesanan

**Entity Objects:**
- Order
- Notification

---

## DETAIL-1 — Percabangan Jenis Pesanan

| No | From | To | Label Pesan | Keterangan |
|---|---|---|---|---|
| 11a | - | - | [is_custom = 0] | Percabangan 2: Pesanan Standard |
| 12a | Kontrol Pemesanan | Order | create(dataPesanan, schemeData) | Simpan pesanan standard dengan status sesuai schemeData.statusAwal |
| 13a | Order | Kontrol Pemesanan | hasilCreate | (return) {idOrder, kodeOrder, status} |
| 11b | - | - | [is_custom = 1] | Percabangan 2: Pesanan Custom |
| 12b | Kontrol Pemesanan | Order | create(dataPesanan, statusCustom) | Simpan pesanan custom dengan status = 'menunggu_konfirmasi_harga' |
| 13b | Order | Kontrol Pemesanan | hasilCreate | (return) {idOrder, kodeOrder, status} |
| 14b | Kontrol Pemesanan | Notification | kirim(idAdmin, tipeCustomBaru) | Notifikasi in-app ke semua Admin: ada pesanan custom baru |
| 15b | Notification | Kontrol Pemesanan | notifSent | (return) notifikasi terkirim |
| 16b | Admin | Halaman Konfirmasi Harga | aksesHalamanKonfirmasi(idOrder) | Admin buka halaman list pemesanan, tab custom, klik aksi "Set Harga" |
| 17b | Halaman Konfirmasi Harga | Admin | tampilkanFormSetHarga | (return) modal form set harga tampil |
| 18b | Admin | Halaman Konfirmasi Harga | setHarga(idOrder, hargaFinal, deadline, catatan) | Submit harga custom + deadline produksi + catatan opsional |
| 19b | Halaman Konfirmasi Harga | Kontrol Pemesanan | prosesSetHarga(idOrder, hargaFinal, deadline, catatan) | Teruskan ke lapisan proses |
| 20b | Kontrol Pemesanan | Order | updateHargaCustom(idOrder, hargaFinal, deadline) | Update harga, estimasi, deadline, status = 'menunggu_konfirmasi_pelanggan' *[METHOD BARU]* |
| 21b | Order | Kontrol Pemesanan | hasilUpdate | (return) status update sukses |
| 22b | Kontrol Pemesanan | Notification | kirim(idPelanggan, tipeKonfirmasiHarga) | Notifikasi email + in-app ke Pelanggan: harga sudah dikonfirmasi |
| 23b | Notification | Kontrol Pemesanan | notifSent | (return) notifikasi terkirim |
| 24b | Pelanggan | Halaman Detail Pesanan | aksesDetailPesanan(kodeOrder) | Pelanggan buka halaman detail pesanan dari email/notifikasi |
| 25b | Halaman Detail Pesanan | Pelanggan | tampilkanKonfirmasiHarga | (return) tampil info harga + tombol Setuju/Tolak |
| 26b | Pelanggan | Halaman Detail Pesanan | konfirmasiHarga(idOrder, keputusan) | Submit keputusan: 'setuju' atau 'tolak' |
| 27b | Halaman Detail Pesanan | Kontrol Pemesanan | prosesKonfirmasiHarga(idOrder, keputusan) | Teruskan ke lapisan proses |
| 28b | Kontrol Pemesanan | Order | konfirmasiHargaCustom(idOrder, keputusan) | Proses konfirmasi sesuai keputusan *[METHOD BARU]* |
| 29b | Order | Kontrol Pemesanan | hasilKonfirmasi | (return) {status, requireDp, statusBaru} |
| 30ba | - | - | [keputusan = 'tolak'] | Percabangan 3 |
| 31ba | Kontrol Pemesanan | Order | updateStatus(idOrder, 'dibatalkan') | Update status pesanan jadi dibatalkan |
| 32ba | Order | Kontrol Pemesanan | statusUpdated | (return) status updated |
| 33ba | Kontrol Pemesanan | Notification | kirim(idAdmin, tipePesananDibatalkan) | Notifikasi email + in-app ke Admin: pelanggan menolak harga |
| 34ba | Notification | Kontrol Pemesanan | notifSent | (return) notifikasi terkirim |
| 35ba | Kontrol Pemesanan | Halaman Detail Pesanan | infoDibatalkan() | Kirim info pesanan dibatalkan |
| 36ba | Halaman Detail Pesanan | Pelanggan | tampilkanInfoDibatalkan | (return) pesan "Harga ditolak. Pesanan dibatalkan." tampil |
| 30bb | - | - | [keputusan = 'setuju'] | Percabangan 3 alternatif — lanjut ke skema pembayaran |
| 31bb | Kontrol Pemesanan | Order | updateStatus(idOrder, statusBaru) | Update status sesuai schemeData.statusAwal (menunggu_verifikasi_dp atau terverifikasi) |
| 32bb | Order | Kontrol Pemesanan | statusUpdated | (return) status updated, data order lengkap termasuk requireDp |
| 33bb | - | - | KEMBALI KE DIAGRAM UTAMA | Lanjut ke ref: DETAIL-2 Percabangan Pembayaran DP (message setelah ref DETAIL-1 di diagram utama) |

---

## CATATAN AKADEMIS DETAIL-1

1. **Actor Admin:** Muncul di tengah diagram karena custom order butuh intervensi Admin untuk set harga. Admin tidak langsung menerima pesan dari Pelanggan, tapi dari sistem (Notification) yang memberi tahu ada custom order baru.

2. **Method Baru:** `updateHargaCustom()` dan `konfirmasiHargaCustom()` sudah diusulkan di bagian USULAN CLASS DIAGRAM. Kedua method ini membungkus logic kompleks yang saat ini ada di `CustomOrderController::setHarga()` dan `CustomOrderController::setuju()/tolak()`.

3. **Percabangan Bertingkat:** Jalur custom (11b) memiliki sub-percabangan lagi (30ba/30bb) untuk keputusan pelanggan. Jika tolak, diagram berakhir di message 36ba. Jika setuju, flow KEMBALI ke diagram utama untuk melanjutkan ke DETAIL-2 (DP payment).

4. **Boundary Object:** Halaman Konfirmasi Harga hanya diakses Admin. Pelanggan tetap di Halaman Detail Pesanan untuk konfirmasi setuju/tolak.
