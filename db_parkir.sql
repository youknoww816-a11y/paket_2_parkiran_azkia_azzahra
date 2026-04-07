-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 02 Apr 2026 pada 02.40
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
(17, '1MA', 'motor', 50, 4, 'tempat kosong masih tersedia'),
(26, '1CA', 'mobil', 17, 0, 'tempat kosong masih tersedia'),
(27, '1OB', 'lainnya', 10, 2, 'tempat kosong masih tersedia');

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
(6, 'B 6716 VRZ', 'motor', 'Motor Honda', 'Merah', 'SA\'ID', 4),
(9, 'B 6716 ARC', 'lainnya', 'Van', 'Putih', 'UDIN', 2),
(10, 'B 6816 VRZ', 'mobil', 'Mobil Toyota', 'Hitam', 'TEST', 3),
(11, 'T 0WN 3NR', 'motor', 'Motor Yamaha', 'Biru', 'Owner', 9),
(12, 'F TSI 0UT', 'motor', 'Motor Honda', 'Magenta', 'Admin?', 12),
(13, 'A ZKL 7BF', 'mobil', 'Mobil Toyota', 'Biru', 'Petugas', 8),
(14, 'N AHU M4N', 'lainnya', '85 70 79', 'Chrome', 'Manusia', 13);

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
(74, 9, 'Masuk parkir - T 0WN 3NR di area 1MB', '2026-02-18 09:34:55'),
(76, 4, 'Masuk parkir - B 6716 VRZ di area 1MB', '2026-02-18 10:39:28'),
(77, 9, 'Keluar parkir - T 0WN 3NR dari area 1MB', '2026-02-18 10:40:08'),
(78, 9, 'Masuk parkir - T 0WN 3NR di area 1MB', '2026-02-18 10:43:50'),
(82, 3, 'Masuk parkir - B 6816 VRZ di area 1MB', '2026-02-18 10:58:57'),
(83, NULL, 'Masuk parkir - K di area 1MB', '2026-02-18 11:00:05'),
(84, NULL, 'Masuk parkir - K di area 1MB', '2026-02-18 11:00:18'),
(85, NULL, 'Keluar parkir - K dari area 1MB', '2026-02-18 11:00:31'),
(86, NULL, 'Keluar parkir - K dari area 1MB', '2026-02-18 11:00:46'),
(89, 2, 'Masuk parkir - B 6716 ARC di area 1MB', '2026-02-23 13:44:06'),
(90, 9, 'Keluar parkir - T 0WN 3NR dari area 1MB', '2026-02-23 13:44:10'),
(91, 12, 'Masuk parkir - F UCK di area 2MB', '2026-03-11 15:58:31'),
(92, 12, 'Keluar parkir - F UCK dari area 2MB', '2026-03-11 15:58:35'),
(93, 2, 'Masuk parkir - B 6716 ARC di area 2MB', '2026-03-11 16:05:14'),
(94, 2, 'Keluar parkir - B 6716 ARC dari area 2MB', '2026-03-23 09:51:14'),
(95, NULL, 'Masuk parkir - K di area 2MB', '2026-03-23 10:17:52'),
(96, 4, 'Masuk parkir - B 6716 VRZ di area 2MB', '2026-03-23 10:32:57'),
(97, 4, 'Keluar parkir - B 6716 VRZ dari area 2MB', '2026-03-23 11:19:16'),
(98, NULL, 'Keluar parkir - K dari area 2MB', '2026-03-23 11:24:02'),
(99, 12, 'Masuk parkir - F UCK di area 2MB', '2026-03-23 11:24:13'),
(100, 12, 'Keluar parkir - F UCK dari area 2MB', '2026-03-23 11:46:01'),
(101, NULL, 'Masuk parkir - F di area 2MB', '2026-03-23 12:27:58'),
(102, NULL, 'Masuk parkir - F di area 2MB', '2026-03-31 06:47:32'),
(103, NULL, 'Keluar parkir - F dari area 2MB', '2026-03-31 06:47:42'),
(104, NULL, 'Keluar parkir - F dari area 2MB', '2026-03-31 07:07:10'),
(105, 2, 'Masuk parkir - B 6716 ARC di area 2MB', '2026-03-31 07:07:30'),
(106, NULL, 'Masuk parkir - N di area 2MB', '2026-03-31 07:07:39'),
(107, 9, 'Masuk parkir - T 0WN 3NR di area 2MB', '2026-03-31 07:30:50'),
(108, NULL, 'Masuk parkir - X di area 2MB', '2026-03-31 07:31:07'),
(109, 2, 'Keluar parkir - B 6716 ARC dari area 2MB', '2026-03-31 08:50:17'),
(110, 3, 'Masuk parkir - B 6816 VRZ di area 2MB', '2026-03-31 08:50:30'),
(111, 4, 'Masuk parkir - B 6716 VRZ di area 2MB', '2026-03-31 08:50:46'),
(112, 2, 'Masuk parkir - B 6716 ARC di area 2MB', '2026-03-31 08:55:18'),
(113, 4, 'Masuk parkir - B 6716 VRZ di area 2MB', '2026-03-31 08:55:26'),
(114, 3, 'Masuk parkir - B 6816 VRZ di area 2MB', '2026-03-31 08:55:38'),
(115, 12, 'Masuk parkir - F TSI 0UT di area 2MB', '2026-03-31 09:33:20'),
(116, NULL, 'Masuk parkir - K di area 2MB', '2026-03-31 09:33:27'),
(117, NULL, 'Keluar parkir - K dari area 2MB', '2026-03-31 15:50:00'),
(118, 12, 'Keluar parkir - F TSI 0UT dari area 2MB', '2026-03-31 16:24:09'),
(119, 12, 'Masuk parkir - F TSI 0UT di area 2MB', '2026-03-31 16:35:18'),
(120, 12, 'Keluar parkir - F TSI 0UT dari area 2MB', '2026-03-31 16:35:25'),
(121, 3, 'Keluar parkir - B 6816 VRZ dari area 2MB', '2026-03-31 16:35:36'),
(122, 2, 'Keluar parkir - B 6716 ARC dari area 2MB', '2026-03-31 16:36:00'),
(123, 4, 'Keluar parkir - B 6716 VRZ dari area 2MB', '2026-03-31 16:36:05'),
(124, 3, 'Masuk parkir - B 6816 VRZ di area 2MB', '2026-03-31 16:36:13'),
(125, 9, 'Masuk parkir - T 0WN 3NR di area 2MB', '2026-03-31 16:36:20'),
(126, 2, 'Masuk parkir - B 6716 ARC di area 2MB', '2026-03-31 16:36:33'),
(127, NULL, 'Masuk parkir - M di area 2MB', '2026-04-01 08:05:04'),
(128, NULL, 'Keluar parkir - M dari area 2MB', '2026-04-01 08:05:11'),
(129, 2, 'Keluar parkir - B 6716 ARC dari area 2MB', '2026-04-01 08:22:44'),
(130, 4, 'Masuk parkir - B 6716 VRZ di area 2MB', '2026-04-01 08:23:12'),
(131, NULL, 'Keluar parkir - X dari area 2MB', '2026-04-01 08:28:14'),
(132, 2, 'Masuk parkir - B 6716 ARC di area 2MB', '2026-04-01 08:28:39'),
(133, 12, 'Masuk parkir - F TSI 0UT di area 2MB', '2026-04-01 08:28:48'),
(134, 2, 'Keluar parkir - B 6716 ARC dari area 2MB', '2026-04-01 08:47:25'),
(135, 2, 'Masuk parkir - B 6716 ARC di area 1OB', '2026-04-01 09:29:37'),
(136, 9, 'Masuk parkir - T 0WN 3NR di area 2MB', '2026-04-01 09:32:21'),
(137, 2, 'Masuk parkir - B 6716 ARC di area 1OB', '2026-04-01 09:32:28'),
(138, 12, 'Masuk parkir - F TSI 0UT di area 2MB', '2026-04-01 09:32:36'),
(139, 4, 'Masuk parkir - B 6716 VRZ di area 2MB', '2026-04-01 09:32:50'),
(140, NULL, 'Masuk parkir - F di area 2MB', '2026-04-01 09:34:41'),
(141, 2, 'Masuk parkir - B 6716 ARC di area 1OB', '2026-04-01 09:48:07'),
(142, 9, 'Masuk parkir - T 0WN 3NR di area 1MA', '2026-04-01 09:48:40'),
(143, 12, 'Masuk parkir - F TSI 0UT di area 1MA', '2026-04-01 09:49:15'),
(144, NULL, 'Masuk parkir - K di area 1MA', '2026-04-01 09:49:22'),
(145, NULL, 'Masuk parkir - L di area 1MA', '2026-04-01 09:49:32'),
(146, 13, 'Masuk parkir - N AHU M4N di area 1OB', '2026-04-01 10:06:24');

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
(115, 9, 'B 6716 ARC', NULL, '2026-04-01 09:48:07', NULL, NULL, 0, 0, 'masuk', 2, 27, 'user'),
(116, 11, 'T 0WN 3NR', NULL, '2026-04-01 09:48:40', NULL, NULL, 0, 0, 'masuk', 9, 17, 'user'),
(117, 12, 'F TSI 0UT', NULL, '2026-04-01 09:49:15', NULL, NULL, 0, 0, 'masuk', 12, 17, 'user'),
(118, NULL, NULL, 'K', '2026-04-01 09:49:22', NULL, NULL, 0, 0, 'masuk', NULL, 17, 'tamu'),
(119, NULL, NULL, 'L', '2026-04-01 09:49:32', NULL, NULL, 0, 0, 'masuk', NULL, 17, 'tamu'),
(120, 14, 'N AHU M4N', NULL, '2026-04-01 10:06:24', NULL, NULL, 0, 0, 'masuk', 13, 27, 'user');

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
(8, 'Petugas', 'petugas#1', '$2y$10$sg4W5w4R7gnp.reCzYIMm.xb0zpqgixOSQIb2eaMN6CW1LFl.u9cq', 'petugas', 1),
(9, 'Owner', 'pemilik#1', '$2y$10$wI4HSLqYInpNsHGXmAK2c.gSiJVBnCCKsZ6Jg97DPTxnf31SRNysK', 'owner', 1),
(12, 'Admin?', 'bukan_admin', '$2y$10$y.AN1vTXFa683sVbZ58rh.8uzFmdb6ddDuVLRTnu3ek0PF8j4VdMG', 'admin', 1),
(13, 'Manusia', 'definitelyhuman', '$2y$10$Ecg2pyHsYrNNICIQkl1lCeJsJ7PNYvFZI6Ts20qg0Je.EkrF0dGXK', 'owner', 1);

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
  ADD KEY `fk_transaksi_tarif` (`id_tarif`),
  ADD KEY `fk_transaksi_user` (`id_user`),
  ADD KEY `fk_transaksi_area` (`id_area`),
  ADD KEY `fk_transaksi_kendaraan` (`id_kendaraan`);

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
  MODIFY `id_area` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `tb_kendaraan`
--
ALTER TABLE `tb_kendaraan`
  MODIFY `id_kendaraan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT untuk tabel `tb_tampilan`
--
ALTER TABLE `tb_tampilan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  MODIFY `id_parkir` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT untuk tabel `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tb_kendaraan`
--
ALTER TABLE `tb_kendaraan`
  ADD CONSTRAINT `fk_kendaraan_id_user` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  ADD CONSTRAINT `fk_log_aktivitas_user` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD CONSTRAINT `fk_transaksi_area` FOREIGN KEY (`id_area`) REFERENCES `tb_area_parkir` (`id_area`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi_kendaraan` FOREIGN KEY (`id_kendaraan`) REFERENCES `tb_kendaraan` (`id_kendaraan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi_tarif` FOREIGN KEY (`id_tarif`) REFERENCES `tb_tarif` (`id_tarif`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
