-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 18 Feb 2026 pada 05.23
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_parkir`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_area_parkir`
--

CREATE TABLE `tb_area_parkir` (
  `id_area` int(11) NOT NULL,
  `nama_area` varchar(50) NOT NULL,
  `tipe_kendaraan` enum('motor','mobil','lainnya') NOT NULL,
  `kapasitas` int(5) NOT NULL,
  `terisi` int(5) NOT NULL,
  `status_area_parkir` enum('penuh','tempat kosong masih tersedia','ditutup') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tb_area_parkir`
--

INSERT INTO `tb_area_parkir` (`id_area`, `nama_area`, `tipe_kendaraan`, `kapasitas`, `terisi`, `status_area_parkir`) VALUES
(6, '1MB', 'motor', 20, 1, 'ditutup'),
(7, '2MB', 'motor', 45, 0, 'ditutup'),
(17, '1MA', 'motor', 50, 0, 'tempat kosong masih tersedia'),
(26, '1CA', 'mobil', 17, 0, 'tempat kosong masih tersedia'),
(27, '1OB', 'lainnya', 10, 0, 'tempat kosong masih tersedia');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_kendaraan`
--

CREATE TABLE `tb_kendaraan` (
  `id_kendaraan` int(11) NOT NULL,
  `plat_nomor` varchar(15) NOT NULL,
  `tipe_kendaraan` enum('motor','mobil','lainnya') NOT NULL,
  `jenis_kendaraan` varchar(20) NOT NULL,
  `warna` varchar(20) NOT NULL,
  `pemilik` varchar(100) NOT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tb_kendaraan`
--

INSERT INTO `tb_kendaraan` (`id_kendaraan`, `plat_nomor`, `tipe_kendaraan`, `jenis_kendaraan`, `warna`, `pemilik`, `id_user`) VALUES
(4, 'D 1010 TSK', 'motor', 'Motor Yamaha', 'Magenta', 'ANOMALI', 6),
(6, 'B 6716 VRZ', 'motor', 'Motor Honda', 'Merah', 'SA\'ID', 4),
(9, 'B 6716 ARC', 'lainnya', 'Van', 'Putih', 'UDIN', 2),
(10, 'B 6816 VRZ', 'mobil', 'Mobil Toyota', 'Hitam', 'TEST', 3),
(11, 'T 0WN 3NR', 'motor', 'Motor Yamaha', 'Biru', 'Owner', 9);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_log_aktivitas`
--

CREATE TABLE `tb_log_aktivitas` (
  `id_log` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `aktivitas` varchar(100) NOT NULL,
  `waktu_aktivitas` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tb_log_aktivitas`
--

INSERT INTO `tb_log_aktivitas` (`id_log`, `id_user`, `aktivitas`, `waktu_aktivitas`) VALUES
(71, 3, 'Masuk parkir - B 6816 VRZ di area 1MB', '2026-02-18 07:19:26'),
(72, NULL, 'Masuk parkir - F UCK di area 1MB', '2026-02-18 07:31:10'),
(73, NULL, 'Masuk parkir - F UCK di area 1MB', '2026-02-18 07:38:19'),
(74, 9, 'Masuk parkir - T 0WN 3NR di area 1MB', '2026-02-18 09:34:55'),
(75, 6, 'Masuk parkir - D 1010 TSK di area 1MB', '2026-02-18 10:32:21'),
(76, 4, 'Masuk parkir - B 6716 VRZ di area 1MB', '2026-02-18 10:39:28'),
(77, 9, 'Keluar parkir - T 0WN 3NR dari area 1MB', '2026-02-18 10:40:08'),
(78, 9, 'Masuk parkir - T 0WN 3NR di area 1MB', '2026-02-18 10:43:50'),
(79, NULL, 'Masuk parkir - F UCK di area 1MB', '2026-02-18 10:44:34'),
(80, NULL, 'Masuk parkir - F UCK di area 1MB', '2026-02-18 10:58:49'),
(81, NULL, 'Keluar parkir - F UCK dari area 1MB', '2026-02-18 10:58:52'),
(82, 3, 'Masuk parkir - B 6816 VRZ di area 1MB', '2026-02-18 10:58:57'),
(83, NULL, 'Masuk parkir - K di area 1MB', '2026-02-18 11:00:05'),
(84, NULL, 'Masuk parkir - K di area 1MB', '2026-02-18 11:00:18'),
(85, NULL, 'Keluar parkir - K dari area 1MB', '2026-02-18 11:00:31'),
(86, NULL, 'Keluar parkir - K dari area 1MB', '2026-02-18 11:00:46'),
(87, NULL, 'Keluar parkir - F UCK dari area 1MB', '2026-02-18 11:19:26'),
(88, NULL, 'Masuk parkir - F UCK di area 1MB', '2026-02-18 11:19:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_tampilan`
--

CREATE TABLE `tb_tampilan` (
  `id` int(11) NOT NULL,
  `tema` enum('normal','dark','retro','neon','kopi susu','cotton-gum') NOT NULL DEFAULT 'normal',
  `running_text_dashboard` varchar(255) DEFAULT 'Selamat datang di Dashboard',
  `running_text_transaksi` varchar(255) DEFAULT 'Selamat datang di Transaksi Parkir',
  `kecepatan_running_text_dashboard` int(11) DEFAULT 15,
  `kecepatan_running_text_transaksi` int(11) DEFAULT 15
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tb_tampilan`
--

INSERT INTO `tb_tampilan` (`id`, `tema`, `running_text_dashboard`, `running_text_transaksi`, `kecepatan_running_text_dashboard`, `kecepatan_running_text_transaksi`) VALUES
(1, 'normal', 'Selamat datang di Dashboard!', 'Selamat datang di Transaksi Parkir!', 15, 15);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_tarif`
--

CREATE TABLE `tb_tarif` (
  `id_tarif` int(11) NOT NULL,
  `jenis_kendaraan` enum('motor','mobil','lainnya') NOT NULL,
  `tarif_per_jam` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tb_tarif`
--

INSERT INTO `tb_tarif` (`id_tarif`, `jenis_kendaraan`, `tarif_per_jam`) VALUES
(1, 'motor', 2000),
(2, 'mobil', 5000),
(3, 'lainnya', 6000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_transaksi`
--

CREATE TABLE `tb_transaksi` (
  `id_parkir` int(11) NOT NULL,
  `id_kendaraan` int(11) DEFAULT NULL,
  `plat_nomor` varchar(20) DEFAULT NULL,
  `plat_nomor_tamu` varchar(15) DEFAULT NULL,
  `waktu_masuk` datetime NOT NULL,
  `waktu_keluar` datetime DEFAULT NULL,
  `id_tarif` int(11) DEFAULT NULL,
  `durasi_jam` int(5) NOT NULL,
  `biaya_total` decimal(10,0) NOT NULL,
  `status` enum('masuk','keluar') NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_area` int(11) DEFAULT NULL,
  `jenis_transaksi` enum('user','tamu') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tb_transaksi`
--

INSERT INTO `tb_transaksi` (`id_parkir`, `id_kendaraan`, `plat_nomor`, `plat_nomor_tamu`, `waktu_masuk`, `waktu_keluar`, `id_tarif`, `durasi_jam`, `biaya_total`, `status`, `id_user`, `id_area`, `jenis_transaksi`) VALUES
(29, 4, NULL, NULL, '2026-02-03 14:02:14', '2026-02-03 14:02:40', 1, 1, 2000, 'keluar', 6, 17, 'user'),
(30, 9, NULL, NULL, '2026-02-03 14:08:19', '2026-02-05 13:31:54', 3, 48, 35000, 'keluar', 2, 27, 'user'),
(31, 10, NULL, NULL, '2026-02-03 14:29:26', '2026-02-03 14:29:54', 2, 1, 5000, 'keluar', 3, 26, 'user'),
(32, 10, NULL, NULL, '2026-02-03 14:36:57', '2026-02-03 14:37:32', 2, 1, 5000, 'keluar', 3, 26, 'user'),
(33, 10, NULL, NULL, '2026-02-03 14:41:17', '2026-02-03 14:54:54', 2, 1, 5000, 'keluar', 3, 26, 'user'),
(34, 4, NULL, NULL, '2026-02-04 07:47:53', '2026-02-04 07:50:03', 1, 1, 2000, 'keluar', 6, 17, 'user'),
(35, 4, NULL, NULL, '2026-02-04 07:50:19', '2026-02-04 07:59:35', 1, 1, 2000, 'keluar', 6, 17, 'user'),
(36, 4, NULL, NULL, '2026-02-04 07:59:52', '2026-02-04 08:24:55', 1, 1, 2000, 'keluar', 6, 17, 'user'),
(37, 4, NULL, NULL, '2026-02-04 08:25:53', '2026-02-04 08:26:44', 1, 1, 2000, 'keluar', 6, 17, 'user'),
(38, 4, NULL, NULL, '2026-02-04 08:27:13', '2026-02-04 08:28:45', 1, 1, 2000, 'keluar', 6, 17, 'user'),
(39, 4, NULL, NULL, '2026-02-06 06:50:52', '2026-02-06 06:51:06', 1, 1, 2000, 'keluar', 6, 17, 'user'),
(40, 9, NULL, NULL, '2026-02-10 08:53:07', '2026-02-10 10:41:06', 3, 2, 11000, 'keluar', 2, 27, 'user'),
(41, 9, NULL, NULL, '2026-02-10 11:03:12', '2026-02-10 11:04:36', 3, 1, 6000, 'keluar', 2, 27, 'user'),
(42, 9, NULL, NULL, '2026-02-10 11:06:18', '2026-02-16 15:19:36', 3, 149, 894000, 'keluar', 2, 27, 'user'),
(43, 4, NULL, NULL, '2026-02-10 11:13:51', '2026-02-10 11:23:13', 1, 1, 2000, 'keluar', 6, 17, 'user'),
(44, 4, NULL, NULL, '2026-02-10 11:28:35', '2026-02-10 11:28:51', 1, 1, 2000, 'keluar', 6, 17, 'user'),
(45, 4, NULL, NULL, '2026-02-10 11:30:20', '2026-02-10 11:41:22', 1, 1, 2000, 'keluar', 6, 17, 'user'),
(46, 4, NULL, NULL, '2026-02-10 11:41:32', '2026-02-10 14:54:53', 1, 4, 8000, 'keluar', 6, 17, 'user'),
(47, 4, NULL, NULL, '2026-02-10 15:04:33', '2026-02-10 15:05:15', 1, 1, 2000, 'keluar', 6, 17, 'user'),
(48, 4, NULL, NULL, '2026-02-10 15:13:43', '2026-02-10 15:13:54', 1, 1, 2000, 'keluar', 6, 17, 'user'),
(49, 4, NULL, NULL, '2026-02-11 06:45:28', '2026-02-16 15:35:47', 1, 129, 258000, 'keluar', 6, 17, 'user'),
(51, 6, NULL, NULL, '2026-02-16 13:43:15', '2026-02-16 15:20:23', 1, 2, 4000, 'keluar', 4, 6, 'user'),
(52, NULL, 'K', NULL, '2026-02-16 14:36:44', '2026-02-16 15:35:38', 1, 1, 2000, 'keluar', NULL, 6, 'user'),
(53, 4, 'D 1010 TSK', NULL, '2026-02-16 15:35:59', '2026-02-16 15:50:43', 1, 1, 2000, 'keluar', 6, 6, 'user'),
(54, NULL, 'T', NULL, '2026-02-16 15:36:24', '2026-02-18 06:17:15', 1, 39, 78000, 'keluar', NULL, 6, 'user'),
(55, NULL, 'K', NULL, '2026-02-16 15:39:44', '2026-02-18 06:19:20', 1, 39, 78000, 'keluar', NULL, 6, 'user'),
(56, NULL, 'F', NULL, '2026-02-17 12:54:47', '2026-02-18 06:19:23', 1, 18, 36000, 'keluar', NULL, 6, 'user'),
(57, 9, 'B 6716 ARC', NULL, '2026-02-17 17:08:42', '2026-02-18 06:19:26', 3, 14, 84000, 'keluar', 2, 6, 'user'),
(58, 11, 'T 0WN 3NR', NULL, '2026-02-17 17:24:12', '2026-02-17 17:29:50', 1, 1, 2000, 'keluar', 9, 6, 'user'),
(59, 10, 'B 6816 VRZ', NULL, '2026-02-18 06:20:54', '2026-02-18 07:12:06', 2, 1, 5000, 'keluar', 3, 6, 'user'),
(60, NULL, 'F', NULL, '2026-02-18 06:21:03', '2026-02-18 07:11:43', 1, 1, 2000, 'keluar', NULL, 6, 'user'),
(61, NULL, 'F UCK', NULL, '2026-02-18 06:51:18', '2026-02-18 06:57:35', 1, 1, 2000, 'keluar', NULL, 6, 'user'),
(62, NULL, 'F UCK', NULL, '2026-02-18 06:53:56', '2026-02-18 07:12:09', 1, 1, 2000, 'keluar', NULL, 6, 'user'),
(63, NULL, 'F UCK', NULL, '2026-02-18 06:54:14', '2026-02-18 06:57:52', 1, 1, 2000, 'keluar', NULL, 6, 'user'),
(64, NULL, 'F UCK', NULL, '2026-02-18 06:56:26', '2026-02-18 06:57:48', 1, 1, 2000, 'keluar', NULL, 6, 'user'),
(65, NULL, 'F UCK', NULL, '2026-02-18 06:57:25', '2026-02-18 06:57:42', 1, 1, 2000, 'keluar', NULL, 6, 'user'),
(66, 11, 'T 0WN 3NR', NULL, '2026-02-18 07:12:19', '2026-02-18 07:18:55', 1, 1, 2000, 'keluar', 9, 6, 'user'),
(67, NULL, 'F UCK', NULL, '2026-02-18 07:13:00', '2026-02-18 07:18:52', 1, 1, 2000, 'keluar', NULL, 6, 'user'),
(68, NULL, 'F UCK', NULL, '2026-02-18 07:17:59', '2026-02-18 07:18:34', 1, 1, 2000, 'keluar', NULL, 6, 'user'),
(69, 10, 'B 6816 VRZ', NULL, '2026-02-18 07:19:26', '2026-02-18 10:33:03', 2, 4, 20000, 'keluar', 3, 6, 'user'),
(70, NULL, 'F UCK', NULL, '2026-02-18 07:31:10', '2026-02-18 09:10:02', 1, 2, 4000, 'keluar', NULL, 6, 'user'),
(71, NULL, 'F UCK', NULL, '2026-02-18 07:38:19', '2026-02-18 07:38:26', 1, 1, 2000, 'keluar', NULL, 6, 'user'),
(72, 11, 'T 0WN 3NR', NULL, '2026-02-18 09:34:55', '2026-02-18 10:40:08', 1, 2, 4000, 'keluar', 9, 6, 'user'),
(73, 4, 'D 1010 TSK', NULL, '2026-02-18 10:32:21', NULL, NULL, 0, 0, 'masuk', 6, 6, 'user'),
(74, 6, 'B 6716 VRZ', NULL, '2026-02-18 10:39:28', NULL, NULL, 0, 0, 'masuk', 4, 6, 'user'),
(75, 11, 'T 0WN 3NR', NULL, '2026-02-18 10:43:50', NULL, NULL, 0, 0, 'masuk', 9, 6, 'user'),
(76, NULL, 'F UCK', NULL, '2026-02-18 10:44:34', '2026-02-18 11:19:26', 1, 1, 2000, 'keluar', NULL, 6, 'user'),
(77, NULL, 'F UCK', NULL, '2026-02-18 10:58:49', '2026-02-18 10:58:52', 1, 1, 2000, 'keluar', NULL, 6, 'user'),
(78, 10, 'B 6816 VRZ', NULL, '2026-02-18 10:58:57', NULL, NULL, 0, 0, 'masuk', 3, 6, 'user'),
(79, NULL, 'K', NULL, '2026-02-18 11:00:05', '2026-02-18 11:00:46', 1, 1, 2000, 'keluar', NULL, 6, 'user'),
(80, NULL, 'K', NULL, '2026-02-18 11:00:18', '2026-02-18 11:00:31', 1, 1, 2000, 'keluar', NULL, 6, 'user'),
(81, NULL, 'F UCK', NULL, '2026-02-18 11:19:49', NULL, NULL, 0, 0, 'masuk', NULL, 6, 'user');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tb_user`
--

CREATE TABLE `tb_user` (
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('admin','petugas','owner') NOT NULL,
  `status_aktif` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tb_user`
--

INSERT INTO `tb_user` (`id_user`, `nama_lengkap`, `username`, `password`, `role`, `status_aktif`) VALUES
(2, 'UDIN', 'saha_maneh', '$2y$10$TdQznjjxRPRZwos3KZ2CfOmoUCYV53i2N6QbAFaahGO9wB2TXeheK', 'owner', 1),
(3, 'TEST', 'test123', '$2y$10$jBVjqTr1c9CVnTGk/976TOX6MbTitoUtJcO.GfoJFKnkYRhKUyAu2', 'admin', 1),
(4, 'SA\'ID', 'bisa_diandalkan', '$2y$10$x1fWuDXdGAE8DBakJUshZOJvx7NIIOA69o93OAOPxCAUmpI5et/oO', 'petugas', 1),
(6, 'ANOMALI', 'decade010', '$2y$10$BVi0ajvJu1hCVA.1.cpbLulmOg.GoxMIcFIK83hIKprGrQqgo24eq', 'admin', 1),
(7, 'Admin', 'admin', '$2y$10$EhXB2d5SLd4QP4dvFVLfL.V3jCbYPEcwsYutSxW9S7z4QVUQ6Nwkq', 'admin', 1),
(8, 'Petugas', 'petugas#1', '$2y$10$sg4W5w4R7gnp.reCzYIMm.xb0zpqgixOSQIb2eaMN6CW1LFl.u9cq', 'petugas', 1),
(9, 'Owner', 'pemilik#1', '$2y$10$wI4HSLqYInpNsHGXmAK2c.gSiJVBnCCKsZ6Jg97DPTxnf31SRNysK', 'owner', 1);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `tb_area_parkir`
--
ALTER TABLE `tb_area_parkir`
  ADD PRIMARY KEY (`id_area`);

--
-- Indeks untuk tabel `tb_kendaraan`
--
ALTER TABLE `tb_kendaraan`
  ADD PRIMARY KEY (`id_kendaraan`),
  ADD UNIQUE KEY `unique_user_kendaraan` (`id_user`);

--
-- Indeks untuk tabel `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `fk_log_aktivitas_user` (`id_user`);

--
-- Indeks untuk tabel `tb_tampilan`
--
ALTER TABLE `tb_tampilan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tb_tarif`
--
ALTER TABLE `tb_tarif`
  ADD PRIMARY KEY (`id_tarif`);

--
-- Indeks untuk tabel `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD PRIMARY KEY (`id_parkir`),
  ADD KEY `fk_transaksi_kendaraan` (`id_kendaraan`),
  ADD KEY `fk_transaksi_tarif` (`id_tarif`),
  ADD KEY `fk_transaksi_user` (`id_user`),
  ADD KEY `fk_transaksi_area` (`id_area`);

--
-- Indeks untuk tabel `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tb_area_parkir`
--
ALTER TABLE `tb_area_parkir`
  MODIFY `id_area` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `tb_kendaraan`
--
ALTER TABLE `tb_kendaraan`
  MODIFY `id_kendaraan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT untuk tabel `tb_tampilan`
--
ALTER TABLE `tb_tampilan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  MODIFY `id_parkir` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT untuk tabel `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tb_kendaraan`
--
ALTER TABLE `tb_kendaraan`
  ADD CONSTRAINT `fk_kendaraan_id_user` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  ADD CONSTRAINT `fk_log_aktivitas_user` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD CONSTRAINT `fk_transaksi_area` FOREIGN KEY (`id_area`) REFERENCES `tb_area_parkir` (`id_area`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi_kendaraan` FOREIGN KEY (`id_kendaraan`) REFERENCES `tb_kendaraan` (`id_kendaraan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi_tarif` FOREIGN KEY (`id_tarif`) REFERENCES `tb_tarif` (`id_tarif`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
