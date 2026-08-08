-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 01, 2026 at 07:01 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simenak_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `form_templates`
--

CREATE TABLE `form_templates` (
  `id_template` int(11) NOT NULL,
  `id_katalog` int(11) NOT NULL,
  `field_key` varchar(50) NOT NULL,
  `field_label` varchar(100) NOT NULL,
  `field_type` enum('text','date','time','textarea','file') NOT NULL,
  `placeholder` varchar(150) DEFAULT NULL,
  `is_required` tinyint(4) DEFAULT 1,
  `urutan` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `form_templates`
--

INSERT INTO `form_templates` (`id_template`, `id_katalog`, `field_key`, `field_label`, `field_type`, `placeholder`, `is_required`, `urutan`) VALUES
(1, 1, 'nama_mempelai_pria', 'Nama Mempelai Pria', 'text', 'Contoh: Ahmad Fauzi', 1, 1),
(2, 1, 'nama_mempelai_wanita', 'Nama Mempelai Wanita', 'text', 'Contoh: Siti Rahmah', 1, 2),
(3, 1, 'nama_keluarga_pria', 'Nama Keluarga Mempelai Pria', 'text', 'Contoh: Bpk. H. Suparman', 1, 3),
(4, 1, 'nama_keluarga_wanita', 'Nama Keluarga Mempelai Wanita', 'text', 'Contoh: Bpk. H. Sumarno', 1, 4),
(5, 1, 'akad_hari', 'Hari Akad Nikah', 'text', 'Contoh: Sabtu', 1, 5),
(6, 1, 'akad_tanggal', 'Tanggal Akad Nikah', 'date', '', 1, 6),
(7, 1, 'akad_waktu', 'Waktu Akad Nikah', 'time', '', 1, 7),
(8, 1, 'akad_tempat', 'Tempat Akad Nikah', 'textarea', 'Nama gedung dan alamat lengkap', 1, 8),
(9, 1, 'resepsi_hari', 'Hari Resepsi', 'text', 'Contoh: Sabtu', 1, 9),
(10, 1, 'resepsi_tanggal', 'Tanggal Resepsi', 'date', '', 1, 10),
(11, 1, 'resepsi_waktu', 'Waktu Resepsi', 'time', '', 1, 11),
(12, 1, 'resepsi_tempat', 'Tempat Resepsi', 'textarea', 'Nama gedung dan alamat lengkap', 1, 12),
(13, 1, 'turut_mengundang', 'Turut Mengundang', 'textarea', 'Nama-nama yang turut mengundang', 0, 13),
(14, 1, 'hiburan', 'Hiburan (jika ada)', 'text', 'Contoh: Organ Tunggal', 0, 14),
(15, 1, 'lampiran_peta', 'Lampiran Peta Lokasi', 'file', '', 0, 15),
(16, 1, 'keterangan', 'Keterangan Tambahan', 'textarea', 'Informasi tambahan lainnya', 0, 16),
(17, 2, 'nama_anak', 'Nama Anak yang Dikhitan', 'text', 'Contoh: Muhammad Rafif', 1, 1),
(18, 2, 'nama_bapak', 'Nama Bapak', 'text', 'Contoh: H. Budi Santoso', 1, 2),
(19, 2, 'nama_ibu', 'Nama Ibu', 'text', 'Contoh: Hj. Sri Wahyuni', 1, 3),
(20, 2, 'resepsi_hari', 'Hari Resepsi', 'text', 'Contoh: Minggu', 1, 4),
(21, 2, 'resepsi_tanggal', 'Tanggal Resepsi', 'date', '', 1, 5),
(22, 2, 'resepsi_waktu', 'Waktu Resepsi', 'time', '', 1, 6),
(23, 2, 'resepsi_tempat', 'Tempat Resepsi', 'textarea', 'Nama gedung dan alamat lengkap', 1, 7),
(24, 2, 'turut_mengundang', 'Turut Mengundang', 'textarea', 'Nama-nama yang turut mengundang', 0, 8),
(25, 2, 'hiburan', 'Hiburan (jika ada)', 'text', 'Contoh: Organ Tunggal', 0, 9),
(26, 2, 'lampiran_peta', 'Lampiran Peta Lokasi', 'file', '', 0, 10),
(27, 2, 'keterangan', 'Keterangan Tambahan', 'textarea', 'Informasi tambahan lainnya', 0, 11);

-- --------------------------------------------------------

--
-- Table structure for table `katalog`
--

CREATE TABLE `katalog` (
  `id_katalog` int(11) NOT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `kategori` enum('desain_grafis','cetak_digital','cetak_offset','media_promosi') NOT NULL,
  `harga_dasar` decimal(12,2) NOT NULL,
  `kuota_revisi_default` int(11) NOT NULL DEFAULT 2,
  `min_order` int(11) NOT NULL DEFAULT 1,
  `satuan` varchar(30) NOT NULL DEFAULT 'pcs',
  `estimasi_hari` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `katalog`
--

INSERT INTO `katalog` (`id_katalog`, `nama_produk`, `kategori`, `harga_dasar`, `kuota_revisi_default`, `min_order`, `satuan`, `estimasi_hari`, `deskripsi`, `gambar`, `is_active`) VALUES
(1, 'Undangan Pernikahan Softcover', 'cetak_digital', 150000.00, 3, 100, 'pcs', '5 hari kerja', 'Undangan pernikahan softcover full color', NULL, 1),
(2, 'Undangan Khitanan', 'cetak_digital', 100000.00, 2, 50, 'pcs', '4 hari kerja', 'Undangan khitanan full color', NULL, 1),
(3, 'Spanduk / Banner', 'media_promosi', 50000.00, 2, 1, 'pcs', '2 hari kerja', 'Cetak spanduk dan banner berbagai ukuran', NULL, 1),
(4, 'Brosur A5', 'cetak_digital', 75000.00, 2, 100, 'lembar', '3 hari kerja', 'Brosur A5 full color bolak-balik', NULL, 1),
(5, 'Kartu Nama', 'cetak_digital', 30000.00, 2, 100, 'pcs', '3 hari kerja', 'Kartu nama full color 2 sisi', NULL, 1),
(6, 'Kalender Dinding', 'cetak_offset', 200000.00, 2, 1, 'pcs', '10 hari kerja', 'Kalender dinding 13 lembar full color', NULL, 1),
(7, 'Nota / Kwitansi', 'cetak_offset', 80000.00, 1, 1, 'buku', '6 hari kerja', 'Nota kwitansi 2 rangkap NCR', NULL, 1),
(8, 'Desain Logo', 'desain_grafis', 300000.00, 4, 1, 'desain', '2 hari kerja', 'Jasa desain logo profesional', '1780282595_logo.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id_notif` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_order` int(11) DEFAULT NULL,
  `judul` varchar(150) DEFAULT NULL,
  `pesan` text DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(64) NOT NULL COMMENT 'SHA-256 hash dari token plain di URL email',
  `expired_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id_order` int(11) NOT NULL,
  `kode_order` varchar(25) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `id_katalog` int(11) NOT NULL,
  `jenis_pelanggan` enum('perseorangan','perusahaan') NOT NULL,
  `is_custom` tinyint(4) DEFAULT 0,
  `jumlah_order` int(11) NOT NULL DEFAULT 1,
  `catatan_custom` text DEFAULT NULL,
  `harga_custom` decimal(12,2) DEFAULT NULL,
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
  `status` enum('menunggu_konfirmasi_harga','menunggu_konfirmasi_pelanggan','menunggu_verifikasi_dp','terverifikasi','proses_desain','proses_revisi','proses_cetak','finishing','siap_kirim','siap_diambil','dikirim','pesanan_diterima','menunggu_verifikasi_lunas','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu_verifikasi_dp',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_attributes`
--

CREATE TABLE `order_attributes` (
  `id_attr` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `attribute_key` varchar(50) NOT NULL,
  `attribute_val` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id_payment` int(11) NOT NULL,
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
  `reminder_verif_6j_sent` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat` varchar(150) DEFAULT NULL,
  `jenis` enum('perseorangan','perusahaan') DEFAULT 'perseorangan',
  `nama_perusahaan` varchar(150) DEFAULT NULL,
  `is_verified` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `id_user`, `no_telp`, `alamat`, `jenis`, `nama_perusahaan`, `is_verified`) VALUES
(1, 5, '081234567890', 'Jl. Test No. 1, Bandung Barat', 'perseorangan', NULL, 0),
(3, 17, '089995643662', NULL, 'perseorangan', NULL, 0),
(4, 18, '087779023551', NULL, 'perseorangan', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pengiriman`
--

CREATE TABLE `pengiriman` (
  `id_kirim` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `no_resi` varchar(100) DEFAULT NULL,
  `nama_ekspedisi` varchar(50) DEFAULT NULL,
  `status_kirim` enum('siap_kirim','siap_diambil','dikirim','diterima') DEFAULT 'siap_kirim',
  `tgl_kirim` datetime DEFAULT NULL,
  `tgl_diterima` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `revisi_desain`
--

CREATE TABLE `revisi_desain` (
  `id_revisi` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `id_produksi` int(11) NOT NULL,
  `versi` int(11) NOT NULL DEFAULT 1,
  `file_draft` varchar(255) NOT NULL,
  `catatan_prod` text DEFAULT NULL,
  `catatan_revisi` text DEFAULT NULL,
  `status` enum('uploaded','diajukan_revisi','acc','ditolak') DEFAULT 'uploaded',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('pelanggan','admin','keuangan','produksi','owner') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Owner Z\'Plack', 'owner@zplack.com', '$2y$10$ohmthfLx8fKiaIyInYx44uFkMYH.9QG7bS9.9laj4VScLqTxXZbUa', 'owner', '2026-05-28 19:36:55'),
(2, 'Admin Z\'Plack', 'admin@zplack.com', '$2y$10$ohmthfLx8fKiaIyInYx44uFkMYH.9QG7bS9.9laj4VScLqTxXZbUa', 'admin', '2026-05-28 19:36:55'),
(3, 'Bagian Keuangan', 'keuangan@zplack.com', '$2y$10$ohmthfLx8fKiaIyInYx44uFkMYH.9QG7bS9.9laj4VScLqTxXZbUa', 'keuangan', '2026-05-28 19:36:55'),
(4, 'Bagian Produksi', 'produksi@zplack.com', '$2y$10$ohmthfLx8fKiaIyInYx44uFkMYH.9QG7bS9.9laj4VScLqTxXZbUa', 'produksi', '2026-05-28 19:36:55'),
(5, 'Pelanggan Test', 'pelanggan@test.com', '$2y$10$ohmthfLx8fKiaIyInYx44uFkMYH.9QG7bS9.9laj4VScLqTxXZbUa', 'pelanggan', '2026-05-28 19:36:55'),
(17, 'OldMoneySuccess', 'oldmoneysuccesswoman@gmail.com', '$2y$10$cWsLp9SOW/HFiQF8QH7nBO6XpML/c3LxcotImz6VYnI/hhvWyswSO', 'pelanggan', '2026-05-29 12:56:52'),
(18, 'John Doe', 'john@gmail.com', '$2y$10$vUliejWmu233vtx7cbVCR.lXmRSaTJ2iQ1Ly6vUWhR8uo1/3srkBi', 'pelanggan', '2026-05-30 14:21:26');

-- --------------------------------------------------------

--
-- Table structure for table `verifikasi_perusahaan`
--

CREATE TABLE `verifikasi_perusahaan` (
  `id_verify` int(11) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `nama_perusahaan` varchar(150) NOT NULL,
  `no_npwp` varchar(30) DEFAULT NULL,
  `dokumen_npwp` varchar(255) DEFAULT NULL,
  `dokumen_ktp_pic` varchar(255) DEFAULT NULL,
  `status` enum('verified','rejected') NOT NULL DEFAULT 'verified',
  `catatan_admin` text DEFAULT NULL,
  `tgl_pengajuan` datetime DEFAULT current_timestamp(),
  `tgl_verifikasi` datetime DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `form_templates`
--
ALTER TABLE `form_templates`
  ADD PRIMARY KEY (`id_template`),
  ADD KEY `id_katalog` (`id_katalog`);

--
-- Indexes for table `katalog`
--
ALTER TABLE `katalog`
  ADD PRIMARY KEY (`id_katalog`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_password_resets_email` (`email`),
  ADD KEY `idx_password_resets_token` (`token`),
  ADD KEY `idx_password_resets_expired_at` (`expired_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id_notif`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_order` (`id_order`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD UNIQUE KEY `kode_order` (`kode_order`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_katalog` (`id_katalog`);

--
-- Indexes for table `order_attributes`
--
ALTER TABLE `order_attributes`
  ADD PRIMARY KEY (`id_attr`),
  ADD UNIQUE KEY `uq_order_attr` (`id_order`,`attribute_key`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id_payment`),
  ADD UNIQUE KEY `kode_payment` (`kode_payment`),
  ADD KEY `id_order` (`id_order`),
  ADD KEY `id_verifikator` (`id_verifikator`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD PRIMARY KEY (`id_kirim`),
  ADD UNIQUE KEY `id_order` (`id_order`);

--
-- Indexes for table `revisi_desain`
--
ALTER TABLE `revisi_desain`
  ADD PRIMARY KEY (`id_revisi`),
  ADD KEY `id_order` (`id_order`),
  ADD KEY `id_produksi` (`id_produksi`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `verifikasi_perusahaan`
--
ALTER TABLE `verifikasi_perusahaan`
  ADD PRIMARY KEY (`id_verify`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_admin` (`id_admin`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `form_templates`
--
ALTER TABLE `form_templates`
  MODIFY `id_template` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `katalog`
--
ALTER TABLE `katalog`
  MODIFY `id_katalog` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id_notif` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_attributes`
--
ALTER TABLE `order_attributes`
  MODIFY `id_attr` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id_payment` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pengiriman`
--
ALTER TABLE `pengiriman`
  MODIFY `id_kirim` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `revisi_desain`
--
ALTER TABLE `revisi_desain`
  MODIFY `id_revisi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `verifikasi_perusahaan`
--
ALTER TABLE `verifikasi_perusahaan`
  MODIFY `id_verify` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `form_templates`
--
ALTER TABLE `form_templates`
  ADD CONSTRAINT `form_templates_ibfk_1` FOREIGN KEY (`id_katalog`) REFERENCES `katalog` (`id_katalog`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`id_katalog`) REFERENCES `katalog` (`id_katalog`);

--
-- Constraints for table `order_attributes`
--
ALTER TABLE `order_attributes`
  ADD CONSTRAINT `order_attributes_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`id_verifikator`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD CONSTRAINT `pelanggan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD CONSTRAINT `pengiriman_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`);

--
-- Constraints for table `revisi_desain`
--
ALTER TABLE `revisi_desain`
  ADD CONSTRAINT `revisi_desain_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`),
  ADD CONSTRAINT `revisi_desain_ibfk_2` FOREIGN KEY (`id_produksi`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `verifikasi_perusahaan`
--
ALTER TABLE `verifikasi_perusahaan`
  ADD CONSTRAINT `verifikasi_perusahaan_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`),
  ADD CONSTRAINT `verifikasi_perusahaan_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `users` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
