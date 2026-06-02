<?php
require "koneksi.php";
require "atas.php";
require "upload.php"; // Wajib load helper untuk upload file

$id_kelas = $_GET['id'];

// Ambil Info Kelas
$q_kelas = mysqli_query($koneksi, "SELECT * FROM courses WHERE id='$id_kelas'");
$kelas = mysqli_fetch_assoc($q_kelas);

// --- PROSES SIMPAN PERTEMUAN BARU (PHP) ---
if (isset($_POST['simpan_pertemuan'])) {
    // Hanya Guru/Admin yang boleh simpan
    if ($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin') {
        
        $title = htmlspecialchars($_POST['title']);
        $desc  = htmlspecialchars($_POST['description']);
        $tugas = htmlspecialchars($_POST['assignment_info']);
        $deadline = $_POST['deadline'];

        // 1. Upload Materi (Prioritas File > Link Manual)
        $final_materi = htmlspecialchars($_POST['link_materi_manual']); 
        if (!empty($_FILES['file_materi']['name'])) {
            $url = uploadFileLokal('file_materi');
            if ($url) $final_materi = $url;
        }

        // 2. Upload Video (Prioritas File > Link YouTube)
        $final_video = htmlspecialchars($_POST['link_video_youtube']);
        if (!empty($_FILES['file_video']['name'])) {
            $url = uploadFileLokal('file_video');
            if ($url) $final_video = $url;
        }

        $sql = "INSERT INTO sessions (course_id, title, description, link_materi, link_video, assignment_info, deadline)
                VALUES ('$id_kelas', '$title', '$desc', '$final_materi', '$final_video', '$tugas', '$deadline')";
        
        if(mysqli_query($koneksi, $sql)){
            echo "<script>alert('Pertemuan Berhasil Disimpan!'); window.location.href='pertemuan.php?id=$id_kelas';</script>";
        } else {
            echo "<script>alert('Gagal: ".mysqli_error($koneksi)."');</script>";
        }
    }
}
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            
            <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
                <div>
                    <h1 class="m-0"><?= $kelas['name'] ?></h1>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="kelas.php">Kelas</a></li>
                        <li class="breadcrumb-item active">Daftar Pertemuan</li>
                    </ol>
                </div>

                <?php if($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin') { ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="fas fa-plus"></i> Tambah Pertemuan
                    </button>
                <?php } ?>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <?php
                    $q_sesi = mysqli_query($koneksi, "SELECT * FROM sessions WHERE course_id='$id_kelas' ORDER BY id ASC");
                    
                    if(mysqli_num_rows($q_sesi) > 0) {
                        $no = 1;
                        while($sesi = mysqli_fetch_assoc($q_sesi)) {
                    ?>
                        <div class="card mb-3 border-start-lg border-start-primary shadow-sm" style="border-left: 5px solid #0d6efd;">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="fw-bold mb-1">Pertemuan <?= $no++ ?>: <?= $sesi['title'] ?></h5>
                                        <p class="text-muted small mb-0"><?= substr($sesi['description'], 0, 100) ?>...</p>
                                    </div>
                                    <div class="text-end">
                                        <?php if(!empty($sesi['deadline'])) { ?>
                                            <span class="badge bg-danger mb-2 d-block">Deadline: <?= date('d M, H:i', strtotime($sesi['deadline'])) ?></span>
                                        <?php } ?>
                                        <div class="d-flex gap-2">
                                            <a href="detail_pertemuan.php?id=<?= $sesi['id'] ?>" class="btn btn-primary">
                                                <i class="fas fa-door-open"></i> Masuk
                                            </a>
                                                                                
                                            <?php if($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin') { ?>
                                                <a href="edit_pertemuan.php?id=<?= $sesi['id'] ?>" class="btn btn-warning text-dark" title="Edit Pertemuan">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        }
                    } else {
                        echo "<div class='alert alert-info text-center'>Belum ada pertemuan yang dibuat.</div>";
                    }
                    ?>
                </div>
            </div>
            <div class="card mb-4 shadow-sm border-danger mt-4">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold"><i class="fas fa-laptop-code"></i> Ujian Pilihan Ganda (UTS/UAS)</h5>
                    
                    <?php if($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin') { ?>
                        <a href="tambah_ujian.php" class="btn btn-light btn-sm text-danger fw-bold">
                            <i class="fas fa-plus"></i> Jadwalkan Ujian
                        </a>
                    <?php } ?>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php
                        // Ambil daftar ujian khusus untuk kelas ini
                        $q_ujian_list = mysqli_query($koneksi, "SELECT * FROM ujian WHERE course_id='$id_kelas' ORDER BY tanggal_ujian ASC");
                        
                        if (mysqli_num_rows($q_ujian_list) > 0) {
                            while ($uj = mysqli_fetch_assoc($q_ujian_list)) {
                                // Cek status ujian (Terbuka atau Belum Mulai)
                                $waktu_ujian = strtotime($uj['tanggal_ujian']);
                                $waktu_sekarang = time();
                                
                                if ($waktu_sekarang >= $waktu_ujian) {
                                    $badge_status = '<span class="badge bg-success ms-2">Terbuka</span>';
                                } else {
                                    $badge_status = '<span class="badge bg-warning text-dark ms-2">Belum Mulai</span>';
                                }
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <div>
                                <h6 class="mb-1 fw-bold"><?= $uj['judul'] ?> <?= $badge_status ?></h6>
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt text-primary"></i> <?= date('d M Y, H:i', $waktu_ujian) ?> WIB &nbsp;|&nbsp; 
                                    <i class="fas fa-stopwatch text-danger"></i> <?= $uj['durasi'] ?> Menit
                                </small>
                            </div>
                            <div>
                                <?php if ($_SESSION['role'] == 'student') { ?>
                                    <a href="ujian.php?id=<?= $uj['id'] ?>" class="btn btn-primary btn-sm px-3">
                                        <i class="fas fa-pen"></i> Masuk Ujian
                                    </a>
                                <?php } else { ?>
                                    <a href="ujian.php?id=<?= $uj['id'] ?>" class="btn btn-outline-danger btn-sm px-3">
                                        <i class="fas fa-cog"></i> Kelola / Lihat Nilai
                                    </a>
                                <?php } ?>
                            </div>
                        </li>
                        <?php 
                            }
                        } else {
                            echo "<li class='list-group-item text-center text-muted p-4'>Belum ada jadwal ujian untuk kelas ini.</li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
            
        </div>
    </main>
    

    <?php if($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'admin') { ?>
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-plus-circle"></i> Buat Pertemuan Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Judul Pertemuan</label>
                            <input type="text" name="title" class="form-control" required placeholder="Contoh: Pertemuan 1 - Pengenalan Database">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Singkat</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Apa yang akan dipelajari hari ini?"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-primary fw-bold"><i class="fas fa-file-pdf"></i> Materi (File)</label>
                                <input type="file" name="file_materi" class="form-control mb-2">
                                <input type="text" name="link_materi_manual" class="form-control form-control-sm" placeholder="Atau Link Manual (G-Drive)...">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label text-danger fw-bold"><i class="fab fa-youtube"></i> Video</label>
                                <input type="file" name="file_video" class="form-control mb-2" accept="video/*">
                                <input type="text" name="link_video_youtube" class="form-control form-control-sm" placeholder="Atau Link YouTube...">
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Instruksi Tugas</label>
                            <textarea name="assignment_info" class="form-control" rows="3" placeholder="Jelaskan tugas yang harus dikerjakan siswa..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-danger">Batas Waktu Pengumpulan (Deadline)</label>
                            <input type="datetime-local" name="deadline" class="form-control">
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="simpan_pertemuan" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

<?php require "bawah.php"; ?>