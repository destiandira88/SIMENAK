-- SIMENAK ERD for drawDB (https://www.drawdb.app/)
-- Import: File > Import from SQL > pilih MySQL / MariaDB
-- Hanya struktur tabel (DDL), tanpa data INSERT

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('pelanggan','admin','keuangan','produksi','owner') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat` varchar(150) DEFAULT NULL,
  `jenis` enum('perseorangan','perusahaan') DEFAULT 'perseorangan',
  `nama_perusahaan` varchar(150) DEFAULT NULL,
  `is_verified` tinyint(4) DEFAULT 0,
  `is_suspended` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_pelanggan`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `pelanggan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `katalog` (
  `id_katalog` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id_katalog`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  KEY `id_katalog` (`id_katalog`),
  CONSTRAINT `form_templates_ibfk_1` FOREIGN KEY (`id_katalog`) REFERENCES `katalog` (`id_katalog`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `status` enum('menunggu_konfirmasi_harga','menunggu_konfirmasi_pelanggan','menunggu_verifikasi_dp','terverifikasi','proses_desain','proses_revisi','proses_cetak','finishing','siap_kirim','siap_diambil','dikirim','pesanan_diterima','menunggu_verifikasi_lunas','pelunasan_terverifikasi','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu_verifikasi_dp',
  `created_at` datetime DEFAULT current_timestamp(),
  `batas_upload_dp` datetime DEFAULT NULL,
  `reminder_dp_sent` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_order`),
  UNIQUE KEY `kode_order` (`kode_order`),
  KEY `id_pelanggan` (`id_pelanggan`),
  KEY `id_katalog` (`id_katalog`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`id_katalog`) REFERENCES `katalog` (`id_katalog`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_attributes` (
  `id_attr` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` int(11) NOT NULL,
  `attribute_key` varchar(50) NOT NULL,
  `attribute_val` text DEFAULT NULL,
  PRIMARY KEY (`id_attr`),
  UNIQUE KEY `uq_order_attr` (`id_order`,`attribute_key`),
  CONSTRAINT `order_attributes_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `revisi_desain` (
  `id_revisi` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` int(11) NOT NULL,
  `id_produksi` int(11) NOT NULL,
  `versi` int(11) NOT NULL DEFAULT 1,
  `file_draft` varchar(255) NOT NULL,
  `catatan_prod` text DEFAULT NULL,
  `catatan_revisi` text DEFAULT NULL,
  `status` enum('uploaded','diajukan_revisi','acc','ditolak') DEFAULT 'uploaded',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_revisi`),
  KEY `id_order` (`id_order`),
  KEY `id_produksi` (`id_produksi`),
  CONSTRAINT `revisi_desain_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`),
  CONSTRAINT `revisi_desain_ibfk_2` FOREIGN KEY (`id_produksi`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pengiriman` (
  `id_kirim` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` int(11) NOT NULL,
  `no_resi` varchar(100) DEFAULT NULL,
  `nama_ekspedisi` varchar(50) DEFAULT NULL,
  `status_kirim` enum('siap_kirim','siap_diambil','dikirim','diterima','diambil') DEFAULT 'siap_kirim',
  `tgl_kirim` datetime DEFAULT NULL,
  `tgl_diterima` datetime DEFAULT NULL,
  PRIMARY KEY (`id_kirim`),
  UNIQUE KEY `id_order` (`id_order`),
  CONSTRAINT `pengiriman_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
