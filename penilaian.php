<?php
require "koneksi.php";
require "atas.php";

// Ambil ID Sesi
$id_sesi = $_GET['id'];

// 1. Ambil Data Sesi & Kelas (Untuk Validasi)
$q_sesi = mysqli_query($koneksi, "SELECT s.*, c.teacher_id, c.name as nama_kelas 
                                  FROM sessions s 
                                  JOIN courses c ON s.course_id = c.id 
                                  WHERE s.id = '$id_sesi'");
$sesi = mysqli_fetch_assoc($q_sesi);

// --- KEAMANAN (Hanya Guru Pemilik & Admin) ---
if (!$sesi) {
    echo "<script>alert('Data tidak ditemukan'); window.location='index.php';</script>"; exit;
}
if ($_SESSION['role'] == 'student') {
    echo "<script>alert('Akses Ditolak'); window.location='index.php';</script>"; exit;
}
if ($_SESSION['role'] == 'teacher' && $sesi['teacher_id'] != $_SESSION['id_user']) {
    echo "<script>alert('Bukan kelas Anda!'); window.location='kelas.php';</script>"; exit;
}

// --- PROSES SIMPAN NILAI ---
if (isset($_POST['simpan_nilai'])) {
    $id_sub   = $_POST['submission_id'];
    $score    = $_POST['score'];
    $feedback = htmlspecialchars($_POST['feedback']);

    // Validasi nilai 0-100
    if ($score < 0 || $score > 100) {
        echo "<script>alert('Nilai harus antara 0 - 100');</script>";
    } else {
        $update = "UPDATE submissions SET score='$score', feedback='$feedback' WHERE id='$id_sub'";
        if (mysqli_query($koneksi, $update)) {
            echo "<script>alert('Nilai berhasil disimpan!'); window.location='penilaian.php?id=$id_sesi';</script>";
        }
    }
}
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            
            <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
                <div>
                    <h1 class="m-0">Panel Penilaian</h1>
                    <div class="text-muted"><?= $sesi['nama_kelas'] ?> - <?= $sesi['title'] ?></div>
                </div>
                <a href="pertemuan.php?id=<?= $sesi['course_id'] ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <?php if (!empty($sesi['deadline'])) { ?>
                <div class="alert alert-info d-flex align-items-center">
                    <i class="fas fa-clock fa-2x me-3"></i>
                    <div>
                        <strong>Deadline Pengumpulan:</strong><br>
                        <?= date('d F Y, H:i', strtotime($sesi['deadline'])) ?> WIB
                    </div>
                </div>
            <?php } ?>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-table me-1"></i> Daftar Tugas Masuk
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th width="5%">No</th>
                                    <th width="20%">Nama Siswa</th>
                                    <th width="15%">Waktu Kirim</th>
                                    <th width="10%">File</th>
                                    <th width="10%">Nilai (0-100)</th>
                                    <th width="25%">Feedback / Komentar</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Query: Ambil data submission join user
                                $q_sub = mysqli_query($koneksi, "SELECT s.*, u.name 
                                                                 FROM submissions s 
                                                                 JOIN users u ON s.student_id = u.id 
                                                                 WHERE s.session_id = '$id_sesi' 
                                                                 ORDER BY s.submitted_at DESC");
                                
                                if (mysqli_num_rows($q_sub) > 0) {
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($q_sub)) {
                                        
                                        // Cek Terlambat atau Tidak
                                        $is_late = false;
                                        if (!empty($sesi['deadline']) && $row['submitted_at'] > $sesi['deadline']) {
                                            $is_late = true;
                                        }
                                ?>
                                <form method="POST">
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td>
                                            <strong><?= $row['name'] ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <?= date('d/m/y H:i', strtotime($row['submitted_at'])) ?><br>
                                            <?php if ($is_late) { ?>
                                                <span class="badge bg-danger">Terlambat</span>
                                            <?php } else { ?>
                                                <span class="badge bg-success">Tepat Waktu</span>
                                            <?php } ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= $row['link_tugas'] ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-download"></i> Buka
                                            </a>
                                        </td>
                                        
                                        <td>
                                            <input type="hidden" name="submission_id" value="<?= $row['id'] ?>">
                                            <input type="number" name="score" class="form-control text-center fw-bold" 
                                                   value="<?= $row['score'] ?>" min="0" max="100" required>
                                        </td>
                                        
                                        <td>
                                            <textarea name="feedback" class="form-control form-control-sm" rows="2" 
                                                      placeholder="Beri catatan..."><?= $row['feedback'] ?></textarea>
                                        </td>
                                        
                                        <td class="text-center">
                                            <button type="submit" name="simpan_nilai" class="btn btn-primary btn-sm">
                                                <i class="fas fa-save"></i> Simpan
                                            </button>
                                        </td>
                                    </tr>
                                </form>
                                <?php 
                                    } 
                                } else {
                                    echo "<tr><td colspan='7' class='text-center py-4 text-muted'>Belum ada siswa yang mengumpulkan tugas.</td></tr>";
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