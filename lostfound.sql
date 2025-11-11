-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 11 Nov 2025 pada 14.03
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
-- Database: `lostfound`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `akun`
--

CREATE TABLE `akun` (
  `id_akun` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nomor_kontak` varchar(20) DEFAULT NULL,
  `role` enum('civitas','satpam') NOT NULL DEFAULT 'civitas',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `akun`
--

INSERT INTO `akun` (`id_akun`, `email`, `password`, `nama`, `nomor_kontak`, `role`, `created_at`) VALUES
(1, 'civitas@student.ub.ac.id', '$2y$10$zuDCCrxnpfdOl7Spae/M/..kFWL9qUCCpB8zznKIe472bKRU3Ohh6', 'najwan', '081234567890', 'civitas', '2025-10-21 19:41:18'),
(4, 'civitas2@student.ub.ac.id', '$2y$10$n6JXpYRQFwZq9WhHdw2mrORtl4QypG/BsrP5UILqY9UVIumQYBom2', 'Civitas2', '08912312313', 'civitas', '2025-10-28 15:25:36'),
(5, 'satpam@gmail.com', '$2y$10$s7Lg05UbcjCR6d0z29wG3u/I8sc8G7ThAvgquHfxdWMPxcpV1in/2', 'Ahmad Satpam', '08912312313', 'satpam', '2025-11-05 12:30:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `civitas`
--

CREATE TABLE `civitas` (
  `nomor_induk` varchar(20) NOT NULL,
  `id_akun` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `civitas`
--

INSERT INTO `civitas` (`nomor_induk`, `id_akun`) VALUES
('245150401111005', 1),
('24512312312321321', 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `klaim`
--

CREATE TABLE `klaim` (
  `id_klaim` int(11) NOT NULL,
  `id_laporan` int(11) NOT NULL,
  `id_akun` int(11) NOT NULL,
  `bukti_kepemilikan` varchar(255) DEFAULT NULL,
  `deskripsi_ciri` text NOT NULL,
  `status_klaim` enum('diajukan','diverifikasi','ditolak') DEFAULT 'diajukan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `klaim`
--

INSERT INTO `klaim` (`id_klaim`, `id_laporan`, `id_akun`, `bukti_kepemilikan`, `deskripsi_ciri`, `status_klaim`, `created_at`) VALUES
(1, 39, 1, 'uploads/bukti_klaim/bukti_klaim_39_1_1762864825.jpg', 'wqeqweqweq', 'diajukan', '2025-11-11 12:40:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int(11) NOT NULL,
  `id_akun` int(11) NOT NULL,
  `tipe_laporan` enum('hilang','ditemukan') NOT NULL DEFAULT 'hilang',
  `nama_barang` varchar(255) NOT NULL,
  `deskripsi_fisik` text DEFAULT NULL,
  `kategori` enum('elektronik','dokumen','pakaian','lainnya') NOT NULL,
  `lokasi` enum('Smart Class Gedung F','Junction','Gedung Kreativitas Mahasiswa (GKM)','Kantin','Ruang Baca','Laboratorium Pembelajaran','Ruang Ujian','Ruang Tunggu','Gazebo lantai 4','Area Parkir','EduTech','Mushola Ulul Al-Baab','Auditorium Algoritma') NOT NULL,
  `waktu` datetime NOT NULL,
  `status` enum('belum_ditemukan','ditemukan','sudah_diambil') NOT NULL DEFAULT 'belum_ditemukan',
  `nim_pengambil` varchar(20) DEFAULT NULL,
  `foto_bukti` varchar(255) DEFAULT NULL,
  `waktu_diambil` datetime DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `laporan`
--

INSERT INTO `laporan` (`id_laporan`, `id_akun`, `tipe_laporan`, `nama_barang`, `deskripsi_fisik`, `kategori`, `lokasi`, `waktu`, `status`, `nim_pengambil`, `foto_bukti`, `waktu_diambil`, `foto`, `created_at`) VALUES
(38, 1, 'hilang', 'pisang', 'asdas', 'elektronik', 'Ruang Ujian', '2025-11-04 21:26:00', 'sudah_diambil', '245150401111005', 'uploads/bukti/bukti_38_1762859931.jpg', '2025-11-11 12:18:51', 'uploads/laporan/laporan_1_pisang_20251104_4281.jpg', '2025-11-04 14:26:34'),
(39, 4, 'hilang', 'kamera', 'asda', 'elektronik', 'Ruang Ujian', '2025-11-04 21:38:00', 'sudah_diambil', '24512312312321321', 'uploads/bukti/nim_24512312312321321_laporan_39_img_1762860327.jpg', '2025-11-11 12:25:27', 'uploads/laporan/laporan_4_kamera_20251104_1383.png', '2025-11-04 14:38:49'),
(42, 1, 'hilang', 'botol', 'sdffsf', 'dokumen', 'Ruang Tunggu', '2025-11-04 22:09:00', 'belum_ditemukan', NULL, NULL, NULL, 'uploads/laporan/laporan_1_botol_20251104_1499.jpg', '2025-11-04 15:09:28'),
(50, 1, 'hilang', 'botol', 'asdadasd', 'lainnya', 'Ruang Baca', '2025-11-05 20:57:00', 'belum_ditemukan', NULL, NULL, NULL, 'uploads/laporan/laporan_1_botol_20251105_2388.png', '2025-11-05 13:57:47'),
(51, 5, 'ditemukan', 'cas laptop', 'ada di atas meja', 'elektronik', 'Kantin', '2025-11-05 22:13:00', 'ditemukan', NULL, NULL, NULL, NULL, '2025-11-05 15:13:34'),
(52, 1, 'hilang', 'cas laptop', 'di bawah meja', 'elektronik', 'Ruang Baca', '2025-11-05 22:14:00', 'belum_ditemukan', NULL, NULL, NULL, 'uploads/laporan/laporan_1_cas-laptop_20251105_9822.png', '2025-11-05 15:14:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `satpam`
--

CREATE TABLE `satpam` (
  `id_satpam` int(11) NOT NULL,
  `id_akun` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `satpam`
--

INSERT INTO `satpam` (`id_satpam`, `id_akun`, `created_at`) VALUES
(1, 5, '2025-11-05 12:30:57');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `akun`
--
ALTER TABLE `akun`
  ADD PRIMARY KEY (`id_akun`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `civitas`
--
ALTER TABLE `civitas`
  ADD PRIMARY KEY (`nomor_induk`),
  ADD KEY `id_akun` (`id_akun`);

--
-- Indeks untuk tabel `klaim`
--
ALTER TABLE `klaim`
  ADD PRIMARY KEY (`id_klaim`),
  ADD UNIQUE KEY `unique_klaim` (`id_laporan`,`id_akun`),
  ADD KEY `id_akun` (`id_akun`);

--
-- Indeks untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_akun` (`id_akun`);

--
-- Indeks untuk tabel `satpam`
--
ALTER TABLE `satpam`
  ADD PRIMARY KEY (`id_satpam`),
  ADD UNIQUE KEY `unique_akun` (`id_akun`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `akun`
--
ALTER TABLE `akun`
  MODIFY `id_akun` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `klaim`
--
ALTER TABLE `klaim`
  MODIFY `id_klaim` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT untuk tabel `satpam`
--
ALTER TABLE `satpam`
  MODIFY `id_satpam` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `civitas`
--
ALTER TABLE `civitas`
  ADD CONSTRAINT `civitas_ibfk_1` FOREIGN KEY (`id_akun`) REFERENCES `akun` (`id_akun`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `klaim`
--
ALTER TABLE `klaim`
  ADD CONSTRAINT `klaim_ibfk_1` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`) ON DELETE CASCADE,
  ADD CONSTRAINT `klaim_ibfk_2` FOREIGN KEY (`id_akun`) REFERENCES `akun` (`id_akun`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_akun`) REFERENCES `akun` (`id_akun`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `satpam`
--
ALTER TABLE `satpam`
  ADD CONSTRAINT `satpam_ibfk_1` FOREIGN KEY (`id_akun`) REFERENCES `akun` (`id_akun`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
