# SEQUENCE DIAGRAM PEMESANAN - DETAIL-2: Percabangan Pembayaran DP

**Konteks:** Dipanggil dari Diagram Utama setelah DETAIL-1 selesai (hanya untuk jalur yang requireDp=1)

**Actors:** Pelanggan, Keuangan

**Boundary Objects:**
- Halaman Detail Pesanan

**Control Objects:**
- Kontrol Pemesanan

**Entity Objects:**
- Payment
- Order
- Notification

---

## DETAIL-2 — Percabangan Pembayaran DP

| No | From | To | Label Pesan | Keterangan |
|---|---|---|---|---|
| 37a | - | - | [requireDp = 0] | Percabangan 4: Tidak Perlu DP (Kerja Sama Perusahaan ≤ Rp 5jt) |
| 38a | Kontrol Pemesanan | Halaman Detail Pesanan | infoTanpaDP(kodeOrder) | Kirim info pesanan langsung masuk antrian produksi (status terverifikasi) |
| 39a | Halaman Detail Pesanan | Pelanggan | tampilkanInfoTanpaDP | (return) pesan "Pesanan berhasil. Langsung masuk antrian produksi (tanpa DP)." tampil |
| 37b | - | - | [requireDp = 1] | Percabangan 4: Wajib DP 50% |
| 38b | Kontrol Pemesanan | Halaman Detail Pesanan | infoPerluDP(kodeOrder, nominalDp, batasUpload) | Kirim info perlu upload DP + nominal + batas waktu (Order sudah tersimpan dengan status 'menunggu_verifikasi_dp') |
| 39b | Halaman Detail Pesanan | Pelanggan | tampilkanInfoPerluDP | (return) pesan "Pesanan berhasil. Silakan upload bukti DP Rp X dalam 24 jam." tampil dengan form upload |
| 40b | Pelanggan | Halaman Detail Pesanan | uploadBuktiDP(idOrder, fileBukti) | Upload file bukti transfer DP (Payment record belum ada) |
| 41b | Halaman Detail Pesanan | Kontrol Pemesanan | prosesUploadDP(idOrder, fileBukti) | Teruskan ke lapisan proses |
| 42b | Kontrol Pemesanan | Payment | create(idOrder, 'dp', nominalDp, namaFile) | Buat record payment DP baru SEKALIGUS dengan bukti file, status 'menunggu' |
| 43b | Payment | Kontrol Pemesanan | paymentCreated | (return) {idPayment, status} |
| 44b | Kontrol Pemesanan | Notification | kirim(idKeuangan, tipeDpBaru) | Notifikasi email + in-app ke Keuangan: ada bukti DP baru perlu diverifikasi |
| 45b | Notification | Kontrol Pemesanan | notifSent | (return) notifikasi terkirim |
| 46b | Kontrol Pemesanan | Halaman Detail Pesanan | infoBuktiUploaded() | Kirim info bukti DP berhasil diunggah |
| 47b | Halaman Detail Pesanan | Pelanggan | tampilkanInfoUploaded | (return) pesan "Bukti DP berhasil diunggah. Menunggu verifikasi." tampil |
| 48b | Keuangan | Halaman Detail Pesanan | aksesVerifikasiDP(kodeOrder) | Keuangan buka halaman verifikasi DP |
| 49b | Halaman Detail Pesanan | Keuangan | tampilkanFormVerifikasi | (return) tampil bukti DP + tombol ACC/Tolak |
| 50b | Keuangan | Halaman Detail Pesanan | verifikasiDP(idPayment, keputusan) | Submit keputusan: 'acc' atau 'tolak' |
| 51b | Halaman Detail Pesanan | Kontrol Pemesanan | prosesVerifikasiDP(idPayment, keputusan) | Teruskan ke lapisan proses |
| 52b | Kontrol Pemesanan | Payment | verifikasi(idPayment, keputusan) | Update status payment sesuai keputusan |
| 53b | Payment | Kontrol Pemesanan | hasilVerifikasi | (return) {statusPayment, idOrder} |
| 54ba | - | - | [keputusan = 'acc'] | Percabangan 5 |
| 55ba | Kontrol Pemesanan | Order | updateStatus(idOrder, 'terverifikasi') | DP diterima, pesanan masuk antrian produksi |
| 56ba | Order | Kontrol Pemesanan | statusUpdated | (return) status updated |
| 57ba | Kontrol Pemesanan | Notification | kirim(idPelanggan, tipeDpAcc) | Notifikasi email + in-app ke Pelanggan: DP diterima, pesanan diproses |
| 58ba | Notification | Kontrol Pemesanan | notifSent | (return) notifikasi terkirim |
| 59ba | Kontrol Pemesanan | Halaman Detail Pesanan | infoDpAcc() | Kirim info DP diverifikasi |
| 60ba | Halaman Detail Pesanan | Pelanggan | tampilkanInfoDpAcc | (return) pesan "DP Anda telah diverifikasi. Pesanan masuk antrian produksi." tampil |
| 54bb | - | - | [keputusan = 'tolak'] | Percabangan 5 alternatif — DP Ditolak |
| 55bb | Kontrol Pemesanan | Order | updateStatus(idOrder, 'menunggu_verifikasi_dp') | Status tetap menunggu DP (untuk upload ulang) |
| 56bb | Order | Kontrol Pemesanan | statusUpdated | (return) status updated, batas_upload_dp diperpanjang |
| 57bb | Kontrol Pemesanan | Notification | kirim(idPelanggan, tipeDpTolak) | Notifikasi email + in-app ke Pelanggan: DP ditolak, perlu upload ulang |
| 58bb | Notification | Kontrol Pemesanan | notifSent | (return) notifikasi terkirim dengan alasan penolakan |
| 59bb | Kontrol Pemesanan | Halaman Detail Pesanan | infoDpTolak(alasan) | Kirim info DP ditolak + alasan |
| 60bb | Halaman Detail Pesanan | Pelanggan | tampilkanInfoDpTolak | (return) pesan "Bukti DP ditolak: [alasan]. Silakan upload ulang." tampil |
| 61bb | - | - | KEMBALI KE MESSAGE 40b | Pelanggan bisa upload ulang bukti DP (loop ke message 40b jika diperlukan, akan create Payment baru atau update yang ditolak) |

