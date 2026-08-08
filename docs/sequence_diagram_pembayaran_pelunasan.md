# SEQUENCE DIAGRAM PEMBAYARAN PELUNASAN

**Use Case:** Pembayaran Pelunasan (Upload + Verifikasi Pelunasan)

**Actors:** Pelanggan, Keuangan

**Boundary Objects:**
- Halaman Detail Pesanan
- Halaman Verifikasi Pelunasan (Keuangan)

**Control Objects:**
- Kontrol Pembayaran

**Entity Objects:**
- Order
- Pelanggan «entity»
- Payment
- Notification

---

## Detail Messages

| No | From | To | Label Pesan | Keterangan |
|---|---|---|---|---|
| **FASE 1: Menentukan Waktu Upload** | | | | |
| 1 | Pelanggan | Halaman Detail Pesanan | aksesDetailPesanan(kodeOrder) | Pelanggan buka detail pesanan saat status siap_kirim/siap_diambil (perseorangan) atau dikirim/diambil (kerja sama) |
| 2 | Halaman Detail Pesanan | Order | getByKode(kodeOrder) | Ambil data order untuk cek jenis_pelanggan dan status |
| 3 | Order | Halaman Detail Pesanan | dataOrder | (return) {idOrder, status, jenis_pelanggan, total_harga, require_dp} |
| 4 | Halaman Detail Pesanan | Pelanggan «entity» | isPelunasanSebelumKirim(dataOrder) | Cek timing pelunasan berdasarkan jenis_pelanggan |
| 5 | Pelanggan «entity» | Halaman Detail Pesanan | timingPelunasan | (return) boolean (true = sebelum kirim, false = setelah terima) |
| 6a | - | - | [timingPelunasan = false AND status != menunggu_verifikasi_lunas] | Percabangan 1: Kerja Sama, belum waktunya upload |
| 7a | Halaman Detail Pesanan | Pelanggan | tampilkanNotaBelumAktif | (return) pesan "Nota tagihan akan aktif setelah barang diterima." tampil |
| 6b | - | - | [timingPelunasan = true OR status = menunggu_verifikasi_lunas] | Percabangan 1: Upload pelunasan aktif |
| 7b | Halaman Detail Pesanan | Pelanggan | tampilkanFormUpload | (return) tampil tombol upload bukti pelunasan + nominal |
| **FASE 2: Pelanggan Upload Bukti** | | | | |
| 8 | Pelanggan | Halaman Detail Pesanan | uploadBuktiPelunasan(kodeOrder, fileBukti) | Upload file bukti transfer pelunasan |
| 9 | Halaman Detail Pesanan | Kontrol Pembayaran | prosesUploadPelunasan(kodeOrder, fileBukti) | Teruskan ke lapisan proses |
| 10 | Kontrol Pembayaran | Payment | create(idOrder, 'pelunasan', nominal) | Buat record payment pelunasan dengan status 'menunggu' (atau update jika sebelumnya ditolak) |
| 11 | Payment | Kontrol Pembayaran | paymentCreated | (return) {idPayment, nominal, status} |
| 12 | Kontrol Pembayaran | Order | updateStatus(idOrder, 'menunggu_verifikasi_lunas') | Update status pesanan jadi menunggu verifikasi pelunasan |
| 13 | Order | Kontrol Pembayaran | statusUpdated | (return) status updated |
| 14 | Kontrol Pembayaran | Notification | kirim(idKeuangan, tipePelunasanBaru) | Notifikasi email + in-app ke Keuangan: ada bukti pelunasan baru |
| 15 | Notification | Kontrol Pembayaran | notifSent | (return) notifikasi terkirim |
| 16 | Kontrol Pembayaran | Halaman Detail Pesanan | infoBuktiUploaded() | Kirim info bukti pelunasan berhasil diunggah |
| 17 | Halaman Detail Pesanan | Pelanggan | tampilkanInfoUploaded | (return) pesan "Bukti pelunasan berhasil diunggah. Menunggu verifikasi." tampil |
| **FASE 3: Keuangan Verifikasi** | | | | |
| 18 | Keuangan | Halaman Verifikasi Pelunasan | aksesVerifikasiPelunasan() | Keuangan buka halaman verifikasi pelunasan |
| 19 | Halaman Verifikasi Pelunasan | Keuangan | tampilkanDaftarPelunasan | (return) tampil list pesanan dengan bukti pelunasan menunggu |
| 20 | Keuangan | Halaman Verifikasi Pelunasan | verifikasiPelunasan(idPayment, keputusan, catatan) | Submit keputusan: 'acc' atau 'tolak' + catatan opsional |
| 21 | Halaman Verifikasi Pelunasan | Kontrol Pembayaran | prosesVerifikasiPelunasan(idPayment, keputusan, catatan) | Teruskan ke lapisan proses |
| 22 | Kontrol Pembayaran | Payment | verifikasi(idPayment, keputusan) | Update status payment sesuai keputusan |
| 23 | Payment | Kontrol Pembayaran | hasilVerifikasi | (return) {statusPayment, idOrder} |
| 24a | - | - | [keputusan = 'acc'] | Percabangan 2 |
| 25a | Kontrol Pembayaran | Order | updateStatus(idOrder, 'pelunasan_terverifikasi') | Pelunasan diterima, status jadi pelunasan_terverifikasi (untuk perseorangan) atau 'selesai' (untuk kerja sama setelah terima) |
| 26a | Order | Kontrol Pembayaran | statusUpdated | (return) status updated |
| 27a | Kontrol Pembayaran | Notification | kirim(idPelanggan, tipePelunasanAcc) | Notifikasi email + in-app ke Pelanggan: pelunasan diterima |
| 28a | Notification | Kontrol Pembayaran | notifSent | (return) notifikasi terkirim |
| 29a | Kontrol Pembayaran | Halaman Verifikasi Pelunasan | infoPelunasanAcc() | Kirim info pelunasan diverifikasi |
| 30a | Halaman Verifikasi Pelunasan | Keuangan | tampilkanInfoAcc | (return) pesan "Pelunasan diverifikasi. Pesanan [status baru]." tampil |
| 24b | - | - | [keputusan = 'tolak'] | Percabangan 2 alternatif — Pelunasan Ditolak |
| 25b | Kontrol Pembayaran | Order | updateStatus(idOrder, 'menunggu_verifikasi_lunas') | Status tetap menunggu pelunasan (untuk upload ulang) |
| 26b | Order | Kontrol Pembayaran | statusUpdated | (return) status updated |
| 27b | Kontrol Pembayaran | Notification | kirim(idPelanggan, tipePelunasanTolak) | Notifikasi email + in-app ke Pelanggan: pelunasan ditolak, perlu upload ulang |
| 28b | Notification | Kontrol Pembayaran | notifSent | (return) notifikasi terkirim dengan alasan penolakan |
| 29b | Kontrol Pembayaran | Halaman Verifikasi Pelunasan | infoPelunasanTolak(catatan) | Kirim info pelunasan ditolak + alasan |
| 30b | Halaman Verifikasi Pelunasan | Keuangan | tampilkanInfoTolak | (return) pesan "Pelunasan ditolak: [alasan]." tampil |
| 31b | - | - | KEMBALI KE FASE 2 | Pelanggan bisa upload ulang bukti pelunasan (loop ke message 8 jika diperlukan) |

