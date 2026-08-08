# Usulan Strategi Kodifikasi Data — SIMENAK

**Kepada:** Ibu Dosen Pembimbing  
**Dari:** Mahasiswa (SIMENAK — Sistem Informasi Pemesanan Percetakan Z'Plack)  
**Perihal:** Penyempurnaan kodifikasi identitas data sesuai saran bimbingan  

---

## 1. Latar Belakang

Mengikuti saran Ibu pada bimbingan sebelumnya mengenai pentingnya **kodifikasi** agar identitas data tidak ambigu saat pengujian, pelaporan, dan operasional, berikut usulan penyempurnaan yang kami susun.

Prinsip yang diusulkan:

1. **Primary key teknis** (`id_*`) tetap menggunakan `AUTO_INCREMENT` untuk menjaga integritas relasi antar tabel (foreign key) dan kinerja join.
2. **Kodifikasi bisnis** ditambahkan sebagai kolom `kode_*` yang **unik**, dibaca manusia, dan dipakai di antarmuka, laporan, serta skenario pengujian.
3. Dengan demikian, saat pengujian kita menyebut misalnya `ORD-20260607-0002` atau `KAT-2026-0003`, bukan sekadar angka `1` yang dapat membingungkan antar entitas.

Pola ini disebut **hybrid identity** (surrogate key + business code) dan umum digunakan pada sistem informasi berbasis RDBMS.

---

## 2. Kodifikasi yang Sudah Ada

| Entitas | Primary Key Teknis | Kode Bisnis | Contoh | Status |
|---------|--------------------|-------------|--------|--------|
| Pesanan (`orders`) | `id_order` AI | `kode_order` UNIQUE | `ORD-20260607-0002` | Sudah diterapkan |
| Pembayaran (`payments`) | `id_payment` AI | `kode_payment` UNIQUE | `PAY-20260607-0001` | Sudah diterapkan |

Format yang berlaku:
- Pesanan: `ORD-YYYYMMDD-XXXX`
- Pembayaran: `PAY-YYYYMMDD-XXXX`

---

## 3. Usulan Perluasan Kodifikasi

Sesuai arahan Ibu (katalog, pelanggan/pengguna, revisi desain, pengiriman), diusulkan penambahan sebagai berikut:

| No | Entitas | Kolom Kode | Format Usulan | Contoh | Keterangan |
|----|---------|------------|---------------|--------|------------|
| 1 | Katalog | `kode_katalog` | `KAT-YYYY-XXXX` | `KAT-2026-0003` | Unik per produk katalog |
| 2 | Pelanggan | `kode_pelanggan` | `PLG-XXXX` | `PLG-0007` | Unik per akun pelanggan |
| 3 | User internal (staf) | `kode_user` | `USR-{ROLE}-XXXX` | `USR-ADM-0001`, `USR-KEU-0002` | Role: ADM / KEU / PRD / OWN |
| 4 | Revisi desain | `kode_revisi` | `REV-{kode_order}-V{n}` | `REV-ORD-20260607-0002-V01` | Terikat ke pesanan + nomor versi |
| 5 | Pengiriman | `kode_kirim` | `KRM-{kode_order}` | `KRM-ORD-20260607-0002` | 1 pesanan : 1 pengiriman |

**Catatan ruang lingkup:**  
Entitas teknis murni seperti `notifications`, `order_attributes`, `form_templates`, dan `password_resets` **tidak** diberi kodifikasi bisnis, karena tidak ditampilkan sebagai identitas utama kepada pengguna/penguji.

---

## 4. Manfaat untuk Pengujian dan Operasional

| Sebelum | Sesudah |
|---------|---------|
| “Tolong cek data 1” → ambigu (user? katalog? order?) | “Tolong cek `KAT-2026-0003` / `ORD-20260607-0002`” → jelas |
| Laporan hanya menampilkan angka internal | Laporan menampilkan kode bisnis yang dapat dilacak |
| Demo skripsi rentan salah sebut identitas | Setiap objek bisnis punya label unik yang konsisten |

---

## 5. Rencana Implementasi (Ringkas)

1. Menambahkan kolom `kode_*` (VARCHAR, UNIQUE) pada tabel terkait melalui **migration** resmi CodeIgniter 4.
2. Membuat fungsi generator kode (mengikuti pola `generateKodeOrder()` / `generateKodePayment()` yang sudah ada).
3. Mengisi kode untuk data lama (*backfill*) agar data uji tetap konsisten.
4. Menampilkan kode bisnis pada UI daftar/detail yang relevan.
5. Mendokumentasikan aturan kodifikasi pada bab perancangan basis data skripsi.

Estimasi dampak: **terbatas dan terukur** — tidak mengubah foreign key inti, tidak merombak seluruh primary key, dan tidak mengubah alur bisnis yang sudah berjalan.

---

## 6. Permohonan Arahan

Mohon arahan Ibu terkait:

1. Apakah format kode pada Tabel 3 sudah sesuai harapan Ibu?
2. Apakah prioritas implementasi (katalog → pelanggan → revisi → pengiriman) dapat disetujui?
3. Apakah pola hybrid (`id_*` AI + `kode_*` UNIQUE) dapat diterima sebagai pendekatan kodifikasi pada SIMENAK?

Atas bimbingan Ibu, kami ucapkan terima kasih.

---

*Dokumen ini bersifat usulan untuk disepakati sebelum perubahan skema diterapkan.*