---

## CATATAN AKADEMIS DETAIL-2

1. **Percabangan Pertama (requireDp):** Jalur 37a (tanpa DP) langsung selesai di message 39a. Jalur 37b (wajib DP) berlanjut ke proses upload dan verifikasi.

2. **Payment Record Dibuat Saat Upload (Sesuai Kode Aktual):**
   - **Message 38b-39b:** Order dibuat dengan status='menunggu_verifikasi_dp' dan batas_upload_dp sudah diset. Payment record BELUM dibuat.
   - **Message 40b-43b:** Pelanggan upload bukti DP → Payment record BARU dibuat SEKALIGUS dengan bukti file (method `Payment.create()` dengan parameter file).
   - **Berbeda dari desain ideal:** Dalam desain ideal, Payment record dibuat lebih dulu (saat order dibuat), lalu bukti di-upload kemudian. Tapi dalam implementasi aktual, Payment record baru dibuat saat ada bukti file.

3. **Percabangan Kedua (keputusan verifikasi):**
   - **54ba (ACC):** DP diterima → status jadi 'terverifikasi' → pesanan masuk antrian produksi → diagram selesai.
   - **54bb (TOLAK):** DP ditolak → status tetap 'menunggu_verifikasi_dp' → Pelanggan dapat notifikasi + alasan penolakan → bisa upload ulang (loop balik ke message 40b, akan create Payment record baru atau update yang ditolak).

4. **Loop Upload Ulang:** Message 61bb menunjukkan ada kemungkinan loop jika Pelanggan upload ulang setelah ditolak. Dalam kode aktual, jika Payment dengan status 'ditolak' sudah ada, maka di-UPDATE dengan bukti baru. Jika belum ada atau status lain, maka dibuat Payment baru.

5. **Scope Akhir:** Diagram ini berakhir di salah satu dari 3 kondisi:
   - 39a: Pesanan tanpa DP langsung terverifikasi
   - 60ba: DP ACC → terverifikasi
   - 60bb: DP TOLAK → menunggu upload ulang (belum selesai, tapi di luar scope use case Pemesanan yang fokus sampai pesanan terverifikasi)

6. **Notification Aktor:** Keuangan tidak langsung menerima pesan dari Pelanggan, tapi dari sistem (Notification) yang memberi tahu ada bukti DP baru.

---

**KEMBALI KE DIAGRAM UTAMA:** Setelah DETAIL-2 selesai, control flow kembali ke message 9 diagram utama untuk menampilkan info sukses final ke Pelanggan.