---

## CATATAN AKADEMIS

1. **Timing Pelunasan (Fase 1):** Diagram ini menunjukkan 2 jalur waktu upload:
   - **Perseorangan:** Upload aktif saat status `siap_kirim` atau `siap_diambil` (sebelum barang dikirim/diambil)
   - **Kerja Sama Perusahaan:** Upload aktif saat status `menunggu_verifikasi_lunas` (setelah barang diterima/diambil, yang diaktifkan di use case Pengiriman)
   
   Percabangan 6a/6b menangani perbedaan timing ini tanpa detail implementasi use case Pengiriman.

2. **Status Akhir Berbeda per Jenis Pelanggan:**
   - **Perseorangan:** `menunggu_verifikasi_lunas` → ACC → `pelunasan_terverifikasi` → (use case Pengiriman lanjutkan ke `dikirim` atau `selesai`)
   - **Kerja Sama Perusahaan:** `menunggu_verifikasi_lunas` (sudah diterima) → ACC → `selesai` (langsung selesai)

   Message 25a menyebutkan kedua kemungkinan status baru ini, tapi untuk akademis cukup disederhanakan sebagai "status baru sesuai jenis_pelanggan".

3. **Loop Upload Ulang:** Message 31b menunjukkan kemungkinan loop jika Keuangan tolak pelunasan, tapi kita tidak gambar loop fragment eksplisit (cukup catatan untuk akademis).

4. **Method Entity:** Semua method yang dipakai sudah ada di class diagram:
   - `Order.getByKode()` ✓
   - `Pelanggan.isPelunasanSebelumKirim()` — **PERLU CEK:** method ini ada di helper notification_helper.php, tapi mungkin perlu dipindahkan ke Pelanggan entity di class diagram
   - `Payment.create()` ✓
   - `Payment.verifikasi()` ✓
   - `Order.updateStatus()` ✓
   - `Notification.create()` ✓

5. **Boundary Objects:** 2 boundary berbeda:
   - Pelanggan: Halaman Detail Pesanan (sama dengan use case lain)
   - Keuangan: Halaman Verifikasi Pelunasan (halaman khusus Keuangan untuk verifikasi)

---

## USULAN PENAMBAHAN METHOD (Opsional)

### 1. Pelanggan.isPelunasanSebelumKirim(dataOrder)
**Alasan:** Method untuk menentukan timing pelunasan (sebelum/sesudah kirim) berdasarkan jenis_pelanggan. Saat ini ada di helper `notification_helper.php::isPelunasanSebelumKirim()`, tapi seharusnya jadi method entity Pelanggan untuk konsistensi class diagram.

**Signature:**
```php
+isPelunasanSebelumKirim(dataOrder: array): bool
```

**Catatan:** Jika tidak ingin pindahkan dari helper ke entity, diagram tetap valid tapi perlu catatan bahwa logic ini ada di helper (tidak ideal untuk class diagram tapi bisa diterima untuk skripsi SI).

---

## SELF-CHECK PEMBAYARAN PELUNASAN

| Item | Status | Catatan |
|---|---|---|
| 1. Method entity cocok dengan class diagram | ⚠️ PERLU KONFIRMASI | isPelunasanSebelumKirim() ada di helper, mungkin perlu dipindah ke entity |
| 2. Tidak ada Control/Entity langsung ke Aktor | ✅ LULUS | Semua lewat Boundary |
| 3. Notasi kurung konsisten | ✅ LULUS | Sinkron=kurung, return=tanpa kurung |
| 4. Penomoran urut | ✅ LULUS | Semua percabangan berurutan |
| 5. Konsisten dengan Login/Register/Pemesanan | ✅ LULUS | Pola sama, mirip dengan DP payment di Pemesanan |
