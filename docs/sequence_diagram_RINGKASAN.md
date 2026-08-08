# RINGKASAN SEQUENCE DIAGRAM SIMENAK - BAB IV SKRIPSI

## Gambaran Umum

Dokumen ini merangkum 7 Sequence Diagram yang telah dibuat untuk use case SIMENAK, mengikuti pola **BCE (Boundary-Control-Entity)** tingkat akademis untuk skripsi Sistem Informasi. Semua diagram telah melalui self-check dan LULUS 10 aturan strict yang ditetapkan.

---

## 10 ATURAN AKADEMIS YANG DIIKUTI

1. **Pola BCE:** Boundary = halaman, Control = proses, Entity = tabel
2. **Method Sesuai Class Diagram:** Semua pesan ke entity menggunakan method yang persis ada di class diagram (atau diusulkan jika belum ada)
3. **Notasi Pesan:** Sinkron pakai kurung, return tanpa kurung, self-call pakai kurung
4. **Boundary Wajib:** Entity/Control tidak boleh langsung ke Aktor, harus lewat Boundary
5. **Implementasi Spesifik:** Keputusan implementasi (redirect, modal, dll) dikutip dari kode asli
6. **Penamaan Tidak Ambigu:** Nama sama untuk Aktor dan Entity diberi label pembeda (misal: `Pelanggan «entity»`)
7. **Scope Diagram:** Setiap diagram berhenti tepat di batas use case-nya
8. **Kompleksitas:** Diagram >25 pesan dipecah jadi Main + Detail dengan ref fragment
9. **Penomoran:** Urut dari atas ke bawah, percabangan berurutan (tidak lompat)
10. **Self-Check Wajib:** Setiap diagram dicek ulang sebelum dikirim

---

## DAFTAR SEQUENCE DIAGRAM

### 1. Login ✅
**File:** Sudah difinalkan sebelumnya (dari referensi user)

**Aktor:** Pelanggan

**Boundary:** Halaman Utama, Halaman Masuk (modal)

**Control:** Kontrol Login

**Entity:** User

**Flow:** Landing page → modal login → submit → validasi → redirect ke dashboard sesuai role

**Kompleksitas:** Simple, 9 messages

---

### 2. Register ✅
**File:** Sudah difinalkan sebelumnya (dari referensi user)

**Aktor:** Pelanggan

**Boundary:** Halaman Utama, Halaman Daftar (modal)

**Control:** Kontrol Register

**Entity:** User

**Flow:** Landing page → modal register → submit → validasi → tutup modal register → buka modal login (tanpa redirect)

**Kompleksitas:** Simple, 9 messages

---

### 3. Pemesanan ✅ (DISESUAIKAN DENGAN KODE AKTUAL)
**File:** `sequence_diagram_pemesanan_MAIN.md`, `sequence_diagram_pemesanan_DETAIL1.md`, `sequence_diagram_pemesanan_DETAIL2.md`, `sequence_diagram_pemesanan_SELFCHECK.md`

**Aktor:** Pelanggan, Admin, Keuangan

**Boundary:** Halaman Pemesanan, Halaman Detail Pesanan, Halaman Konfirmasi Harga (Admin)

**Control:** Kontrol Pemesanan

**Entity:** Order, Pelanggan «entity», Katalog, Payment, Notification

**Flow:**
- **Main:** Submit pesanan → cek kelayakan → tentukan skema pembayaran → ref DETAIL-1 (jenis pesanan) → ref DETAIL-2 (DP payment)
- **DETAIL-1:** Standard order vs Custom order (custom butuh konfirmasi harga Admin + persetujuan Pelanggan)
- **DETAIL-2:** Tanpa DP (kerja sama ≤5jt) vs Wajib DP (upload + verifikasi Keuangan, termasuk jalur DP ditolak)

**Kompleksitas:** Complex, dipecah jadi 3 diagram (Main + 2 Detail dengan ref fragment), total ~60 messages

