# SEQUENCE DIAGRAM TRACKING PEMESANAN

**Use Case:** Tracking Pemesanan (Lihat Status & Timeline Pesanan)

**Actors:** Pelanggan

**Boundary Objects:**
- Halaman Detail Pesanan

**Control Objects:**
- TIDAK ADA (use case read-only sederhana, Boundary langsung query Entity)

**Entity Objects:**
- Order
- RevisiDesain
- Payment
- Pengiriman

---

## Detail Messages

| No | From | To | Label Pesan | Keterangan |
|---|---|---|---|---|
| 1 | Pelanggan | Halaman Detail Pesanan | aksesDetailPesanan(kodeOrder) | Pelanggan buka halaman detail pesanan |
| 2 | Halaman Detail Pesanan | Order | getByKode(kodeOrder) | Ambil data order lengkap (status, timeline, deadline, total_harga, dll) |
| 3 | Order | Halaman Detail Pesanan | dataOrder | (return) {idOrder, status, timeline, kodeOrder, total_harga, metode_pengiriman, ...} |
| 4 | Halaman Detail Pesanan | RevisiDesain | getByOrder(idOrder) | Ambil semua versi draft desain untuk pesanan ini |
| 5 | RevisiDesain | Halaman Detail Pesanan | listRevisi | (return) array revisi [{versi, file_draft, status, catatan_prod, catatan_revisi}, ...] |
| 6 | Halaman Detail Pesanan | Payment | getByOrder(idOrder) | Ambil semua record payment (DP & pelunasan) |
| 7 | Payment | Halaman Detail Pesanan | listPayment | (return) array payment [{jenis, nominal, status, tgl_upload, tgl_verifikasi}, ...] |
| 8 | Halaman Detail Pesanan | Pengiriman | getByOrder(idOrder) | Ambil data pengiriman jika ada |
| 9 | Pengiriman | Halaman Detail Pesanan | dataPengiriman | (return) {no_resi, kurir, tgl_kirim, tgl_diterima} atau null jika belum ada |
| 10 | Halaman Detail Pesanan | Halaman Detail Pesanan | renderTimelineStatus() | Self-call: render timeline visual dari data yang dikumpulkan |
| 11 | Halaman Detail Pesanan | Pelanggan | tampilkanDetailPesanan | (return) halaman lengkap tampil: status saat ini, timeline, revisi, payment, pengiriman |

---

## CATATAN AKADEMIS

1. **Use Case Sederhana (Read-Only):** Use case ini tidak mengubah data apa pun, hanya membaca dan menampilkan. Sesuai aturan no. 1 (Kontrol boleh dihilangkan jika use case sangat sederhana/read-only), diagram ini TIDAK memiliki Control object. Boundary langsung query Entity dan render sendiri.

2. **Self-Call renderTimelineStatus():** Message 10 adalah self-call (Boundary memanggil method internal sendiri untuk render timeline visual dari data yang sudah dikumpulkan). Ini menunjukkan bahwa Boundary bukan hanya dumb UI, tapi bisa punya logic rendering.

3. **Sequence Order:** Data diambil berurutan: Order (master data) → RevisiDesain (draft history) → Payment (DP & pelunasan) → Pengiriman (resi & tracking). Urutan ini logis karena setiap entity punya foreign key ke Order (id_order).

4. **Method Entity:** Semua method adalah getter standar:
   - `Order.getByKode()` — method yang mengambil order berdasarkan kode_order
   - `RevisiDesain.getByOrder()` — method yang sudah ada di class diagram (atau bisa disebutkan sebagai method untuk mengambil list revisi)
   - `Payment.getByOrder()` — method untuk mengambil list payment
   - `Pengiriman.getByOrder()` — method untuk mengambil data pengiriman

   **CATATAN:** Method `getByOrder()` mungkin tidak eksplisit ada di class diagram, tapi ini adalah getter standar (SELECT * FROM table WHERE id_order = X) yang bisa dianggap sebagai bagian dari method standar Entity. Jika perlu, kita bisa usulankan sebagai method baru, tapi untuk akademis level skripsi SI, getter seperti ini biasanya dianggap implicit.

5. **Return Type:** Semua return adalah data struktur (object/array), bukan pesan action. Ini konsisten dengan notasi tanpa kurung untuk return message.

6. **Boundary Object:** Hanya 1 Boundary object (Halaman Detail Pesanan) yang sama dengan use case lain. Ini halaman yang sama, tapi untuk use case Tracking fokus ke aspek read-only (lihat status), bukan action (upload, konfirmasi, dll).

---

## USULAN PENAMBAHAN METHOD (Opsional)

Jika dosen penguji ingin method getter eksplisit di class diagram, usulkan method berikut:

### 1. RevisiDesain.getByOrder(idOrder)
**Alasan:** Method untuk mengambil semua versi draft desain untuk pesanan tertentu, digunakan di use case Tracking dan Revisi Desain.

**Signature:**
```php
+getByOrder(idOrder: int): array
```

### 2. Payment.getByOrder(idOrder)
**Alasan:** Method untuk mengambil semua record payment (DP & pelunasan) untuk pesanan tertentu, digunakan di use case Tracking dan Pembayaran Pelunasan.

**Signature:**
```php
+getByOrder(idOrder: int): array
```

### 3. Pengiriman.getByOrder(idOrder)
**Alasan:** Method untuk mengambil data pengiriman untuk pesanan tertentu, digunakan di use case Tracking dan Pengiriman.

**Signature:**
```php
+getByOrder(idOrder: int): array
```

---

## SELF-CHECK TRACKING PEMESANAN

| Item | Status | Catatan |
|---|---|---|
| 1. Method entity cocok dengan class diagram | ⚠️ PERLU KONFIRMASI | Getter standar (getByOrder) mungkin perlu ditambahkan eksplisit, tapi bisa dianggap implicit |
| 2. Tidak ada Control/Entity langsung ke Aktor | ✅ LULUS | Semua lewat Boundary |
| 3. Notasi kurung konsisten | ✅ LULUS | Sinkron=kurung, return=tanpa kurung |
| 4. Penomoran urut | ✅ LULUS | Tidak ada percabangan, urutan linear |
| 5. Konsisten dengan Login/Register/Pemesanan | ✅ LULUS | Pola sama (Boundary langsung query Entity untuk read-only case) |

**Catatan:** Jika dosen ingin method getter eksplisit di class diagram, tambahkan 3 method di atas. Jika method getter dianggap implicit, diagram ini sudah final.
