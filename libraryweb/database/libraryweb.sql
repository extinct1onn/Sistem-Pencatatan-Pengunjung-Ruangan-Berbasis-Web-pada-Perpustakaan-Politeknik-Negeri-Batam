-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 01 Okt 2025 pada 02.57
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `libraryweb`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `nim` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `device_id` varchar(255) DEFAULT NULL,
  `is_registered_via_scan` tinyint(1) DEFAULT 0,
  `profile_picture` varchar(255) DEFAULT 'img/default_profile.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `nim`, `email`, `phone`, `password`, `role`, `device_id`, `is_registered_via_scan`, `profile_picture`, `created_at`, `updated_at`) VALUES
(1, 'Edward', '12345678910', 'edward@gmail.com', NULL, '$2y$10$KwCYTUE3ZEGm5gz93L5RIuxsmfQwA4iQMurOemzEvgey9XmI50Qb.', 'admin', NULL, 0, 'img/68db32de3b418_b59d547248d4e68d11c7d12978ddf21a.jpg', '2025-08-11 03:39:56', '2025-09-30 01:31:10'),
(2, 'Charles', '34523142123', 'charles@gmail.com', NULL, '$2y$10$7dYh0msjM1NnbBJjXyJPoukxLAwXbvn2Xg98EkVskV4TIjmquE1ye', 'user', NULL, 0, 'img/68c22bd74b30d_download (1).jpeg', '2025-08-11 04:03:25', '2025-09-11 01:54:31'),
(3, 'Evangeline', '2008200360', 'evangeline@gmail.com', NULL, '$2y$10$N2dpufHqggi6uQcW6jHu9e/1pHH2sLIgzcViVb.aPL2TygslQXLqq', 'admin', NULL, 0, 'img/default_profile.png', '2025-08-26 20:58:19', '2025-09-29 09:09:10'),
(4, 'Nelson', '4566841238', 'nelson@gmail.com', NULL, '$2y$10$dI4EwgXbnKXl7.wp01bDSuuggO8Rv8yaqQFKp3g0STXErRGXgD/MK', 'user', NULL, 0, 'img/default_profile.png', '2025-09-24 02:15:38', '2025-09-24 02:36:33'),
(5, 'Luiz', '34714892231', 'luiz@gmail.com', NULL, '$2y$10$hQ2tiRrJkqwtmdqj8Evdf.azGMRhQ4oA4vLK7qtvsnadF05sunRpS', 'user', NULL, 0, 'img/default_profile.png', '2025-09-24 02:22:43', '2025-09-24 02:36:33'),
(8, 'Kevin', '382476231872', 'kevin@gmail.com', NULL, '$2y$10$w5zAytnnrnkhRDGyGX/MNejFrv0Azu4qAoJ7Y3/fJKQ/7kyIJLhr6', 'user', NULL, 0, 'img/default_profile.png', '2025-09-25 02:16:32', '2025-09-25 02:20:12'),
(9, 'Joshua', '41128378390', 'joshua@gmail.com', NULL, '$2y$10$vleNXhIU4lL7Bg2BOaODYOLAUMaSozBXZncUV6ghJtaSro2U0SyuC', 'user', NULL, 0, 'img/default_profile.png', '2025-09-25 02:21:40', '2025-09-25 02:21:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `visits`
--

CREATE TABLE `visits` (
  `id` int(11) NOT NULL,
  `nim` varchar(100) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `tujuan` varchar(255) NOT NULL,
  `tanggal_kunjungan` date NOT NULL,
  `jam_masuk` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `device_id` varchar(255) DEFAULT NULL,
  `scan_location` varchar(100) DEFAULT 'entrance',
  `visit_method` enum('manual','scan') DEFAULT 'manual'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `visits`
--

INSERT INTO `visits` (`id`, `nim`, `nama`, `tujuan`, `tanggal_kunjungan`, `jam_masuk`, `created_at`, `device_id`, `scan_location`, `visit_method`) VALUES
(1, '12345678910', 'Edward', 'Ruang Komputer', '2025-09-24', '09:09:18', '2025-09-24 02:09:18', NULL, 'entrance', 'manual'),
(2, '34523142123', 'Charles', 'Ruang Baca', '2025-09-24', '09:10:10', '2025-09-24 02:10:10', NULL, 'entrance', 'manual'),
(3, '34714892231', 'Luiz', 'Ruang Baca', '2025-09-24', '09:23:13', '2025-09-24 02:23:13', NULL, 'entrance', 'manual'),
(4, '12345678910', 'Edward', 'Ruang Diskusi', '2025-09-24', '09:25:58', '2025-09-24 02:25:58', NULL, 'entrance', 'manual'),
(5, '4566841238', 'Nelson', 'Ruang Diskusi', '2025-09-24', '09:26:50', '2025-09-24 02:26:50', NULL, 'entrance', 'manual'),
(6, '12345678910', 'Edward', 'Ruang Komputer', '2025-09-24', '09:27:11', '2025-09-24 02:27:11', NULL, 'entrance', 'manual'),
(7, '382476231872', 'Kevin', 'Ruang Komputer', '2025-09-25', '09:18:36', '2025-09-25 02:18:36', NULL, 'entrance', 'manual'),
(8, '41128378390', 'Joshua', 'Ruang Komputer', '2025-09-25', '09:21:56', '2025-09-25 02:21:56', NULL, 'entrance', 'manual'),
(9, '12345678910', 'Edward', 'Ruang Diskusi', '2025-09-25', '22:27:53', '2025-09-25 15:27:53', NULL, 'entrance', 'manual'),
(10, '12345678910', 'Edward', 'Ruang Diskusi', '2025-09-29', '06:30:33', '2025-09-29 04:30:33', '4d1508e8fae85952d0bdeb9542487c06c8634c1d2e9e42f70e9f7e445e8771fe', 'entrance_main', 'scan'),
(11, '2008200360', 'Evangeline', 'Ruang Diskusi', '2025-09-29', '16:17:29', '2025-09-29 09:17:29', NULL, 'entrance', 'manual'),
(12, '34523142123', 'Charles', 'Ruang Baca', '2025-09-30', '03:20:55', '2025-09-30 01:20:55', NULL, 'entrance', 'manual'),
(13, '34523142123', 'Charles', 'Ruang Diskusi', '2025-09-30', '03:23:34', '2025-09-30 01:23:34', NULL, 'entrance', 'manual'),
(14, '34523142123', 'Charles', 'Ruang Komputer', '2025-09-30', '03:23:49', '2025-09-30 01:23:49', NULL, 'entrance', 'manual'),
(15, '382476231872', 'Kevin', 'Ruang Diskusi', '2025-09-30', '05:58:12', '2025-09-30 03:58:12', NULL, 'entrance', 'manual'),
(16, '41128378390', 'Joshua', 'Ruang Diskusi', '2025-09-30', '06:01:38', '2025-09-30 04:01:38', NULL, 'entrance', 'manual'),
(17, '41128378390', 'Joshua', 'Ruang Baca', '2025-09-30', '11:03:32', '2025-09-30 04:03:32', NULL, 'entrance', 'manual');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `device_id` (`device_id`);

--
-- Indeks untuk tabel `visits`
--
ALTER TABLE `visits`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `visits`
--
ALTER TABLE `visits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