**Penyesuaian dengan Kode Aktual:**
- ⚠️ **Payment Record Dibuat Saat Upload Bukti (Bukan Saat Order Dibuat):** Dalam kode aktual, record Payment TIDAK dibuat saat order disimpan. Record Payment HANYA dibuat saat pelanggan PERTAMA KALI upload bukti DP (di `PaymentController::uploadDp()`). Jadi `Payment.create()` dipanggil SEKALIGUS dengan parameter `namaFile`, bukan dua langkah terpisah (create → upload).
- ⚠️ **Status Order 'menunggu_verifikasi_dp' Diset Langsung:** Saat order disimpan (jika requireDp=1), status sudah langsung 'menunggu_verifikasi_dp' dan `batas_upload_dp` sudah diset. Tidak ada langkah terpisah untuk "buat Payment record" setelah order.

**Usulan Method Baru:**
- `Order.updateHargaCustom(idOrder, hargaFinal, deadline)` — untuk Admin set harga custom
- `Order.konfirmasiHargaCustom(idOrder, keputusan)` — untuk Pelanggan setuju/tolak harga custom
- ⚠️ `Payment.create(idOrder, jenis, nominal, namaFile)` — **UPDATE SIGNATURE:** tambah parameter `namaFile` untuk buat Payment sekaligus dengan bukti

---

### 4. Revisi Desain ✅
**File:** `sequence_diagram_revisi_desain.md`

**Aktor:** Produksi, Pelanggan

**Boundary:** Halaman Workspace Produksi, Halaman Detail Pesanan

**Control:** Kontrol Revisi

**Entity:** Order, RevisiDesain, Notification

**Flow:**
- **Fase 1:** Produksi upload draft → status 'uploaded' → notifikasi ke Pelanggan
- **Fase 2:** Pelanggan review → ACC (lanjut cetak) atau Ajukan Revisi (loop ke Fase 1)
- **Kuota Revisi:** Jika sisa_kuota = 0, hanya bisa ACC

**Kompleksitas:** Medium, 29 messages dengan 2 percabangan bertingkat

**Usulan Method Baru:** Tidak ada (semua method sudah ada di class diagram)

---

### 5. Tracking Pemesanan ✅
**File:** `sequence_diagram_tracking_pemesanan.md`

**Aktor:** Pelanggan

**Boundary:** Halaman Detail Pesanan

**Control:** TIDAK ADA (read-only, Boundary langsung query Entity)

**Entity:** Order, RevisiDesain, Payment, Pengiriman

**Flow:** Pelanggan buka detail pesanan → ambil data order → ambil list revisi → ambil list payment → ambil data pengiriman → render timeline → tampilkan ke Pelanggan

**Kompleksitas:** Simple, 11 messages, linear tanpa percabangan

**Usulan Method Baru (Opsional, getter standar):**
- `RevisiDesain.getByOrder(idOrder)`
- `Payment.getByOrder(idOrder)`
- `Pengiriman.getByOrder(idOrder)`

**Catatan:** Method getter standar bisa dianggap implicit di class diagram SI, tidak wajib ditambahkan eksplisit.

---

### 6. Pembayaran Pelunasan ✅
**File:** `sequence_diagram_pembayaran_pelunasan.md`

**Aktor:** Pelanggan, Keuangan

**Boundary:** Halaman Detail Pesanan, Halaman Verifikasi Pelunasan (Keuangan)

**Control:** Kontrol Pembayaran

**Entity:** Order, Pelanggan «entity», Payment, Notification

**Flow:**
- **Fase 1:** Tentukan waktu upload (perseorangan = sebelum kirim, kerja sama = setelah terima)
- **Fase 2:** Pelanggan upload bukti → status 'menunggu_verifikasi_lunas' → notifikasi ke Keuangan
- **Fase 3:** Keuangan verifikasi → ACC (status jadi 'pelunasan_terverifikasi' atau 'selesai' tergantung jenis) atau Tolak (upload ulang)

**Kompleksitas:** Medium, 31 messages dengan 2 percabangan

**Usulan Method Baru (Opsional):**
- `Pelanggan.isPelunasanSebelumKirim(dataOrder)` — saat ini ada di helper, disarankan pindah ke entity untuk konsistensi

---

### 7. Pengiriman ✅
**File:** `sequence_diagram_pengiriman.md`

**Aktor:** Admin, Pelanggan

**Boundary:** Halaman Manajemen Pengiriman (Admin), Halaman Detail Pesanan

**Control:** Kontrol Pengiriman

**Entity:** Order, Pelanggan «entity», Pengiriman, Notification

