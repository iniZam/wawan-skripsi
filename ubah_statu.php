<?php
session_start();
require "koneksi.php";

// 1. Cek apakah ada data ID dan STATUS yang dikirim
if (isset($_GET['id']) && isset($_GET['status'])) {
    
    $id_kelas = $_GET['id'];
    $status_baru = $_GET['status']; // Nilainya nanti: 'active' atau 'finished'
    
    // --- KEAMANAN (Backend Protection) ---
    // Pastikan Siswa tidak bisa akses
    if ($_SESSION['role'] == 'student') {
        echo "<script>alert('Akses Ditolak!'); window.location.href='kelas.php';</script>";
        exit;
    }

    // Jika Pengajar, Pastikan dia pemilik kelas tersebut
    if ($_SESSION['role'] == 'teacher') {
        $cek_guru = mysqli_query($koneksi, "SELECT teacher_id FROM courses WHERE id='$id_kelas'");
        $data_guru = mysqli_fetch_assoc($cek_guru);
        
        if ($data_guru['teacher_id'] != $_SESSION['id_user']) {
            echo "<script>alert('Anda tidak berhak mengubah kelas orang lain!'); window.location.href='kelas.php';</script>";
            exit;
        }
    }
    // -------------------------------------

    // 2. Lakukan Update Status
    $query = "UPDATE courses SET status = '$status_baru' WHERE id = '$id_kelas'";
    
    if (mysqli_query($koneksi, $query)) {
        // Berhasil
        header("Location: kelas.php");
    } else {
        echo "Gagal mengubah status: " . mysqli_error($koneksi);
    }

} else {
    // Jika dibuka tanpa parameter
    header("Location: kelas.php");
}
?>