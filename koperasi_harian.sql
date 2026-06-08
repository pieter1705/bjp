-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 13 Jan 2026 pada 03.20
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
-- Database: `koperasi_harian`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `anggota`
--

CREATE TABLE `anggota` (
  `id_anggota` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `tempat_usaha` varchar(100) DEFAULT NULL,
  `telepon` varchar(15) DEFAULT NULL,
  `total_pinjaman` decimal(12,2) DEFAULT 0.00,
  `tgl_pinjaman` date DEFAULT NULL,
  `status_pinjaman` enum('Belum Lunas','Lunas') DEFAULT 'Belum Lunas',
  `foto_ktp` varchar(255) DEFAULT NULL,
  `foto_nasabah` varchar(255) DEFAULT NULL,
  `tgl_gabung` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `anggota`
--

INSERT INTO `anggota` (`id_anggota`, `nama`, `alamat`, `tempat_usaha`, `telepon`, `total_pinjaman`, `tgl_pinjaman`, `status_pinjaman`, `foto_ktp`, `foto_nasabah`, `tgl_gabung`) VALUES
(3, 'Bryan Leatemia', 'Tanah Tinggi', 'Tanah Tinggi', '082248884462', 0.00, '2026-01-03', 'Belum Lunas', 'ktp_6964c9f7ae2aa.jpeg', 'profil_6964c9f7aeae8.jpeg', NULL),
(4, 'Felix Lodar', 'Tanah Tinggi', 'Tanah Tinggi', '085213678937', 0.00, '2026-01-05', 'Belum Lunas', 'ktp_6964c9bf20e56.jpeg', 'profil_6964c9bf21490.jpeg', NULL),
(5, 'Maya Ivon Soumokil ', 'Tanah Tinggi', 'Tanah Tinggi', '085244336468', 0.00, '2026-01-05', 'Belum Lunas', 'default.jpg', 'profil_69649b940f1b3.jpeg', NULL),
(6, 'Bronson Latuar', 'Tanah Tinggi', 'Tanah Tinggi', '', 0.00, '2026-01-06', 'Belum Lunas', 'ktp_6964c8fe8fb1f.jpeg', 'profil_6964c8fe90031.jpeg', NULL),
(7, 'Amelia Natalia Serlaloy', 'Gudang Arang', 'Gudang Arang', '', 0.00, '2026-01-05', 'Belum Lunas', 'ktp_6964c8e9aed8e.jpeg', 'profil_6964c8e9af60e.jpeg', NULL),
(8, 'Denny Ivanda Tuhumury ', 'Tanah Tinggi', 'Tanah Tinggi', '', 0.00, '2026-01-07', 'Belum Lunas', 'ktp_6964c8c7ce66c.jpeg', 'profil_6964c8c7cebc7.jpeg', NULL),
(9, 'Johana Polnaya', 'Gudang Arang', 'Gudang Arang', '', 0.00, '2026-01-07', 'Belum Lunas', 'ktp_6964c8b59881b.jpeg', 'profil_6964c8b598f72.jpeg', NULL),
(10, 'Mike Sahetapy ', 'Gudang Arang', 'Gudang Arang', '', 0.00, '2026-01-07', 'Belum Lunas', 'ktp_6964c8a0d27eb.jpeg', 'profil_6964c8a0d2e93.jpeg', NULL),
(11, 'Jeselina Latupeirissa ', 'Gudang Arang', 'Gudang Arang', '', 0.00, '2026-01-07', 'Belum Lunas', 'ktp_6964c7d6675be.jpeg', 'profil_6964c7d667ecb.jpeg', NULL),
(12, 'Mita Pattiasina ', 'Gudang Arang', 'Gudang Arang', '-', 0.00, '2026-01-07', 'Belum Lunas', 'ktp_6964c799cfe17.jpeg', 'profil_6964c799d052e.jpeg', NULL),
(13, 'Juneth Latuheru ', 'Gudang Arang', 'Gudang Arang', '081247750336', 0.00, '2026-01-05', 'Belum Lunas', 'ktp_6964c6a6c8e78.jpeg', 'profil_6964c6a6c96ab.jpeg', NULL),
(14, 'Djunaidi Morees', 'Ponogoro', 'Ponogoro', '085287837916', 0.00, '2026-01-08', 'Belum Lunas', 'ktp_6964c65734afb.jpeg', 'profil_6964c65735001.jpeg', NULL),
(15, 'Ambar T', 'Tanah Tinggi', 'Tanah Tinggi', '', 0.00, '2026-01-12', 'Belum Lunas', 'ktp_6964c5ee42a62.jpeg', 'profil_6964c5ee4364c.jpeg', NULL),
(16, 'Ade De Fretese', 'Gudang Arang', 'Gudang Arang', '', 0.00, '0000-00-00', 'Belum Lunas', 'default.jpg', 'default.jpg', NULL),
(17, 'Yakob Kalibonso', 'Tanah Tinggi', 'Tanah Tinggi', '082238666004', 0.00, '2026-01-12', 'Belum Lunas', 'ktp_6964c471f0b9c.jpeg', 'profil_6964c471f1614.jpeg', NULL),
(18, 'augustina isabella patrisia bala ubleeuw', 'Kudamati-FArmasi', 'Jaksa', '-', 0.00, '2026-01-12', 'Belum Lunas', 'ktp_6964cbfb90c4a.jpeg', 'profil_6964cbfb91482.jpeg', NULL),
(19, 'stevi pattiasina', 'Gudang Arang', 'bawa motor sampah', '082188091833', 0.00, '2026-01-12', 'Belum Lunas', 'ktp_6964cc2dc46bd.jpeg', 'profil_6964cc2dc4f01.jpeg', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `angsuran`
--

CREATE TABLE `angsuran` (
  `id_angsuran` int(11) NOT NULL,
  `id_pinjaman` int(11) DEFAULT NULL,
  `id_kolektor` int(11) DEFAULT NULL,
  `tgl_bayar` timestamp NOT NULL DEFAULT current_timestamp(),
  `nominal_bayar` decimal(12,2) DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `tanggal_bayar` date DEFAULT NULL,
  `angsuran_ke` int(2) DEFAULT NULL,
  `status_verifikasi` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `angsuran`
--

INSERT INTO `angsuran` (`id_angsuran`, `id_pinjaman`, `id_kolektor`, `tgl_bayar`, `nominal_bayar`, `petugas_id`, `tanggal_bayar`, `angsuran_ke`, `status_verifikasi`) VALUES
(4, 5, 1, '2026-01-12 05:37:51', 60000.00, NULL, '2026-01-03', 20, '1'),
(5, 5, 1, '2026-01-12 05:41:04', 60000.00, NULL, '2026-01-05', 1, '1'),
(6, 5, 1, '2026-01-12 05:41:25', 60000.00, NULL, '2026-01-06', 2, '1'),
(7, 6, 1, '2026-01-12 05:50:19', 90000.00, NULL, '2026-01-05', 20, '1'),
(8, 6, 1, '2026-01-12 05:50:30', 90000.00, NULL, '2026-01-06', 1, '1'),
(9, 6, 1, '2026-01-12 05:50:46', 90000.00, NULL, '2026-01-07', 2, '1'),
(10, 6, 1, '2026-01-12 05:54:54', 90000.00, NULL, '2026-01-08', 3, '1'),
(11, 6, 1, '2026-01-12 05:55:28', 90000.00, NULL, '2026-01-09', 4, '1'),
(12, 6, 1, '2026-01-12 05:55:40', 90000.00, NULL, '2026-01-10', 5, '1'),
(13, 19, 1, '2026-01-12 08:20:35', 60000.00, NULL, '2026-01-12', 20, '0'),
(14, 19, 1, '2026-01-12 08:20:43', 60000.00, NULL, '2026-01-12', 1, '0'),
(15, 18, 1, '2026-01-12 10:31:30', 60000.00, NULL, '2026-01-12', 20, '0'),
(16, 18, 1, '2026-01-12 10:31:42', 60000.00, NULL, '2026-01-12', 1, '0'),
(17, 21, 1, '2026-01-12 10:32:25', 180000.00, NULL, '2026-01-12', 20, '0'),
(18, 20, 1, '2026-01-12 10:32:42', 60000.00, NULL, '2026-01-12', 20, '0'),
(19, 5, 1, '2026-01-12 10:39:15', 60000.00, NULL, '2026-01-12', 3, '0'),
(20, 5, 1, '2026-01-12 10:39:57', 60000.00, NULL, '2026-01-12', 4, '0'),
(21, 5, 1, '2026-01-12 10:40:50', 60000.00, NULL, '2026-01-12', 5, '0'),
(22, 5, 1, '2026-01-12 10:40:55', 60000.00, NULL, '2026-01-12', 6, '0'),
(23, 5, 1, '2026-01-12 10:40:59', 60000.00, NULL, '2026-01-12', 7, '0'),
(24, 6, 1, '2026-01-12 10:50:23', 90000.00, NULL, '2026-01-12', 6, '0'),
(25, 7, 1, '2026-01-12 11:38:29', 120000.00, NULL, '2026-01-06', 20, '0'),
(26, 7, 1, '2026-01-12 11:38:38', 120000.00, NULL, '2026-01-07', 1, '0'),
(27, 7, 1, '2026-01-12 11:38:45', 120000.00, NULL, '2026-01-08', 2, '0'),
(28, 7, 1, '2026-01-12 11:38:53', 120000.00, NULL, '2026-01-09', 3, '0'),
(29, 7, 1, '2026-01-12 11:39:01', 120000.00, NULL, '2026-01-10', 4, '0'),
(30, 11, 1, '2026-01-12 11:40:07', 150000.00, NULL, '2026-01-06', 20, '0'),
(31, 11, 1, '2026-01-12 11:40:14', 150000.00, NULL, '2026-01-07', 1, '0'),
(32, 11, 1, '2026-01-12 11:40:21', 150000.00, NULL, '2026-01-08', 2, '0'),
(33, 11, 1, '2026-01-12 11:40:28', 150000.00, NULL, '2026-01-09', 3, '0'),
(34, 11, 1, '2026-01-12 11:40:35', 150000.00, NULL, '2026-01-10', 4, '0');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pinjaman`
--

CREATE TABLE `pinjaman` (
  `id_pinjaman` int(11) NOT NULL,
  `id_anggota` int(11) DEFAULT NULL,
  `plafon_pinjaman` decimal(12,2) DEFAULT NULL,
  `lama_hari` int(11) DEFAULT NULL,
  `bunga_persen` decimal(5,2) DEFAULT NULL,
  `total_tagihan` decimal(12,2) DEFAULT NULL,
  `tgl_cair` date DEFAULT NULL,
  `status` enum('aktif','lunas') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pinjaman`
--

INSERT INTO `pinjaman` (`id_pinjaman`, `id_anggota`, `plafon_pinjaman`, `lama_hari`, `bunga_persen`, `total_tagihan`, `tgl_cair`, `status`) VALUES
(5, 3, 1000000.00, 20, 20.00, 1200000.00, '2026-01-03', 'aktif'),
(6, 4, 1500000.00, 20, 20.00, 1800000.00, '2026-01-05', 'aktif'),
(7, 5, 2000000.00, 20, 20.00, 2400000.00, '2026-01-06', 'aktif'),
(8, 6, 1000000.00, 20, 20.00, 1200000.00, '2026-01-06', 'aktif'),
(9, 7, 2500000.00, 20, 20.00, 3000000.00, '2026-01-06', 'aktif'),
(10, 16, 500000.00, 20, 20.00, 600000.00, '2026-01-06', 'aktif'),
(11, 8, 2500000.00, 20, 20.00, 3000000.00, '2026-01-07', 'aktif'),
(12, 9, 2000000.00, 20, 20.00, 2400000.00, '2026-01-07', 'aktif'),
(13, 10, 1500000.00, 20, 20.00, 1800000.00, '2026-01-07', 'aktif'),
(14, 11, 500000.00, 20, 20.00, 600000.00, '2026-01-07', 'aktif'),
(15, 12, 500000.00, 20, 20.00, 600000.00, '2026-01-07', 'aktif'),
(16, 13, 1000000.00, 20, 20.00, 1200000.00, '2026-01-08', 'aktif'),
(17, 14, 1000000.00, 20, 20.00, 1200000.00, '2026-01-09', 'aktif'),
(18, 15, 1000000.00, 20, 20.00, 1200000.00, '2026-01-12', 'aktif'),
(19, 17, 1000000.00, 20, 20.00, 1200000.00, '2026-01-12', 'aktif'),
(20, 19, 1000000.00, 20, 20.00, 1200000.00, '2026-01-12', 'aktif'),
(21, 18, 3000000.00, 20, 20.00, 3600000.00, '2026-01-12', 'aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `role` enum('admin','manager','petugas') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(1, 'Pieter', 'admin123', 'Pieter Toisuta', 'admin'),
(2, 'Joris', 'manager123', 'Joris Ohoilulin', 'manager'),
(3, 'Karel', 'resort2', 'Karel Saimima', 'petugas');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_kolektor`
--

CREATE TABLE `user_kolektor` (
  `id_user` int(11) NOT NULL,
  `nama_kolektor` varchar(100) NOT NULL,
  `wilayah_tugas` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user_kolektor`
--

INSERT INTO `user_kolektor` (`id_user`, `nama_kolektor`, `wilayah_tugas`) VALUES
(1, 'Admin Utama', 'Pusat'),
(2, 'Kolektor A', 'Wilayah Utara');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id_anggota`);

--
-- Indeks untuk tabel `angsuran`
--
ALTER TABLE `angsuran`
  ADD PRIMARY KEY (`id_angsuran`),
  ADD KEY `id_pinjaman` (`id_pinjaman`);

--
-- Indeks untuk tabel `pinjaman`
--
ALTER TABLE `pinjaman`
  ADD PRIMARY KEY (`id_pinjaman`),
  ADD KEY `pinjaman_ibfk_1` (`id_anggota`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- Indeks untuk tabel `user_kolektor`
--
ALTER TABLE `user_kolektor`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id_anggota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `angsuran`
--
ALTER TABLE `angsuran`
  MODIFY `id_angsuran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT untuk tabel `pinjaman`
--
ALTER TABLE `pinjaman`
  MODIFY `id_pinjaman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `user_kolektor`
--
ALTER TABLE `user_kolektor`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `angsuran`
--
ALTER TABLE `angsuran`
  ADD CONSTRAINT `angsuran_ibfk_1` FOREIGN KEY (`id_pinjaman`) REFERENCES `pinjaman` (`id_pinjaman`);

--
-- Ketidakleluasaan untuk tabel `pinjaman`
--
ALTER TABLE `pinjaman`
  ADD CONSTRAINT `pinjaman_ibfk_1` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