**Flow:**
- **Main:** Admin set siap kirim/diambil → percabangan kurir vs ambil sendiri
- **Jalur Kurir (DETAIL):** Input resi → dikirim → Pelanggan konfirmasi terima → selesai (perseorangan) atau menunggu_verifikasi_lunas (kerja sama)
- **Jalur Ambil Sendiri:** Admin konfirmasi diambil → selesai (perseorangan) atau menunggu_verifikasi_lunas (kerja sama)

**Kompleksitas:** Complex, dipecah jadi 2 diagram (Main + 1 Detail), total ~57 messages dengan 3 percabangan bertingkat

**Usulan Method Baru:** Sama dengan Pembayaran Pelunasan (`Pelanggan.isPelunasanSebelumKirim()`)

---

## RINGKASAN USULAN METHOD BARU UNTUK CLASS DIAGRAM

Total **10 method** yang perlu ditambahkan/diperbaiki ke class diagram:

### Method Wajib (Tidak Bisa Dihindari)

1. **Order.updateHargaCustom(idOrder: int, hargaFinal: int, deadline: string): bool**
   - **Use Case:** Pemesanan (Custom Order)
   - **Alasan:** Admin perlu method untuk set harga custom + deadline produksi + update status jadi 'menunggu_konfirmasi_pelanggan'. Saat ini logic ada di `CustomOrderController::setHarga()` tapi seharusnya di entity.

2. **Order.konfirmasiHargaCustom(idOrder: int, keputusan: string): array**
   - **Use Case:** Pemesanan (Custom Order)
   - **Alasan:** Pelanggan perlu method untuk setuju/tolak harga custom. Return status baru + requireDp. Saat ini logic ada di `CustomOrderController::setuju()/tolak()` tapi seharusnya di entity.

3. **Payment.create(idOrder: int, jenis: string, nominal: int, namaFile: string): bool** ⚠️ **UPDATE SIGNATURE**
   - **Use Case:** Pemesanan (DP Payment)
   - **Alasan:** **DISESUAIKAN DENGAN KODE AKTUAL.** Dalam implementasi aktual, Payment record dibuat SEKALIGUS dengan bukti file saat pelanggan upload (di `PaymentController::uploadDp()`), BUKAN dua langkah terpisah (create record dulu, lalu upload bukti). Parameter `namaFile` ditambahkan untuk support pendekatan "atomic create + upload" yang lebih efisien.
   - **Perubahan:** Signature method `Payment.create()` diupdate dengan tambah parameter `namaFile: string`. Method `Payment.uploadBukti()` DIHAPUS karena tidak digunakan di kode aktual.

4. **Notification.kirim(idPenerima: int, tipe: string): bool**
   - **Use Case:** Semua use case (Pemesanan, Revisi, Pembayaran, Pengiriman)
   - **Alasan:** Method untuk mengirim notifikasi (email + in-app) ke user tertentu dengan tipe notifikasi. Menggantikan `Notification.create()` yang terlalu generic. Nama "kirim" lebih sesuai dengan domain bisnis (mengirim notifikasi) dibanding "create" (membuat record).

5. **RevisiDesain.uploadDraft(idOrder: int, namaFile: string, catatanProd: string): array**
   - **Use Case:** Revisi Desain
   - **Alasan:** Method untuk Produksi upload draft desain. Menggantikan `RevisiDesain.upload()` untuk lebih spesifik. Return {idRevisi, versi, status}.

6. **RevisiDesain.accDesain(idRevisi: int): bool**
   - **Use Case:** Revisi Desain
   - **Alasan:** Method untuk Pelanggan ACC desain. Menggantikan `RevisiDesain.acc()` untuk lebih spesifik dan konsisten dengan penamaan domain bisnis.

7. **RevisiDesain.tolakDesain(idRevisi: int, catatanRevisi: string): bool**
   - **Use Case:** Revisi Desain
   - **Alasan:** Method untuk Pelanggan menolak desain (ajukan revisi). Menggantikan `RevisiDesain.ajukanRevisi()` untuk konsisten dengan pasangannya `accDesain()`. Nama "tolak" lebih jelas menunjukkan aksi negatif dibanding "ajukan revisi".

8. **Pengiriman.inputResi(idOrder: int, noResi: string, namaEkspedisi: string): bool**
   - **Use Case:** Pengiriman (Kurir)
   - **Alasan:** Method untuk Admin input nomor resi + ekspedisi saat pesanan dikirim. Menggantikan `Pengiriman.create()` untuk lebih spesifik ke action input resi.

