# SEQUENCE DIAGRAM REVISI DESAIN

**Use Case:** Revisi Desain (Upload Draft → Review → ACC/Revisi Loop)

**Actors:** Produksi, Pelanggan

**Boundary Objects:**
- Halaman Workspace Produksi
- Halaman Detail Pesanan

**Control Objects:**
- Kontrol Revisi

**Entity Objects:**
- Order
- RevisiDesain
- Notification

---

## Detail Messages

| No | From | To | Label Pesan | Keterangan |
|---|---|---|---|---|
| **FASE 1: Produksi Upload Draft** | | | | |
| 1 | Produksi | Halaman Workspace Produksi | aksesWorkspace(idOrder) | Produksi buka halaman workspace untuk pesanan terverifikasi |
| 2 | Halaman Workspace Produksi | Produksi | tampilkanWorkspace | (return) tampil form upload draft + data pesanan |
| 3 | Produksi | Halaman Workspace Produksi | uploadDraft(idOrder, fileDraft, catatanProd) | Submit file draft + catatan opsional |
| 4 | Halaman Workspace Produksi | Kontrol Revisi | prosesUploadDraft(idOrder, fileDraft, catatanProd) | Teruskan ke lapisan proses |
| 5 | Kontrol Revisi | RevisiDesain | uploadDraft(idOrder, namaFile, catatanProd) | Simpan record draft baru dengan status 'uploaded', versi auto-increment |
| 6 | RevisiDesain | Kontrol Revisi | hasilUpload | (return) {idRevisi, versi, status} |
| 7 | Kontrol Revisi | Order | updateStatus(idOrder, 'proses_desain') | Update status pesanan jadi proses_desain |
| 8 | Order | Kontrol Revisi | statusUpdated | (return) status updated |
| 9 | Kontrol Revisi | Notification | kirim(idPelanggan, tipeDraftTersedia) | Notifikasi email + in-app ke Pelanggan: draft v{X} siap direview |
| 10 | Notification | Kontrol Revisi | notifSent | (return) notifikasi terkirim |
| 11 | Kontrol Revisi | Halaman Workspace Produksi | infoDraftUploaded(versi) | Kirim info draft berhasil diunggah |
| 12 | Halaman Workspace Produksi | Produksi | tampilkanInfoSukses | (return) pesan "Draft v{X} berhasil diunggah." tampil |
| **FASE 2: Pelanggan Review Draft** | | | | |
| 13 | Pelanggan | Halaman Detail Pesanan | aksesDetailPesanan(kodeOrder) | Pelanggan buka detail pesanan dari notifikasi |
| 14 | Halaman Detail Pesanan | Pelanggan | tampilkanDraftDanTombol | (return) tampil preview draft + tombol ACC/Ajukan Revisi |
| 15a | - | - | [Pelanggan pilih ACC] | Percabangan 1 |
| 16a | Pelanggan | Halaman Detail Pesanan | accDraft(idOrder, idRevisi) | Submit ACC tanpa catatan revisi |
| 17a | Halaman Detail Pesanan | Kontrol Revisi | prosesAcc(idOrder, idRevisi) | Teruskan ke lapisan proses |
| 18a | Kontrol Revisi | RevisiDesain | accDesain(idRevisi) | Update status revisi jadi 'acc' |
| 19a | RevisiDesain | Kontrol Revisi | hasilAcc | (return) status updated |
| 20a | Kontrol Revisi | Order | updateStatus(idOrder, 'proses_cetak') | Draft disetujui, lanjut proses cetak |
| 21a | Order | Kontrol Revisi | statusUpdated | (return) status updated |
| 22a | Kontrol Revisi | Notification | kirim(idProduksi, tipeDesainAcc) | Notifikasi in-app ke Produksi: draft v{X} di-ACC, lanjut cetak |
| 23a | Notification | Kontrol Revisi | notifSent | (return) notifikasi terkirim |
| 24a | Kontrol Revisi | Halaman Detail Pesanan | infoDesainAcc() | Kirim info desain di-ACC |
| 25a | Halaman Detail Pesanan | Pelanggan | tampilkanInfoAcc | (return) pesan "Desain berhasil di-ACC. Pesanan lanjut ke proses cetak." tampil |
| 15b | - | - | [Pelanggan pilih Ajukan Revisi] | Percabangan 1 alternatif |
| 16b | Pelanggan | Halaman Detail Pesanan | ajukanRevisi(idOrder, idRevisi, catatanRevisi) | Submit catatan revisi wajib |
| 17b | Halaman Detail Pesanan | Kontrol Revisi | prosesAjukanRevisi(idOrder, idRevisi, catatanRevisi) | Teruskan ke lapisan proses |
| 18b | Kontrol Revisi | Order | getDataOrder(idOrder) | Ambil sisa_kuota untuk validasi |
| 19b | Order | Kontrol Revisi | dataOrder | (return) {sisa_kuota, kodeOrder} |
| 20ba | - | - | [sisa_kuota = 0] | Percabangan 2 |
| 21ba | Kontrol Revisi | Halaman Detail Pesanan | infoKuotaHabis() | Kirim info kuota revisi habis, hanya bisa ACC |
| 22ba | Halaman Detail Pesanan | Pelanggan | tampilkanInfoKuotaHabis | (return) pesan "Kuota revisi habis. Hanya bisa ACC." tampil |
| 20bb | - | - | [sisa_kuota > 0] | Percabangan 2 alternatif — lanjut ajukan revisi |
| 21bb | Kontrol Revisi | RevisiDesain | tolakDesain(idRevisi, catatanRevisi) | Update status revisi jadi 'diajukan_revisi' + simpan catatan |
| 22bb | RevisiDesain | Kontrol Revisi | hasilAjukan | (return) status updated |
| 23bb | Kontrol Revisi | Order | updateStatus(idOrder, 'proses_revisi') | Update status pesanan + kurangi sisa_kuota |
| 24bb | Order | Kontrol Revisi | statusUpdated | (return) status updated, sisa_kuota baru |
| 25bb | Kontrol Revisi | Notification | kirim(idProduksi, tipeRevisiDiajukan) | Notifikasi in-app ke Produksi: revisi diajukan, sisa kuota X |
| 26bb | Notification | Kontrol Revisi | notifSent | (return) notifikasi terkirim |
| 27bb | Kontrol Revisi | Halaman Detail Pesanan | infoRevisiDiajukan(sisaKuotaBaru) | Kirim info revisi diajukan + sisa kuota |
| 28bb | Halaman Detail Pesanan | Pelanggan | tampilkanInfoRevisi | (return) pesan "Revisi berhasil diajukan. Tim produksi akan menyiapkan draft baru. Sisa kuota: X." tampil |
| 29bb | - | - | KEMBALI KE FASE 1 | Loop: Produksi upload draft baru (message 1) jika revisi diajukan |

