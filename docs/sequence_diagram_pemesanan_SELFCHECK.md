# SELF-CHECK SEQUENCE DIAGRAM PEMESANAN

Sesuai aturan mutlak yang diberikan, berikut hasil self-check untuk Sequence Diagram Pemesanan (Main + 2 Detail):

## ✅ Checklist Item 1: Semua pesan ke entity sudah dicocokkan ke class diagram

**Total pesan ke entity yang dicek:** 17 pesan

| No Pesan | Pesan | Entity Target | Method di Class Diagram | Status |
|---|---|---|---|---|
| 4 | getById(idKatalog) | Katalog | ✓ Ada | OK |
| 5 | canCreateOrder() | Pelanggan «entity» | ✓ Ada | OK |
| 7b | getPaymentScheme(totalHarga) | Pelanggan «entity» | ✓ Ada | OK |
| 12a | create(dataPesanan, schemeData) | Order | ✓ Ada | OK |
| 12b | create(dataPesanan, statusCustom) | Order | ✓ Ada | OK |
| 14b | create(idAdmin, tipeCustomBaru) | Notification | ✓ Ada | OK |
| 20b | updateHargaCustom(idOrder, hargaFinal, deadline) | Order | ⚠️ **METHOD BARU** | Diusulkan |
| 22b | create(idPelanggan, tipeKonfirmasiHarga) | Notification | ✓ Ada | OK |
| 28b | konfirmasiHargaCustom(idOrder, keputusan) | Order | ⚠️ **METHOD BARU** | Diusulkan |
| 31ba | updateStatus(idOrder, 'dibatalkan') | Order | ✓ Ada | OK |
| 31bb | updateStatus(idOrder, statusBaru) | Order | ✓ Ada | OK |
| 33ba | create(idAdmin, tipePesananDibatalkan) | Notification | ✓ Ada | OK |
| 38b | create(idOrder, 'dp', nominalDp) | Payment | ✓ Ada | OK |
| 40b | updateStatus(idOrder, 'menunggu_verifikasi_dp') | Order | ✓ Ada | OK |
| 46b | uploadBukti(idPayment, namaFile) | Payment | ✓ Ada | OK |
| 48b | create(idKeuangan, tipeDpBaru) | Notification | ✓ Ada | OK |
| 54b | verifikasi(idPayment, keputusan) | Payment | ✓ Ada | OK |
| 57ba | updateStatus(idOrder, 'terverifikasi') | Order | ✓ Ada | OK |
| 57bb | updateStatus(idOrder, 'menunggu_verifikasi_dp') | Order | ✓ Ada | OK |
| 59ba | create(idPelanggan, tipeDpAcc) | Notification | ✓ Ada | OK |
| 59bb | create(idPelanggan, tipeDpTolak) | Notification | ✓ Ada | OK |

**Hasil:** 15 method sudah ada di class diagram, 2 method baru diusulkan dengan alasan jelas (sudah ditulis di bagian USULAN PENAMBAHAN METHOD).

---

## ✅ Checklist Item 2: Tidak ada pesan Control/Entity langsung ke Aktor

**Total interaksi yang dicek:** 62 pesan

**Semua pesan yang menuju Aktor (Pelanggan, Admin, Keuangan) SELALU lewat Boundary terlebih dahulu:**
- Pesan dari Control → Boundary → Aktor: ✓
- Pesan dari Entity → Control → Boundary → Aktor: ✓

**Contoh yang sudah benar:**
- Message 7a: `Kontrol Pemesanan → Halaman Pemesanan → Pelanggan`
- Message 17b: `Halaman Konfirmasi Harga → Admin`
- Message 51b: `Halaman Detail Pesanan → Keuangan`

**Tidak ada pesan yang melompati Boundary.**

---

## ✅ Checklist Item 3: Notasi kurung konsisten

**Pesan Sinkron (dengan kurung):** ✓ Semua pesan yang memanggil aksi pakai kurung
- Contoh: `aksesHalaman()`, `submitPesanan(dataPesanan)`, `create(...)`, `updateStatus(...)`

**Pesan Return (tanpa kurung):** ✓ Semua pesan balasan tanpa kurung
- Contoh: `tampilkanFormPemesanan`, `dataKatalog`, `statusKelayakan`, `hasilCreate`, `notifSent`

**Self-call (dengan kurung):** Tidak ada self-call di diagram ini, semua pesan dikirim ke objek lain.

---

## ✅ Checklist Item 4: Penomoran urut tanpa lompat balik

**Struktur penomoran:**
- Main: 1 → 10 (dengan percabangan 6a/6b)
- Detail-1: 11a/11b → 36ba/36bb (dengan sub-percabangan 30ba/30bb)
- Detail-2: 37a/37b → 63bb (dengan percabangan 56ba/56bb)

**Semua percabangan (alt) muncul berurutan:**
- Percabangan 1 (6a/6b): baris berurutan di tabel
- Percabangan 2 (11a/11b): baris berurutan di tabel
- Percabangan 3 (30ba/30bb): baris berurutan di tabel
- Percabangan 4 (37a/37b): baris berurutan di tabel
- Percabangan 5 (56ba/56bb): baris berurutan di tabel

**Tidak ada penomoran yang melompat ke bagian lain tabel.**

---

## ✅ Checklist Item 5: Tidak ada kontradiksi dengan Login/Register

**Diagram Login:** Menggunakan pola yang sama:
- Actor → Boundary → Control → Entity → Control → Boundary → Actor ✓
- Boundary tidak pernah skip Control untuk kirim ke Entity ✓
- Setiap kali tampil sesuatu ke layar, ada pesan return eksplisit ✓

**Diagram Register:** Menggunakan pola yang sama:
- Actor → Boundary → Control → Entity → Control → Boundary → Actor ✓
- Method `User.register()` sesuai class diagram ✓

**Diagram Pemesanan:** Mengikuti pola yang sama secara konsisten:
- Semua interaksi lewat Control (Kontrol Pemesanan) ✓
- Semua method entity sesuai class diagram (kecuali 2 method baru yang sudah diusulkan) ✓
- Semua tampilan ke layar ada return message eksplisit ✓

**Tidak ada kontradiksi.**

---

## 📋 Ringkasan Self-Check

| Item | Status | Catatan |
|---|---|---|
| 1. Method entity cocok dengan class diagram | ✅ LULUS | 15/17 ada, 2 diusulkan dengan alasan |
| 2. Tidak ada Control/Entity langsung ke Aktor | ✅ LULUS | Semua lewat Boundary |
| 3. Notasi kurung konsisten | ✅ LULUS | Sinkron=kurung, return=tanpa kurung |
| 4. Penomoran urut | ✅ LULUS | Semua percabangan berurutan |
| 5. Konsisten dengan Login/Register | ✅ LULUS | Pola sama, tidak ada kontradiksi |

---

## 🎯 Kesimpulan

Sequence Diagram Pemesanan (Main + 2 Detail) LULUS semua self-check dan siap digunakan untuk dokumentasi skripsi Bab IV.

**2 Method Baru yang Perlu Ditambahkan ke Class Diagram:**
1. `Order.updateHargaCustom(idOrder: int, hargaFinal: int, deadline: string): bool`
2. `Order.konfirmasiHargaCustom(idOrder: int, keputusan: string): array`

Kedua method ini sudah dijelaskan alasan dan signaturenya di file `sequence_diagram_pemesanan_MAIN.md` bagian USULAN PENAMBAHAN METHOD.
