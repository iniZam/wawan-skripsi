<?php
session_start();
require "koneksi.php";

// 1. Validasi Keamanan: HANYA ADMIN yang boleh menghapus kelas
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses Ditolak! Hanya Admin yang berhak menghapus kelas.'); window.location='kelas.php';</script>";
    exit;
}

// 2. Pastikan ada ID yang dikirim
if (!isset($_GET['id'])) {
    echo "<script>window.location='kelas.php';</script>";
    exit;
}

$id_kelas = $_GET['id'];

// 3. Eksekusi Hapus
// Catatan: Jika tabel MySQL kamu sudah menggunakan relasi ON DELETE CASCADE, 
// maka menghapus kelas akan otomatis menghapus seluruh pertemuan, tugas, dan nilai di dalamnya.
$query_hapus = "DELETE FROM courses WHERE id = '$id_kelas'";

if (mysqli_query($koneksi, $query_hapus)) {
    echo "<script>alert('Kelas berhasil dihapus secara permanen!'); window.location.href='kelas.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus kelas: " . mysqli_error($koneksi) . "'); window.location.href='kelas.php';</script>";
}
?>