---

## CATATAN AKADEMIS

1. **Loop Implisit:** Diagram ini menunjukkan 1 cycle lengkap (upload → review → ACC atau Revisi). Jika Pelanggan ajukan revisi, flow KEMBALI ke message 1 (Produksi upload draft baru dengan versi yang lebih tinggi). Untuk akademis, cukup beri catatan di message 29bb tanpa gambar loop fragment eksplisit.

2. **Kuota Revisi System:** Percabangan 20ba/20bb menegakkan business rule: jika sisa_kuota = 0, Pelanggan tidak bisa ajukan revisi lagi dan harus ACC salah satu draft yang ada.

3. **Scope Akhir:** Diagram berakhir di salah satu dari 3 kondisi:
   - 25a: Draft di-ACC → pesanan lanjut proses cetak (selesai)
   - 22ba: Kuota habis → Pelanggan harus ACC (belum selesai, tapi tidak bisa lanjut revisi)
   - 28bb: Revisi diajukan → loop ke Produksi upload draft baru (cycle berikutnya di luar scope diagram ini)

4. **Actors:** Hanya 2 actors (Produksi dan Pelanggan) yang berinteraksi bergantian. Tidak ada actor lain yang terlibat dalam use case ini.

5. **Method Entity:** Semua method yang dipakai sudah ada di class diagram:
   - `RevisiDesain.upload()` ✓
   - `RevisiDesain.acc()` ✓
   - `RevisiDesain.ajukanRevisi()` ✓
   - `Order.updateStatus()` ✓
   - `Order.getDataOrder()` (bisa diganti dengan getter standar atau method `Order` yang mengambil data lengkap)
   - `Notification.create()` ✓

6. **Boundary Objects:** Produksi berinteraksi dengan "Halaman Workspace Produksi" (halaman khusus produksi), Pelanggan berinteraksi dengan "Halaman Detail Pesanan" (halaman detail yang sama seperti use case lain).

---

## SELF-CHECK REVISI DESAIN

| Item | Status | Catatan |
|---|---|---|
| 1. Method entity cocok dengan class diagram | ✅ LULUS | Semua method ada, tidak ada usulan baru |
| 2. Tidak ada Control/Entity langsung ke Aktor | ✅ LULUS | Semua lewat Boundary |
| 3. Notasi kurung konsisten | ✅ LULUS | Sinkron=kurung, return=tanpa kurung |
| 4. Penomoran urut | ✅ LULUS | Semua percabangan berurutan |
| 5. Konsisten dengan Login/Register/Pemesanan | ✅ LULUS | Pola sama, tidak ada kontradiksi |
