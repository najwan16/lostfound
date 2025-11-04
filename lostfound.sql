-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 29 Okt 2025 pada 03.40
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
(3, 'satpam@filkom.ub.ac.id', '$2y$10$6Zh6Ckj2T1KA/PKnybBP8eId8tsm7nkim4WE8XEagCLcc7ezmEpk2', 'Satpam Filkom', '08912312313', 'satpam', '2025-10-28 15:06:29'),
(4, 'civitas2@student.ub.ac.id', '$2y$10$n6JXpYRQFwZq9WhHdw2mrORtl4QypG/BsrP5UILqY9UVIumQYBom2', 'Civitas2', '08912312313', 'civitas', '2025-10-28 15:25:36');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `laporan`
--

INSERT INTO `laporan` (`id_laporan`, `id_akun`, `tipe_laporan`, `nama_barang`, `deskripsi_fisik`, `kategori`, `lokasi`, `waktu`, `status`, `created_at`) VALUES
(4, 1, 'hilang', 'kamera', 'di atas meja', 'elektronik', 'Kantin', '2025-10-25 21:41:00', 'belum_ditemukan', '2025-10-25 14:41:30'),
(5, 1, 'hilang', 'baju', 'di sekitar edu tech', 'pakaian', 'EduTech', '2025-10-25 21:46:00', 'belum_ditemukan', '2025-10-25 14:46:42'),
(6, 1, 'hilang', 'laptop asus', 'di kantin tahu telor', 'elektronik', 'Kantin', '2025-10-25 22:37:00', 'belum_ditemukan', '2025-10-25 15:37:10'),
(7, 1, 'hilang', 'botol', 'asdada', 'lainnya', 'EduTech', '2025-10-27 13:43:00', 'belum_ditemukan', '2025-10-27 06:43:21'),
(8, 4, 'hilang', 'kamera', 'asadasd', 'elektronik', 'Ruang Ujian', '2025-10-28 22:26:00', 'belum_ditemukan', '2025-10-28 15:26:03');

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
-- Indeks untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_akun` (`id_akun`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `akun`
--
ALTER TABLE `akun`
  MODIFY `id_akun` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `civitas`
--
ALTER TABLE `civitas`
  ADD CONSTRAINT `civitas_ibfk_1` FOREIGN KEY (`id_akun`) REFERENCES `akun` (`id_akun`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_akun`) REFERENCES `akun` (`id_akun`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
