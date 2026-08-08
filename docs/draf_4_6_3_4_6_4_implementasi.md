# Draf Bab Implementasi — SIMENAK
## Subbab 4.6.3 Implementasi Basis Data & 4.6.4 Implementasi Antarmuka

> **Catatan penggunaan:** salin ke dokumen skripsi, sesuaikan nomor tabel (contoh: Tabel 4.11) dengan urutan tabel di bab Anda. Sintaks SQL mengikuti MySQL/MariaDB. Nama file antarmuka memakai ekstensi `.php` karena SIMENAK dibangun dengan CodeIgniter 4 (bukan Laravel Blade).

---

### 4.6.3 Implementasi Basis Data

Pada SIMENAK (Sistem Informasi Pemesanan Percetakan Z’Plack), server basis data yang digunakan adalah MySQL/MariaDB yang dikelola melalui phpMyAdmin, sedangkan akses dari aplikasi dilakukan melalui Query Builder CodeIgniter 4. Skema basis data diimplementasikan dan dilacak melalui *migration* resmi pada folder `app/Database/Migrations/`, sehingga perubahan struktur tetap terdokumentasi dan dapat dijalankan ulang dengan perintah `php spark migrate`. Berikut implementasi basis data SIMENAK menggunakan MySQL.

**1. Tabel Users**

Tabel `users` menyimpan akun autentikasi seluruh peran (pelanggan, admin, keuangan, produksi, dan owner), termasuk kodifikasi bisnis (`kode_user`) serta flag wajib ganti password untuk staf baru.

