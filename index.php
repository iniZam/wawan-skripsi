<?php
require "koneksi.php";
require "atas.php";

// --- QUERY STATISTIK (Mengambil jumlah data) ---
// 1. Total Siswa
$q1 = mysqli_query($koneksi, "SELECT COUNT(*) as jumlah FROM users WHERE role='student'");
$total_siswa = mysqli_fetch_assoc($q1)['jumlah'];

// 2. Total Pengajar
$q2 = mysqli_query($koneksi, "SELECT COUNT(*) as jumlah FROM users WHERE role='teacher'");
$total_guru = mysqli_fetch_assoc($q2)['jumlah'];

// 3. Kelas yang Sedang Aktif (Berjalan)
$q3 = mysqli_query($koneksi, "SELECT COUNT(*) as jumlah FROM courses WHERE status='active'");
$kelas_aktif = mysqli_fetch_assoc($q3)['jumlah'];

// 4. Total Semua Kelas (Termasuk selesai/draft)
$q4 = mysqli_query($koneksi, "SELECT COUNT(*) as jumlah FROM courses");
$total_kelas = mysqli_fetch_assoc($q4)['jumlah'];
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Dashboard Utama</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">Sistem kelola sekolah </li>
            </ol>
            
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>Total Siswa</div>
                                <div class="h2 mb-0"><?= $total_siswa ?></div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">Lihat Detail</a>
                            <div class="small text-white"><i class="fas fa-user-graduate"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>Total Pengajar</div>
                                <div class="h2 mb-0"><?= $total_guru ?></div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="pengajar.php">Lihat Pengajar</a>
                            <div class="small text-white"><i class="fas fa-chalkboard-teacher"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>Kelas Aktif</div>
                                <div class="h2 mb-0"><?= $kelas_aktif ?></div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="kelas.php">Lihat Kelas</a>
                            <div class="small text-white"><i class="fas fa-play-circle"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card bg-danger text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>Total Materi</div>
                                <div class="h2 mb-0"><?= $total_kelas ?></div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="kelas.php">Kelola Materi</a>
                            <div class="small text-white"><i class="fas fa-book"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-chart-area me-1"></i>
                            Statistik Aktivitas (Demo)
                        </div>
                        <div class="card-body"><canvas id="myAreaChart" width="100%" height="40"></canvas></div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-history me-1"></i>
                            5 Kelas Terbaru Ditambahkan
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nama Kelas</th>
                                            <th>Tanggal</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Query: Ambil 5 data terakhir (LIMIT 5)
                                        $query_mini = "SELECT name, start_date, status FROM courses ORDER BY id DESC LIMIT 5";
                                        $result_mini = mysqli_query($koneksi, $query_mini);
                                        
                                        if(mysqli_num_rows($result_mini) > 0){
                                            while($row = mysqli_fetch_assoc($result_mini)){
                                                $badge = ($row['status'] == 'active') ? 'bg-success' : 'bg-secondary';
                                        ?>
                                            <tr>
                                                <td><?= $row['name'] ?></td>
                                                <td><?= date('d/m/Y', strtotime($row['start_date'])) ?></td>
                                                <td><span class="badge <?= $badge ?>"><?= ucfirst($row['status']) ?></span></td>
                                            </tr>
                                        <?php 
                                            }
                                        } else {
                                            echo "<tr><td colspan='3' class='text-center'>Belum ada kelas.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end mt-3">
                                <a href="kelas.php" class="btn btn-sm btn-primary">Lihat Semua Kelas <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </main>

<?php require "bawah.php"; ?>