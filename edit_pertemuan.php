<?php
require "koneksi.php";
require "atas.php";
require "upload.php"; // WAJIB: Untuk upload file baru

// Ambil ID Sesi dari URL
$id_sesi = $_GET['id'];

// Ambil Data Sesi + Data Kelas (untuk cek teacher_id)
$query = "SELECT s.*, c.teacher_id, c.id as id_kelas 
          FROM sessions s 
          JOIN courses c ON s.course_id = c.id 
          WHERE s.id = '$id_sesi'";
$result = mysqli_query($koneksi, $query);
$data   = mysqli_fetch_assoc($result);

// --- PROTEKSI KEAMANAN ---
// 1. Cek apakah data ada?
if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='kelas.php';</script>";
    exit;
}
// 2. Cek Hak Akses (Hanya Admin atau Guru Pemilik Kelas)
if ($_SESSION['role'] == 'student') {
    echo "<script>alert('Siswa tidak boleh masuk sini!'); window.location='index.php';</script>";
    exit;
}
if ($_SESSION['role'] == 'teacher' && $data['teacher_id'] != $_SESSION['id_user']) {
    echo "<script>alert('Anda tidak berhak mengedit pertemuan orang lain!'); window.location='kelas.php';</script>";
    exit;
}

// --- PROSES UPDATE DATA ---
if (isset($_POST['update'])) {
    $title = htmlspecialchars($_POST['title']);
    $desc  = htmlspecialchars($_POST['description']);
    $tugas = htmlspecialchars($_POST['assignment_info']);
    $deadline = $_POST['deadline'];

    // 1. LOGIKA UPDATE MATERI
    // Ambil link lama dulu sebagai default
    $final_materi = $data['link_materi']; 
    
    // Jika ada file baru diupload -> Upload ke Nextcloud & Timpa Link Lama
    if (!empty($_FILES['file_materi']['name'])) {
        $url = uploadFileLokal('file_materi');
        if ($url) $final_materi = $url;
    } 
    // Jika tidak upload file, tapi ada input link manual baru -> Timpa Link Lama
    else if (!empty($_POST['link_materi_manual'])) {
        $final_materi = htmlspecialchars($_POST['link_materi_manual']);
    }

    // 2. LOGIKA UPDATE VIDEO
    $final_video = $data['link_video'];
    
    if (!empty($_FILES['file_video']['name'])) {
        $url = uploadFileLokal('file_video');
        if ($url) $final_video = $url;
    } 
    else if (!empty($_POST['link_video_youtube'])) {
        $final_video = htmlspecialchars($_POST['link_video_youtube']);
    }

    // QUERY UPDATE
    $sql_update = "UPDATE sessions SET 
                   title='$title', 
                   description='$desc', 
                   link_materi='$final_materi', 
                   link_video='$final_video', 
                   assignment_info='$tugas', 
                   deadline='$deadline' 
                   WHERE id='$id_sesi'";

    if (mysqli_query($koneksi, $sql_update)) {
        echo "<script>alert('Perubahan Berhasil Disimpan!'); window.location.href='pertemuan.php?id=" . $data['id_kelas'] . "';</script>";
    } else {
        echo "<script>alert('Gagal Update: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Edit Pertemuan</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="pertemuan.php?id=<?= $data['id_kelas'] ?>">Daftar Pertemuan</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>

            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-edit me-1"></i> Form Edit Pertemuan
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Judul Pertemuan</label>
                            <input type="text" name="title" class="form-control" value="<?= $data['title'] ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3"><?= $data['description'] ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-primary">Materi (File/Link)</label>
                                <div class="p-2 border rounded bg-light mb-2">
                                    <small class="text-muted">Link Saat Ini:</small><br>
                                    <a href="<?= $data['link_materi'] ?>" target="_blank" class="small text-break"><?= $data['link_materi'] ?: '-' ?></a>
                                </div>
                                <label class="small text-muted">Ganti File (Nextcloud):</label>
                                <input type="file" name="file_materi" class="form-control mb-1">
                                <input type="text" name="link_materi_manual" class="form-control form-control-sm" placeholder="Atau ganti Link Manual...">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-danger">Video Pembelajaran</label>
                                <div class="p-2 border rounded bg-light mb-2">
                                    <small class="text-muted">Video Saat Ini:</small><br>
                                    <a href="<?= $data['link_video'] ?>" target="_blank" class="small text-break"><?= $data['link_video'] ?: '-' ?></a>
                                </div>
                                <label class="small text-muted">Ganti Video (Upload/YouTube):</label>
                                <input type="file" name="file_video" class="form-control mb-1" accept="video/*">
                                <input type="text" name="link_video_youtube" class="form-control form-control-sm" placeholder="Atau ganti Link YouTube...">
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Instruksi Tugas</label>
                            <textarea name="assignment_info" class="form-control" rows="3"><?= $data['assignment_info'] ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-danger">Deadline</label>
                            <input type="datetime-local" name="deadline" class="form-control" 
                                   value="<?= date('Y-m-d\TH:i', strtotime($data['deadline'])) ?>">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="update" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                            <a href="pertemuan.php?id=<?= $data['id_kelas'] ?>" class="btn btn-secondary">Batal</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </main>

<?php require "bawah.php"; ?>