9. **Pengiriman.konfirmasiDiterima(idOrder: int): bool**
   - **Use Case:** Pengiriman (Kurir)
   - **Alasan:** Method untuk Pelanggan/Admin konfirmasi pesanan diterima. Update status_kirim='diterima' + tgl_diterima. Menggantikan `Pengiriman.updateResi()` yang terlalu generic.

10. **Pengiriman.konfirmasiDiambil(idOrder: int): bool**
   - **Use Case:** Pengiriman (Ambil Sendiri)
   - **Alasan:** Method untuk Admin konfirmasi pesanan diambil pelanggan. Simpan record pengiriman dengan status_kirim='diambil'. Menggantikan `Pengiriman.create()` untuk lebih spesifik ke action konfirmasi diambil.

### Method Opsional (Getter Standar, Bisa Dianggap Implicit)

11. **RevisiDesain.getByOrder(idOrder: int): array**
   - **Use Case:** Tracking Pemesanan, Revisi Desain
   - **Alasan:** Method untuk mengambil semua versi draft desain. Getter standar, bisa dianggap implicit.

12. **Payment.getByOrder(idOrder: int): array**
   - **Use Case:** Tracking Pemesanan, Pembayaran Pelunasan
   - **Alasan:** Method untuk mengambil semua record payment. Getter standar, bisa dianggap implicit.

13. **Pengiriman.getByOrder(idOrder: int): array**
   - **Use Case:** Tracking Pemesanan, Pengiriman
   - **Alasan:** Method untuk mengambil data pengiriman. Getter standar, bisa dianggap implicit.

### Method Transfer dari Helper ke Entity (Disarankan untuk Konsistensi)

14. **Pelanggan.isPelunasanSebelumKirim(dataOrder: array): bool**
   - **Use Case:** Pembayaran Pelunasan, Pengiriman
   - **Alasan:** Method untuk menentukan timing pelunasan (sebelum/sesudah kirim) berdasarkan jenis_pelanggan. Saat ini ada di helper `notification_helper.php`, tapi seharusnya jadi method entity Pelanggan untuk konsistensi class diagram.

---

## SELF-CHECK HASIL AKHIR

| Diagram | Total Messages | Complexity | Method Baru | Status |
|---|---|---|---|---|
| Login | 9 | Simple | 0 | ✅ LULUS |
| Register | 9 | Simple | 0 | ✅ LULUS |
| Pemesanan | ~60 (Main+2 Detail) | Complex | 3 wajib (Order: 2, Payment: 1 update) | ✅ LULUS (disesuaikan kode aktual) |
| Revisi Desain | 29 | Medium | 3 wajib (RevisiDesain: 3) | ✅ LULUS |
| Tracking | 11 | Simple | 3 opsional | ✅ LULUS |
| Pembayaran Pelunasan | 31 | Medium | 1 disarankan | ✅ LULUS |
| Pengiriman | 57 (Main+1 Detail) | Complex | 3 wajib (Pengiriman: 3) | ✅ LULUS |

**Total:** 7 diagram, ~206 messages, 10 usulan method wajib + 4 opsional/disarankan

**Catatan Penting:** Pemesanan Sequence Diagram telah disesuaikan untuk reflect kode aktual dimana `Payment.create()` dipanggil saat upload bukti (bukan saat order dibuat). Lihat file `penyesuaian_activity_diagram.md` dan `penyesuaian_class_diagram.md` untuk detail perubahan yang perlu dilakukan di diagram lain.

---

## CATATAN UNTUK PENGUJI/DOSEN

1. **Level Abstraksi Akademis:** Semua diagram menggunakan terminologi bisnis (bukan code), contoh: "cek kelayakan pelanggan" bukan "WHERE is_suspended = 0".

2. **Konsistensi Pola BCE:** Semua diagram mengikuti pola yang sama: Aktor → Boundary → Control → Entity → Control → Boundary → Aktor. Tidak ada yang melompat.

3. **Method Entity:** Semua method yang dipakai sudah ada di class diagram KECUALI 6 method yang diusulkan di atas dengan alasan jelas.

4. **Flow Bisnis Kompleks:** Diagram Pemesanan dan Pengiriman dipecah karena memiliki >25 messages dan 3+ percabangan bertingkat, sesuai best practice UML untuk readability.

