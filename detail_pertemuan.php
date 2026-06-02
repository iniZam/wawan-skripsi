<?php
require "koneksi.php";
require "atas.php";
require "upload.php"; // Memanggil helper upload sesuai formatmu

if (!isset($_GET['id'])) {
    echo "<script>window.location='kelas.php';</script>";
    exit;
}

$id_sesi = $_GET['id'];
$role = $_SESSION['role'];
$my_id = $_SESSION['id_user']; // Pastikan session ID kamu menggunakan 'id_user'

// 1. Ambil Data Sesi & Kelas
$q_sesi = mysqli_query($koneksi, "SELECT * FROM sessions WHERE id='$id_sesi'");
$sesi   = mysqli_fetch_assoc($q_sesi);

if (!$sesi) {
    die("Data pertemuan tidak ditemukan.");
}

$id_kelas = $sesi['course_id'];

// --- LOGIKA SISWA MENGUMPULKAN TUGAS (UPLOAD FILE) ---
if (isset($_POST['kumpul_tugas']) && $role == 'student') {
    // 'file_tugas' adalah name dari input type file di form bawah
    $link_lokal = uploadFileLokal('file_tugas'); 

    if ($link_lokal) {
        // Cek apakah siswa ini sudah pernah mengumpulkan
        $cek_submit = mysqli_query($koneksi, "SELECT * FROM submissions WHERE session_id='$id_sesi' AND student_id='$my_id'");
        
        if (mysqli_num_rows($cek_submit) > 0) {
            // Update (Revisi Tugas)
            $query_submit = "UPDATE submissions SET link_tugas='$link_lokal', submitted_at=NOW() 
                             WHERE session_id='$id_sesi' AND student_id='$my_id'";
            $pesan = "File tugas berhasil diperbarui!";
        } else {
            // Insert (Baru)
            $query_submit = "INSERT INTO submissions (session_id, student_id, link_tugas) 
                             VALUES ('$id_sesi', '$my_id', '$link_lokal')";
            $pesan = "File tugas berhasil dikirim!";
        }

        if (mysqli_query($koneksi, $query_submit)) {
            echo "<script>alert('$pesan'); window.location.href='detail_pertemuan.php?id=$id_sesi';</script>";
        }
    } else {
        echo "<script>alert('Gagal mengupload file tugas. Periksa format/ukuran file.');</script>";
    }
}
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            
            <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
                <h2 class="m-0"><?= $sesi['title'] ?></h2>
                <a href="pertemuan.php?id=<?= $id_kelas ?>" class="btn btn-secondary btn-sm shadow-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    
                    <?php if (!empty($sesi['link_video'])) { ?>
                        <div class="card mb-4 shadow-sm border-0">
                            <div class="card-header bg-dark text-white"><i class="fab fa-youtube"></i> Video Pembelajaran</div>
                            <div class="card-body p-0">
                                <?php
                                $video_url = $sesi['link_video'];
                                if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                                    $video_embed = str_replace("watch?v=", "embed/", $video_url);
                                    if(strpos($video_embed, 'youtu.be') !== false) {
                                        $id_yt = substr($video_embed, strrpos($video_embed, '/') + 1);
                                        $video_embed = "https://www.youtube.com/embed/" . $id_yt;
                                    }
                                    echo '<div class="ratio ratio-16x9"><iframe src="'.$video_embed.'" allowfullscreen></iframe></div>';
                                } else {
                                    echo '<div class="p-5 text-center"><a href="'.$video_url.'" target="_blank" class="btn btn-primary btn-lg"><i class="fas fa-play"></i> Tonton Video Materi</a></div>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-header bg-white fw-bold">
                            <i class="fas fa-book-open text-primary"></i> Materi Pembelajaran
                        </div>
                        <div class="card-body">
                            <p class="card-text fs-6" style="line-height: 1.8;"><?= nl2br($sesi['description']) ?></p>
                            
                            <?php if (!empty($sesi['link_materi'])) { ?>
                                <hr>
                                <h6 class="text-muted">Bahan Bacaan Lampiran:</h6>
                                <a href="<?= $sesi['link_materi'] ?>" target="_blank" class="btn btn-outline-primary">
                                    <i class="fas fa-cloud-download-alt"></i> Download File Materi
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    
                    <div class="card shadow-sm border-success mb-4">
                        <div class="card-header bg-success text-white fw-bold">
                            <i class="fas fa-user-check"></i> Status Kehadiran
                        </div>
                        <div class="card-body text-center">
                            
                            <?php if ($role == 'student') { 
                                // TAMPILAN SISWA
                                $cek_hadir = mysqli_query($koneksi, "SELECT waktu_hadir FROM attendance WHERE session_id='$id_sesi' AND student_id='$my_id'");
                                if (mysqli_num_rows($cek_hadir) > 0) {
                                    $data_hadir = mysqli_fetch_assoc($cek_hadir);
                            ?>
                                    <i class="fas fa-check-circle text-success fa-3x mb-2"></i>
                                    <h5 class="text-success fw-bold">Anda Hadir</h5>
                                    <small class="text-muted">Telah dicatat oleh pengajar pada:<br><?= date('d M Y, H:i', strtotime($data_hadir['waktu_hadir'])) ?></small>
                            <?php } else { ?>
                                    <i class="fas fa-times-circle text-secondary fa-3x mb-2"></i>
                                    <h5 class="text-secondary fw-bold">Belum Diabsen</h5>
                                    <p class="small text-muted mb-0">Pengajar belum mencatat kehadiran Anda pada pertemuan ini.</p>
                            <?php } ?>
                            
                            <?php } else { 
                                // TAMPILAN GURU / ADMIN
                                $q_total_absen = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM attendance WHERE session_id='$id_sesi'");
                                $total_absen = mysqli_fetch_assoc($q_total_absen)['total'];
                                
                                $q_total_siswa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM enrollments WHERE course_id='$id_kelas'");
                                $total_siswa = mysqli_fetch_assoc($q_total_siswa)['total'];
                            ?>
                                <h3 class="fw-bold text-success mb-0"><?= $total_absen ?> <span class="fs-6 text-muted">/ <?= $total_siswa ?> Siswa</span></h3>
                                <span class="text-muted d-block mb-3">Telah diabsen Hadir</span>
                                
                                <a href="absensi.php?id=<?= $id_sesi ?>" class="btn btn-success w-100 fw-bold py-2 shadow-sm">
                                    <i class="fas fa-clipboard-list me-1"></i> Kelola Kehadiran Siswa
                                </a>
                            <?php } ?>

                        </div>
                    </div>

                    <div class="card shadow-sm border-primary mb-4">
                        <div class="card-header bg-primary text-white fw-bold">
                            <i class="fas fa-file-upload"></i> Pengumpulan Tugas
                        </div>
                        <div class="card-body">
                            
                            <div class="alert alert-light border small">
                                <strong>Soal/Instruksi:</strong><br>
                                <?= nl2br($sesi['assignment_info']) ?>
                            </div>

                            <?php if (!empty($sesi['deadline'])) { ?>
                                <div class="badge bg-danger mb-3 w-100 p-2">
                                    <i class="fas fa-clock"></i> Deadline: <?= date('d M Y, H:i', strtotime($sesi['deadline'])) ?>
                                </div>
                            <?php } ?>

                            <?php if ($role == 'student') { 
                                $q_me = mysqli_query($koneksi, "SELECT * FROM submissions WHERE session_id='$id_sesi' AND student_id='$my_id'");
                                $data_tugas = mysqli_fetch_assoc($q_me);
                                $sudah_kumpul = ($data_tugas) ? true : false;
                            ?>
                                <hr>
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small">Upload File Tugas</label>
                                        <input type="file" name="file_tugas" class="form-control form-control-sm" required>
                                    </div>
                                    
                                    <button type="submit" name="kumpul_tugas" class="btn btn-primary btn-sm w-100 shadow-sm">
                                        <i class="fas fa-paper-plane"></i> <?= $sudah_kumpul ? 'Upload Ulang (Revisi)' : 'Kirim Tugas' ?>
                                    </button>
                                </form>

                                <?php if ($sudah_kumpul) { ?>
                                    <div class="mt-3 p-2 bg-light border rounded text-center">
                                        <i class="fas fa-check-circle text-success fa-2x mb-1"></i><br>
                                        <strong class="text-success">Tugas Terkirim!</strong><br>
                                        <small class="text-muted d-block mb-2"><?= date('d M Y H:i', strtotime($data_tugas['submitted_at'])) ?></small>
                                        <a href="<?= $data_tugas['link_tugas'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary w-100">Buka File Saya</a>

                                        <?php if ($data_tugas['score'] > 0) { ?>
                                            <hr class="my-2">
                                            <h2 class="text-primary mb-0"><?= $data_tugas['score'] ?></h2>
                                            <span class="badge bg-primary">Nilai Anda</span>
                                            <?php if($data_tugas['feedback']) { ?>
                                                <div class="alert alert-info mt-2 small text-start"><?= $data_tugas['feedback'] ?></div>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <div class="mt-2 badge bg-secondary w-100 py-2">Menunggu Penilaian Guru</div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>

                            <?php } else { ?>
                                <hr>
                                <h6 class="text-muted small">Progress Pengumpulan:</h6>
                                <?php
                                $q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM submissions WHERE session_id='$id_sesi'");
                                $total_subs = mysqli_fetch_assoc($q_total)['total'];
                                ?>
                                <h3 class="text-center mb-3 text-primary"><?= $total_subs ?> <span class="fs-6 text-muted">Siswa Mengumpulkan</span></h3>
                                <div class="d-grid gap-2">
                                    <a href="penilaian.php?id=<?= $id_sesi ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-clipboard-check"></i> Buka Panel Penilaian
                                    </a>
                                </div>
                            <?php } ?>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>
    <?php require "bawah.php"; ?>