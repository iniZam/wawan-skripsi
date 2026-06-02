<?php
require "koneksi.php";
require "atas.php";

$my_id = $_SESSION['id_user'];

// Proteksi agar Guru/Admin tidak nyasar ke sini (Opsional tapi baik untuk keamanan)
if ($_SESSION['role'] != 'student') {
    echo "<script>window.location='index.php';</script>";
    exit;
}

// 1. Hitung total kelas yang diikuti siswa
$q_kelas = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM enrollments WHERE student_id='$my_id'");
$total_kelas_saya = mysqli_fetch_assoc($q_kelas)['total'];

// 2. Hitung total tugas yang sudah dikumpulkan
$q_tugas = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM submissions WHERE student_id='$my_id'");
$total_tugas = mysqli_fetch_assoc($q_tugas)['total'];

// 3. Ambil data kelas yang diikuti beserta nama gurunya
$q_kelas_aktif = mysqli_query($koneksi, "SELECT c.*, u.name as nama_pengajar 
                                         FROM enrollments e 
                                         JOIN courses c ON e.course_id = c.id 
                                         JOIN users u ON c.teacher_id = u.id 
                                         WHERE e.student_id='$my_id' 
                                         ORDER BY e.enrolled_at DESC");
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            
            <h1 class="mt-4">Ruang Belajar Saya</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">Selamat datang kembali, <strong><?= $_SESSION['nama_user'] ?? 'Siswa' ?></strong>!</li>
            </ol>

            <!-- KARTU STATISTIK -->
            <div class="row">
                <div class="col-xl-6 col-md-6">
                    <div class="card bg-primary text-white mb-4 shadow-sm border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fs-6 fw-bold">Kelas yang Diikuti</div>
                                <h2 class="m-0"><?= $total_kelas_saya ?> Kelas</h2>
                            </div>
                            <i class="fas fa-book-reader fa-3x opacity-50"></i>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#kelas_saya">Lihat Kelas Saya</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-6 col-md-6">
                    <div class="card bg-success text-white mb-4 shadow-sm border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fs-6 fw-bold">Tugas Diselesaikan</div>
                                <h2 class="m-0"><?= $total_tugas ?> Tugas</h2>
                            </div>
                            <i class="fas fa-check-circle fa-3x opacity-50"></i>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <span class="small text-white">Terus tingkatkan belajarmu!</span>
                            <div class="small text-white"><i class="fas fa-thumbs-up"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DAFTAR KELAS SAYA -->
            

        </div>
    </main>

<?php require "bawah.php"; ?>