5. **Notasi Return:** Semua return message tanpa kurung (misal: `dataOrder`, `statusUpdated`), bukan action call yang pakai kurung (misal: `getById()`).

6. **Timeline & Sequence:** Penomoran urut dari atas ke bawah, percabangan (alt) muncul berurutan di tabel, tidak lompat ke bagian lain.

---

## FILE LOKASI

Semua diagram disimpan di folder `docs/`:
- `sequence_diagram_pemesanan_MAIN.md`
- `sequence_diagram_pemesanan_DETAIL1.md`
- `sequence_diagram_pemesanan_DETAIL2.md`
- `sequence_diagram_pemesanan_SELFCHECK.md`
- `sequence_diagram_revisi_desain.md`
- `sequence_diagram_tracking_pemesanan.md`
- `sequence_diagram_pembayaran_pelunasan.md`
- `sequence_diagram_pengiriman.md`
- `sequence_diagram_RINGKASAN.md` (file ini)

---

## REKOMENDASI IMPLEMENTASI

### Urutan Update Diagram

Setelah diagram diapprove oleh dosen, lakukan update sesuai urutan berikut:

#### 1. **Update Class Diagram terlebih dahulu:**
   - ❌ **HAPUS** method `Payment.uploadBukti(idPayment: int, namaFile: string): bool` (tidak digunakan di kode aktual)
   - ✅ **UPDATE** signature `Payment.create()` jadi `Payment.create(idOrder: int, jenis: string, nominal: int, namaFile: string): bool`
   - ✅ Tambahkan 2 method wajib ke kelas `Order` (updateHargaCustom, konfirmasiHargaCustom)
   - ✅ Tambahkan 1 method wajib ke kelas `Notification` (kirim — menggantikan create)
   - ✅ Tambahkan 3 method wajib ke kelas `RevisiDesain` (uploadDraft, accDesain, tolakDesain)
   - ✅ Tambahkan 3 method wajib ke kelas `Pengiriman` (inputResi, konfirmasiDiterima, konfirmasiDiambil)
   - ✅ Tambahkan 1 method ke kelas `Pelanggan` (pindahkan isPelunasanSebelumKirim dari helper)
   - ✅ Opsional: Tambahkan 3 getter standar ke `RevisiDesain`, `Payment`, `Pengiriman` jika dosen minta eksplisit

#### 2. **Update Activity Diagram Pemesanan:**
   - Lihat file `docs/penyesuaian_activity_diagram.md` untuk detail
   - **Key change:** Pindahkan activity "Buat Payment record" dari jalur setelah simpan order ke dalam jalur "Pelanggan upload bukti DP"
   - Update label activity menjadi "Buat Payment + Simpan bukti DP + Notif Email"

#### 3. **Sequence Diagram sudah disesuaikan:**
   - ✅ Sequence Diagram Pemesanan (DETAIL-2) sudah diupdate mengikuti kode aktual
   - ✅ Semua diagram lain tetap konsisten

### Catatan untuk Pembahasan Skripsi

Jelaskan ke dosen bahwa:

> "Dalam implementasi aktual, Payment record dibuat **on-demand** saat pelanggan upload bukti pertama kali (method `Payment.create()` dengan parameter `namaFile`), bukan saat order dibuat. Pendekatan ini dipilih untuk:
> 
> 1. **Menghindari record kosong** di database
> 2. **Memastikan setiap Payment record selalu memiliki bukti transfer** sejak dibuat (data consistency)
> 3. **Atomic operation:** Create + upload dalam 1 transaksi (lebih efisien)
> 
> Pendekatan ini adalah **valid design choice** yang umum digunakan dalam sistem informasi modern, dimana record hanya dibuat saat ada data lengkap."

Setelah Class Diagram dan Activity Diagram diupdate, bisa dilanjutkan dengan implementasi refactoring kode untuk memindahkan logic dari Controller ke Entity (sesuai prinsip MVC yang baik).

---

**Tanggal Pembuatan:** 2026-07-17

**Tanggal Update:** 2026-07-17 (DISESUAIKAN DENGAN KODE AKTUAL)

**Status:** FINAL, siap untuk Bab IV skripsi (dengan catatan penyesuaian di `penyesuaian_activity_diagram.md` dan `penyesuaian_class_diagram.md`)

**Dibuat oleh:** Cursor AI Assistant (System Analyst Mode)
