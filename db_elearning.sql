-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 11 Feb 2026 pada 05.03
-- Versi server: 10.4.19-MariaDB
-- Versi PHP: 8.0.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_elearning`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `link_materi` text DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `status` enum('active','registration','finished') DEFAULT 'registration',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `courses`
--

INSERT INTO `courses` (`id`, `name`, `description`, `link_materi`, `teacher_id`, `category`, `start_date`, `status`, `created_at`) VALUES
(1, 'Pemrograman Web Dasar', 'Belajar HTML, CSS, dan PHP Native', 'https://cloud.sekolah.id/s/xyz123', 2, 'Teknologi', '2023-10-01', 'active', '2026-02-09 16:16:15'),
(2, 'kelas jual obat', '', 'http://solid-skripsi.skripsijanuari.my.id/s/e2FKJxsnNXs7CK4', 2, '', '2026-02-10', 'registration', '2026-02-09 16:24:09'),
(3, 'cyber crime', 'belajar soal keamanan sistem', '', 6, 'IT', '2026-02-18', 'registration', '2026-02-11 03:51:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_date` date DEFAULT curdate(),
  `status` enum('active','completed','dropped') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `enrollment_date`, `status`) VALUES
(1, 3, 1, '2026-02-10', 'active'),
(2, 4, 1, '2026-02-10', 'active');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `link_materi` text DEFAULT NULL,
  `link_video` text DEFAULT NULL,
  `assignment_info` text DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `course_id`, `title`, `description`, `link_materi`, `link_video`, `assignment_info`, `deadline`, `created_at`) VALUES
(1, 1, 'Pertemuan 1: Pengenalan HTML', 'Mempelajari tag dasar HTML', NULL, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'Buat file index.html berisi biodata diri.', '2023-12-31 23:59:00', '2026-02-09 16:16:15'),
(2, 1, 'Peremuan 2 CSS dan JS', 'pengenalan css dan js', 'http://solid-skripsi.skripsijanuari.my.id/s/Cxr5korAdewwLoe', 'https://youtu.be/UXiwRmlCZ7E?si=GiQF2_Id8ViSTQnI', 'baut tampilan web sederhana', '2026-02-25 11:16:00', '2026-02-11 02:17:44'),
(3, 2, 'Pertemuan 1 dasar-dasar Marketing', 'belajar ilmu marketing dari dasar', 'http://solid-skripsi.skripsijanuari.my.id/s/SN6NkDrmsKpzXYw', 'https://youtu.be/UXiwRmlCZ7E?si=GiQF2_Id8ViSTQnI', 'kerjakan tugas di halaman terakhir', '2026-02-28 11:22:00', '2026-02-11 02:22:16'),
(4, 2, 'pertemuan 2 teknik dasar marketing', 'teknik2 dasar', 'http://solid-skripsi.skripsijanuari.my.id/s/gH6DpqGkCdrndLt', 'https://youtu.be/naz0-szzYXk?si=7OrTsnbduIKWcZ4E', 'kerjakan tugas di hal terakhir', '2026-02-11 11:27:00', '2026-02-11 02:27:27'),
(5, 3, 'tentang keamanan jaringan', 'etika dalam hacking', 'http://solid-skripsi.skripsijanuari.my.id/s/JZbxzWWXzEFRSbM', 'https://youtu.be/pxIxtZdgaAI?si=wAU6ztiUmqcG6xTW', 'rangkum vidionya', '2026-02-13 12:53:00', '2026-02-11 03:53:39'),
(6, 3, 'tahap hacking', 'pengumpulan informasi', 'http://solid-skripsi.skripsijanuari.my.id/s/ssp9XreoK2EjJA7', 'https://youtu.be/pxIxtZdgaAI?si=wAU6ztiUmqcG6xTW', 'rangkum vidionya', '2026-02-20 12:53:00', '2026-02-11 03:53:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `link_tugas` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `score` int(11) DEFAULT 0,
  `feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `submissions`
--

INSERT INTO `submissions` (`id`, `session_id`, `student_id`, `link_tugas`, `submitted_at`, `score`, `feedback`) VALUES
(1, 1, 3, 'http://solid-skripsi.skripsijanuari.my.id/s/5B4QfraZM3tLAAD', '2026-02-11 02:09:10', 85, 'Bagus, tapi rapikan kodenya.'),
(2, 1, 4, 'https://drive.google.com/hehe', '2026-02-11 02:05:05', 0, NULL),
(3, 2, 4, 'http://solid-skripsi.skripsijanuari.my.id/s/2TFeoXBJjXzfcFg', '2026-02-11 02:41:40', 0, NULL),
(4, 3, 4, 'http://solid-skripsi.skripsijanuari.my.id/s/YrQMxH4iAtYzKgE', '2026-02-11 03:02:13', 70, 'kamu kontol\r\n'),
(5, 5, 4, 'http://solid-skripsi.skripsijanuari.my.id/s/rTB8DmGYX9Eg83e', '2026-02-11 03:57:27', 99, 'nice mantap'),
(6, 5, 7, 'http://solid-skripsi.skripsijanuari.my.id/s/mmiSakdncnHnk3C', '2026-02-11 03:59:48', 40, 'salahhh');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','student') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin Utama', 'admin@sekolah.com', '123', 'admin', '2026-02-09 16:16:15'),
(2, 'Pak Budi Santoso', 'guru@sekolah.com', '123', 'teacher', '2026-02-09 16:16:15'),
(3, 'Siti Aminah', 'siswa1@sekolah.com', '123', 'student', '2026-02-09 16:16:15'),
(4, 'Ahmad Dani', 'siswa2@sekolah.com', '123', 'student', '2026-02-09 16:16:15'),
(5, 'pak bambang s.krg', 'bambang@gmail.com', '123456', 'teacher', '2026-02-09 16:22:01'),
(6, 'pak Sigit M.Kom, S.t', 'sigit@gmail.com', '123456', 'teacher', '2026-02-09 16:22:43'),
(7, 'fatima', 'fatimah@gmail.com', '123', 'student', '2026-02-11 03:58:49');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_teacher` (`teacher_id`);

--
-- Indeks untuk tabel `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_enroll_student` (`student_id`),
  ADD KEY `fk_enroll_course` (`course_id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_session_course` (`course_id`);

--
-- Indeks untuk tabel `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_submit_session` (`session_id`),
  ADD KEY `fk_submit_student` (`student_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `fk_enroll_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_enroll_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_session_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `fk_submit_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_submit_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
