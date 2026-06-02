<?php
require "koneksi.php";
require "atas.php";

// Ambil data session user yang sedang login
$id_user = $_SESSION['id_user'];
$role    = $_SESSION['role'];

// LOGIKA KUERI BERDASARKAN PERAN (ROLE)
if ($role == 'admin') {
    // Admin melihat semua kelas
    $query_tabel = "SELECT c.*, u.name as nama_pengajar 
                    FROM courses c
                    JOIN users u ON c.teacher_id = u.id 
                    ORDER BY c.start_date DESC";
} else if ($role == 'teacher') {
    // Guru hanya melihat kelas yang dia ajar
    $query_tabel = "SELECT c.*, u.name as nama_pengajar 
                    FROM courses c
                    JOIN users u ON c.teacher_id = u.id 
                    WHERE c.teacher_id = '$id_user'
                    ORDER BY c.start_date DESC";
} else if ($role == 'student') {
    // Siswa hanya melihat kelas yang di-enroll / diikuti
    $query_tabel = "SELECT c.*, u.name as nama_pengajar 
                    FROM courses c
                    JOIN users u ON c.teacher_id = u.id 
                    JOIN enrollments e ON c.id = e.course_id
                    WHERE e.student_id = '$id_user'
                    ORDER BY c.start_date DESC";
}

$result_tabel = mysqli_query($koneksi, $query_tabel);
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Daftar Kelas</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Kelas</li>
            </ol>

            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <div>
                        <i class="fas fa-table me-1"></i>
                        <?php 
                        if ($role == 'student') {
                            echo "Kelas Yang Saya Ikuti";
                        } else if ($role == 'teacher') {
                            echo "Kelas Yang Saya Ajar";
                        } else {
                            echo "Manajemen Seluruh Kelas";
                        }
                        ?>
                    </div>
                    
                    <?php if ($role == 'admin' || $role == 'teacher') { ?>
                        <a href="tambah_kelas.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Buat Kelas Baru
                        </a>
                    <?php } ?>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="datatablesSimple" class="table table-bordered table-striped align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Kelas</th>
                                    <th>Pengajar</th>
                                    <th>Kategori</th>
                                    <th>Tanggal Mulai</th>
                                    <th width="25%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (mysqli_num_rows($result_tabel) > 0) {
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($result_tabel)) {
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><strong><?= $row['name'] ?></strong></td>
                                    <td><?= $row['nama_pengajar'] ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?= $row['category'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?= date('d M Y', strtotime($row['start_date'])) ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="pertemuan.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm text-white me-1"> 
                                            <i class="fas fa-door-open"></i> Masuk Kelas
                                        </a>

                                        <?php if ($role == 'admin') { ?>
                                            <a href="edit_kelas.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm me-1" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="hapus_kelas.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus kelas ini beserta seluruh data di dalamnya?')" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        
                                        <?php } else if ($role == 'teacher' && $row['teacher_id'] == $id_user) { ?>
                                            <a href="edit_kelas.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm text-dark" title="Edit Kelas">
                                                <i class="fas fa-pen"></i> Edit
                                            </a>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php
                                    }
                                } else {
                                    // Pesan dinamis jika data kelas kosong berdasarkan role
                                    echo "<tr><td colspan='6' class='text-center py-4 text-muted'>";
                                    if ($role == 'student') {
                                        echo "<em>Anda belum terdaftar di kelas manapun. Silakan hubungi Administrator.</em>";
                                    } else if ($role == 'teacher') {
                                        echo "<em>Belum ada kelas yang ditugaskan kepada Anda.</em>";
                                    } else {
                                        echo "<em>Belum ada data kelas di dalam sistem.</em>";
                                    }
                                    echo "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php require "bawah.php"; ?>