<?php
require "koneksi.php";
require "atas.php";

// --- PROTEKSI KEAMANAN ---
// Siswa tidak boleh melihat daftar semua siswa (opsional, untuk privasi)
if ($_SESSION['role'] == 'student') {
    echo "<script>alert('Akses Ditolak!'); window.location='index.php';</script>";
    exit;
}
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Data Siswa</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Siswa</li>
            </ol>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-user-graduate me-1"></i>
                        Daftar Siswa Terdaftar
                    </div>
                </div>
                <div class="card-body">
                    <table id="datatablesSimple">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Tanggal Bergabung</th>
                                <th>Jumlah Kelas Diikuti</th>
                                <?php if($_SESSION['role'] == 'admin') { ?>
                                    <th>Aksi</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            // Query untuk mengambil data siswa dan menghitung berapa kelas yang mereka ikuti
                            $query_siswa = "SELECT u.*, 
                                            (SELECT COUNT(*) FROM enrollments e WHERE e.student_id = u.id) as total_kelas 
                                            FROM users u 
                                            WHERE u.role = 'student' 
                                            ORDER BY u.name ASC";
                            
                            $result = mysqli_query($koneksi, $query_siswa);

                            while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= $row['name'] ?></strong></td>
                                <td><?= $row['email'] ?></td>
                                <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark"><?= $row['total_kelas'] ?> Kelas</span>
                                </td>
                                
                                <?php if($_SESSION['role'] == 'admin') { ?>
                                    <td class="text-center">
                                        <a href="#" class="btn btn-warning btn-sm" onclick="alert('Fitur edit siswa belum aktif')">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-danger btn-sm" onclick="alert('Fitur hapus siswa belum aktif')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                <?php } ?>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

<?php require "bawah.php"; ?>