```sql
CREATE TABLE `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `google_id` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('pelanggan','admin','keuangan','produksi','owner') NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `wajib_ganti_password` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `kode_user` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `google_id` (`google_id`),
  UNIQUE KEY `uq_users_kode_user` (`kode_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**2. Tabel Pelanggan**

Tabel `pelanggan` menyimpan profil pelanggan yang terhubung ke `users`, termasuk status kerja sama perusahaan dan kodifikasi `kode_pelanggan`.

```sql
CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `kode_pelanggan` varchar(20) DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat` varchar(150) DEFAULT NULL,
  `jenis` enum('perseorangan','perusahaan') DEFAULT 'perseorangan',
  `nama_perusahaan` varchar(150) DEFAULT NULL,
  `is_verified` tinyint(4) DEFAULT 0,
  `is_suspended` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_pelanggan`),
  UNIQUE KEY `uq_pelanggan_kode_pelanggan` (`kode_pelanggan`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `pelanggan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**3. Tabel Verifikasi Perusahaan**

Tabel `verifikasi_perusahaan` menyimpan arsip penetapan/pencabutan kerja sama perusahaan oleh Admin (bukan antrian verifikasi Owner).

```sql
CREATE TABLE `verifikasi_perusahaan` (
  `id_verify` int(11) NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int(11) NOT NULL,
  `nama_perusahaan` varchar(150) NOT NULL,
  `no_npwp` varchar(30) DEFAULT NULL,
  `jabatan_pic` varchar(100) DEFAULT NULL,
  `wa_perusahaan` varchar(20) DEFAULT NULL,
  `alamat_kantor` varchar(255) DEFAULT NULL,
  `dokumen_npwp` varchar(255) DEFAULT NULL,
  `dokumen_ktp_pic` varchar(255) DEFAULT NULL,
  `dokumen_mou` varchar(255) DEFAULT NULL,
  `status` enum('verified','rejected') NOT NULL DEFAULT 'verified',
  `catatan_admin` text DEFAULT NULL,
  `tgl_pengajuan` datetime DEFAULT current_timestamp(),
  `tgl_verifikasi` datetime DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_verify`),
  KEY `id_pelanggan` (`id_pelanggan`),
  KEY `id_admin` (`id_admin`),
  CONSTRAINT `verifikasi_perusahaan_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`),
  CONSTRAINT `verifikasi_perusahaan_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**4. Tabel Katalog**

Tabel `katalog` menyimpan master produk percetakan beserta parameter minimum order, kuota revisi default, dan kodifikasi `kode_katalog`.

```sql
CREATE TABLE `katalog` (
  `id_katalog` int(11) NOT NULL AUTO_INCREMENT,
  `kode_katalog` varchar(20) DEFAULT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `kategori` enum('desain_grafis','cetak_digital','cetak_offset','media_promosi') NOT NULL,
  `harga_dasar` decimal(12,2) NOT NULL,
  `kuota_revisi_default` int(11) NOT NULL DEFAULT 2,
  `min_order` int(11) NOT NULL DEFAULT 1,
  `satuan` varchar(30) NOT NULL DEFAULT 'pcs',
  `estimasi_hari` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id_katalog`),
  UNIQUE KEY `uq_katalog_kode_katalog` (`kode_katalog`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**5. Tabel Form Templates**

Tabel `form_templates` mendefinisikan field dinamis (pola EAV) per produk katalog.

```sql
CREATE TABLE `form_templates` (
  `id_template` int(11) NOT NULL AUTO_INCREMENT,
  `id_katalog` int(11) NOT NULL,
  `field_key` varchar(50) NOT NULL,
  `field_label` varchar(100) NOT NULL,
  `field_type` enum('text','date','time','textarea','file') NOT NULL,
  `placeholder` varchar(150) DEFAULT NULL,
  `is_required` tinyint(4) DEFAULT 1,
  `urutan` int(11) DEFAULT 0,
  PRIMARY KEY (`id_template`),
  UNIQUE KEY `uq_field` (`id_katalog`,`field_key`),
  KEY `id_katalog` (`id_katalog`),
  CONSTRAINT `form_templates_ibfk_1` FOREIGN KEY (`id_katalog`) REFERENCES `katalog` (`id_katalog`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**6. Tabel Orders**

Tabel `orders` menyimpan data pemesanan, skema pembayaran, kuota revisi, status alur produksi/pengiriman, serta batasan unggah DP.

```sql
CREATE TABLE `orders` (
  `id_order` int(11) NOT NULL AUTO_INCREMENT,
  `kode_order` varchar(25) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `id_katalog` int(11) NOT NULL,
  `jenis_pelanggan` enum('perseorangan','perusahaan') NOT NULL,
  `is_custom` tinyint(4) DEFAULT 0,
  `jumlah_order` int(11) NOT NULL DEFAULT 1,
  `catatan_custom` text DEFAULT NULL,
  `harga_custom` decimal(12,2) DEFAULT NULL,
  `estimasi_custom` varchar(50) DEFAULT NULL,
  `estimasi_hari` int(10) unsigned DEFAULT NULL,
  `catatan_admin_custom` text DEFAULT NULL,
  `referensi_desain` varchar(255) DEFAULT NULL,
  `detail_pesanan` text DEFAULT NULL,
  `deadline_diajukan` date DEFAULT NULL,
  `deadline_produksi` date DEFAULT NULL,
  `metode_pengiriman` enum('kurir','ambil_sendiri') NOT NULL,
  `alamat_kirim` text DEFAULT NULL,
  `kuota_revisi` int(11) NOT NULL,
  `sisa_kuota` int(11) NOT NULL,
  `total_harga` decimal(12,2) DEFAULT NULL,
  `require_dp` tinyint(4) DEFAULT 1,
  `status` enum(
    'menunggu_konfirmasi_harga',
    'menunggu_konfirmasi_pelanggan',
    'menunggu_verifikasi_dp',
    'terverifikasi',
    'proses_desain',
    'proses_revisi',
    'proses_cetak',
    'finishing',
    'siap_kirim',
    'siap_diambil',
    'dikirim',
    'pesanan_diterima',
    'menunggu_verifikasi_lunas',
    'pelunasan_terverifikasi',
    'selesai',
    'dibatalkan'
  ) NOT NULL DEFAULT 'menunggu_verifikasi_dp',
  `created_at` datetime DEFAULT current_timestamp(),
  `batas_upload_dp` datetime DEFAULT NULL,
  `reminder_dp_sent` tinyint(1) NOT NULL DEFAULT 0,
  `reminder_harga_eskalasi_sent` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_order`),
  UNIQUE KEY `kode_order` (`kode_order`),
  KEY `id_pelanggan` (`id_pelanggan`),
  KEY `id_katalog` (`id_katalog`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`id_katalog`) REFERENCES `katalog` (`id_katalog`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**7. Tabel Order Attributes**

Tabel `order_attributes` menyimpan jawaban field dinamis per pesanan (EAV).

```sql
CREATE TABLE `order_attributes` (
  `id_attr` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` int(11) NOT NULL,
  `attribute_key` varchar(50) NOT NULL,
  `attribute_val` text DEFAULT NULL,
  PRIMARY KEY (`id_attr`),
  UNIQUE KEY `uq_order_attr` (`id_order`,`attribute_key`),
  CONSTRAINT `order_attributes_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**8. Tabel Payments**

Tabel `payments` menyimpan transaksi DP dan pelunasan beserta status verifikasi keuangan.

```sql
CREATE TABLE `payments` (
  `id_payment` int(11) NOT NULL AUTO_INCREMENT,
  `kode_payment` varchar(25) NOT NULL,
  `id_order` int(11) NOT NULL,
  `jenis` enum('dp','pelunasan') NOT NULL,
  `nominal` decimal(12,2) NOT NULL,
  `bukti_tf` varchar(255) DEFAULT NULL,
  `status` enum('menunggu','terverifikasi','ditolak') DEFAULT 'menunggu',
  `catatan_tolak` text DEFAULT NULL,
  `id_verifikator` int(11) DEFAULT NULL,
  `tgl_upload` datetime DEFAULT current_timestamp(),
  `tgl_verifikasi` datetime DEFAULT NULL,
  `reminder_verif_2j_sent` tinyint(1) NOT NULL DEFAULT 0,
  `reminder_verif_6j_sent` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_payment`),
  UNIQUE KEY `kode_payment` (`kode_payment`),
  KEY `id_order` (`id_order`),
  KEY `id_verifikator` (`id_verifikator`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`),
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`id_verifikator`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**9. Tabel Revisi Desain**

Tabel `revisi_desain` menyimpan riwayat versi draft desain beserta status ACC/tolak dan kodifikasi `kode_revisi`.

```sql
CREATE TABLE `revisi_desain` (
  `id_revisi` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` int(11) NOT NULL,
  `id_produksi` int(11) NOT NULL,
  `versi` int(11) NOT NULL DEFAULT 1,
  `kode_revisi` varchar(60) DEFAULT NULL,
  `file_draft` varchar(255) NOT NULL,
  `catatan_prod` text DEFAULT NULL,
  `catatan_revisi` text DEFAULT NULL,
  `status` enum('uploaded','diajukan_revisi','acc','ditolak') DEFAULT 'uploaded',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_revisi`),
  UNIQUE KEY `uq_revisi_desain_kode_revisi` (`kode_revisi`),
  KEY `id_order` (`id_order`),
  KEY `id_produksi` (`id_produksi`),
  CONSTRAINT `revisi_desain_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`),
  CONSTRAINT `revisi_desain_ibfk_2` FOREIGN KEY (`id_produksi`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**10. Tabel Pengiriman**

Tabel `pengiriman` menyimpan data resi/ekspedisi dan status pengiriman/pengambilan per pesanan, termasuk kodifikasi `kode_kirim`.

```sql
CREATE TABLE `pengiriman` (
  `id_kirim` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` int(11) NOT NULL,
  `kode_kirim` varchar(50) DEFAULT NULL,
  `no_resi` varchar(100) DEFAULT NULL,
  `nama_ekspedisi` varchar(50) DEFAULT NULL,
  `status_kirim` enum('siap_kirim','siap_diambil','dikirim','diterima','diambil') DEFAULT 'siap_kirim',
  `tgl_kirim` datetime DEFAULT NULL,
  `tgl_diterima` datetime DEFAULT NULL,
  PRIMARY KEY (`id_kirim`),
  UNIQUE KEY `id_order` (`id_order`),
  UNIQUE KEY `uq_pengiriman_kode_kirim` (`kode_kirim`),
  CONSTRAINT `pengiriman_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**11. Tabel Notifications**

Tabel `notifications` menyimpan notifikasi in-app per pengguna.

```sql
CREATE TABLE `notifications` (
  `id_notif` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_order` int(11) DEFAULT NULL,
  `judul` varchar(150) DEFAULT NULL,
  `pesan` text DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_notif`),
  KEY `id_user` (`id_user`),
  KEY `id_order` (`id_order`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**12. Tabel Password Resets**

Tabel `password_resets` menyimpan token hash untuk alur lupa sandi pelanggan.

```sql
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(64) NOT NULL COMMENT 'SHA-256 hash dari token plain di URL email',
  `expired_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_password_resets_email` (`email`),
  KEY `idx_password_resets_token` (`token`),
  KEY `idx_password_resets_expired_at` (`expired_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**13. Tabel Activity Logs**

Tabel `activity_logs` menyimpan jejak aktivitas staf internal untuk audit Owner, dengan retensi yang dibatasi secara sistem.

```sql
CREATE TABLE `activity_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(10) unsigned NOT NULL,
  `nama_user` varchar(100) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `aksi` varchar(50) NOT NULL,
  `modul` varchar(50) NOT NULL,
  `keterangan` text NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`),
  KEY `modul` (`modul`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 4.6.4 Implementasi Antarmuka

Implementasi antarmuka memuat informasi dari setiap menu, deskripsi, dan nama file setiap antarmuka yang dapat diakses oleh pengguna pada SIMENAK. Antarmuka dibangun menggunakan HTML, Tailwind CSS CDN, dan Vanilla JavaScript pada arsitektur MVC CodeIgniter 4.

**Tabel 4.X Tabel Implementasi Antarmuka**

| Nama | Deskripsi | Nama File |
|---|---|---|
| Landing Page | Halaman publik utama (katalog ringkas, CTA pesan, modal login/register) | `landing/index.php` |
| Portal Login Internal | Login khusus staf (admin, keuangan, produksi, owner) | `auth/login.php` |
| Buat Password Baru | Form wajib ganti password pada login pertama staf yang dibuat Owner | `auth/buat_password_baru.php` |
| Dashboard Pelanggan | Ringkasan pesanan dan status untuk peran pelanggan | `dashboard/pelanggan.php` |
| Dashboard Admin | Ringkasan operasional admin | `dashboard/admin.php` |
| Dashboard Keuangan | Ringkasan verifikasi pembayaran | `dashboard/keuangan.php` |
| Dashboard Produksi | Ringkasan antrian desain/produksi | `dashboard/produksi.php` |
| Dashboard Owner | Ringkasan bisnis tingkat pemilik | `dashboard/owner.php` |
| Katalog Publik | Daftar produk aktif untuk pelanggan | `katalog/list.php` |
| Kelola Katalog | CRUD katalog produk (admin; owner lihat) | `katalog/index.php` |
| Tambah Produk | Form penambahan produk katalog | `katalog/create.php` |
| Edit / Detail Produk | Form ubah atau lihat detail produk | `katalog/edit.php` |
| Form Template | Pengaturan field dinamis EAV per katalog | `katalog/form_template.php` |
| Form Pemesanan | Form pembuatan pesanan per katalog | `order/create.php` |
| Pesanan Saya | Daftar pesanan milik pelanggan | `order/index.php` |
| Detail Pesanan | Detail status, pembayaran, revisi, dan aksi terkait pesanan | `order/detail.php` |
| List Pemesanan | Daftar seluruh pemesanan untuk admin/owner | `order/list_pemesanan.php` |
| Verifikasi DP | Antrian ACC/tolak bukti DP | `payment/verifikasi_dp.php` |
| Verifikasi Pelunasan | Antrian ACC/tolak bukti pelunasan | `payment/verifikasi_pelunasan.php` |
| Riwayat Pembayaran | Riwayat transaksi pembayaran | `payment/riwayat.php` |
| Nota Tagihan | Halaman cetak nota tagihan pelunasan | `payment/nota_tagihan.php` |
| Bukti Pembayaran Pelunasan | Halaman bukti pelunasan | `payment/bukti_pembayaran_pelunasan.php` |
| Antrian Desain | Antrian kerja desain untuk produksi | `revisi/antrian.php` |
| Workspace Produksi | Workspace unggah draft dan pantau revisi | `revisi/workspace.php` |
| Riwayat Revisi | Riwayat versi draft per pesanan | `revisi/history.php` |
| Unggah Draft | Form unggah draft desain | `revisi/upload_draft.php` |
| Manajemen Pengiriman | Antrian pengiriman/pengambilan (admin kelola; owner lihat) | `pengiriman/index.php` |
| Manajemen Pengguna | Kelola akun pelanggan dan staf (termasuk tetapkan/cabut kerja sama) | `profil/pengguna.php` |
| Form Profil | Form lihat/ubah data profil akun | `partials/profil_form_content.php` |
| Laporan Pemesanan (Admin) | Laporan rekap pesanan + filter/ekspor | `laporan/admin.php` |
| Laporan Transaksi | Laporan pemasukan/transaksi + filter/ekspor | `laporan/keuangan.php` |
| Laporan Desain | Laporan antrian/aktivitas desain + filter/ekspor | `laporan/produksi.php` |
| Ringkasan Bisnis | Laporan ringkasan performa bisnis owner | `laporan/owner.php` |
| Riwayat Aktivitas | Audit log aktivitas staf (owner) | `activity_log/index.php` |
| Notifikasi | Daftar notifikasi in-app | `dashboard/notifikasi.php` |
| Layout Landing | Kerangka halaman publik | `layouts/landing.php` |
| Layout Auth | Kerangka halaman autentikasi portal | `layouts/auth.php` |
| Layout Utama | Kerangka dashboard internal (sidebar + topbar) | `layouts/main.php` |

---

### Catatan teknis untuk penulisan skripsi

1. **Konsistensi nama file:** pada contoh kating digunakan `*.blade.php` (Laravel). Pada SIMENAK tulis `*.php` karena view CodeIgniter 4.
2. **Nomor tabel:** ganti `Tabel 4.X` menjadi nomor berurutan di bab Anda (misalnya Tabel 4.11 untuk antarmuka, Tabel 4.10 untuk daftar tabel basis data jika dipisah).
3. **Screenshot:** setelah subbab teks ini, biasanya dilanjutkan dengan gambar antarmuka per menu (opsional di 4.6.4 atau subbab berikutnya) — beri caption *Gambar 4.x Antarmuka …*.
4. **Kolom kodifikasi & `wajib_ganti_password`:** sudah dimasukkan pada DDL di atas agar selaras dengan implementasi terkini SIMENAK.
