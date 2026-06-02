<?php
require "koneksi.php";
require "atas.php";

$role = $_SESSION['role'];
$id_user = $_SESSION['id_user'];

// Proteksi Keamanan: Hanya Admin & Guru yang boleh masuk
if ($role == 'student') {
    echo "<script>alert('Siswa tidak memiliki akses ke halaman ini!'); window.location='index.php';</script>";
    exit;
}

// Proses Simpan Data Ujian Baru
if (isset($_POST['simpan_ujian'])) {
    $course_id = $_POST['course_id'];
    $judul = htmlspecialchars($_POST['judul']);
    $tanggal_ujian = $_POST['tanggal_ujian'];
    $durasi = $_POST['durasi'];

    $query_insert = "INSERT INTO ujian (course_id, judul, tanggal_ujian, durasi) 
                     VALUES ('$course_id', '$judul', '$tanggal_ujian', '$durasi')";

    if (mysqli_query($koneksi, $query_insert)) {
        // Setelah ujian berhasil dibuat, arahkan ke halaman kelola soal
        $ujian_id = mysqli_insert_id($koneksi); // Ambil ID ujian yang baru saja dibuat
        echo "<script>alert('Jadwal Ujian berhasil dibuat! Silakan tambahkan soal.'); window.location.href='kelola_soal.php?ujian_id=$ujian_id';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan ujian: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Jadwalkan Ujian Baru</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Tambah Ujian Pilihan Ganda</li>
            </ol>

            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-calendar-plus me-1"></i> Form Pengaturan Ujian
                </div>
                <div class="card-body">
                    <form method="POST">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Kelas <span class="text-danger">*</span></label>
                            <select name="course_id" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php
                                // Logika Dropdown: Admin melihat semua kelas, Guru melihat kelasnya sendiri
                                if ($role == 'admin') {
                                    $q_kelas = mysqli_query($koneksi, "SELECT * FROM courses ORDER BY name ASC");
                                } else {
                                    $q_kelas = mysqli_query($koneksi, "SELECT * FROM courses WHERE teacher_id='$id_user' ORDER BY name ASC");
                                }

                                while ($kelas = mysqli_fetch_assoc($q_kelas)) {
                                    echo "<option value='{$kelas['id']}'>{$kelas['name']}</option>";
                                }
                                ?>
                            </select>
                            <?php if(mysqli_num_rows($q_kelas) == 0) { ?>
                                <div class="form-text text-danger">Anda belum memiliki kelas untuk dijadwalkan ujian.</div>
                            <?php } ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Judul Ujian <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required placeholder="Contoh: Ujian Akhir Semester (UAS) - Ganjil">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tanggal & Waktu Ujian <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="tanggal_ujian" class="form-control" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Durasi (Menit) <span class="text-danger">*</span></label>
                                <input type="number" name="durasi" class="form-control" required min="10" placeholder="Contoh: 90">
                                <div class="form-text">Berapa lama waktu siswa untuk mengerjakan soal?</div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                            <button type="submit" name="simpan_ujian" class="btn btn-danger">
                                <i class="fas fa-arrow-right"></i> Lanjut Buat Soal
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </main>

<?php require "bawah.php"; ?>