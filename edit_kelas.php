<?php
require "koneksi.php";
require "atas.php";

$role = $_SESSION['role'];
$id_user = $_SESSION['id_user'];

if (!isset($_GET['id'])) {
    echo "<script>window.location='kelas.php';</script>";
    exit;
}

$id_kelas = $_GET['id'];

// 1. Ambil Data Kelas Saat Ini
$q_kelas = mysqli_query($koneksi, "SELECT * FROM courses WHERE id = '$id_kelas'");
$kelas = mysqli_fetch_assoc($q_kelas);

if (!$kelas) {
    die("Data kelas tidak ditemukan.");
}

// 2. Proteksi Keamanan Ganda
if ($role == 'student') {
    echo "<script>alert('Siswa tidak dapat mengakses halaman ini!'); window.location='index.php';</script>";
    exit;
}
// Jika Guru, pastikan dia adalah pemilik kelas tersebut
if ($role == 'teacher' && $kelas['teacher_id'] != $id_user) {
    echo "<script>alert('Akses ditolak! Anda hanya bisa mengedit kelas yang Anda ajar.'); window.location='kelas.php';</script>";
    exit;
}

// 3. Proses Update Data
if (isset($_POST['update_kelas'])) {
    $nama_kelas = htmlspecialchars($_POST['name']);
    $kategori = htmlspecialchars($_POST['category']);
    $start_date = $_POST['start_date'];
    
    // Jika Admin, ambil teacher_id dari dropdown. Jika Guru, gunakan ID dia sendiri.
    $teacher_id = ($role == 'admin') ? $_POST['teacher_id'] : $kelas['teacher_id'];

    $query_update = "UPDATE courses SET 
                        name = '$nama_kelas', 
                        category = '$kategori', 
                        start_date = '$start_date', 
                        teacher_id = '$teacher_id' 
                     WHERE id = '$id_kelas'";

    if (mysqli_query($koneksi, $query_update)) {
        echo "<script>alert('Data kelas berhasil diperbarui!'); window.location.href='kelas.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui kelas: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Edit Kelas Pembelajaran</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="kelas.php">Kelas</a></li>
                <li class="breadcrumb-item active">Edit Kelas</li>
            </ol>

            <div class="card shadow-lg mb-4 border-0">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="fas fa-edit me-1"></i> Form Edit Kelas
                </div>
                <div class="card-body bg-light">
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Kelas / Mata Pelajaran</label>
                            <input type="text" name="name" class="form-control" value="<?= $kelas['name'] ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Kategori / Jurusan</label>
                                <input type="text" name="category" class="form-control" value="<?= $kelas['category'] ?>" placeholder="Contoh: IT, Bahasa, IPA" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tanggal Mulai</label>
                                <input type="date" name="start_date" class="form-control" value="<?= $kelas['start_date'] ?>" required>
                            </div>
                        </div>

                        <?php if ($role == 'admin') { ?>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-primary">Ubah Pengajar (Khusus Admin)</label>
                            <select name="teacher_id" class="form-select border-primary">
                                <?php
                                $q_guru = mysqli_query($koneksi, "SELECT id, name FROM users WHERE role='teacher' ORDER BY name ASC");
                                while ($guru = mysqli_fetch_assoc($q_guru)) {
                                    $selected = ($guru['id'] == $kelas['teacher_id']) ? "selected" : "";
                                    echo "<option value='{$guru['id']}' $selected>{$guru['name']}</option>";
                                }
                                ?>
                            </select>
                            <div class="form-text small">Pilih nama pengajar baru jika Anda ingin mengalihkan hak akses kelas ini ke guru lain.</div>
                        </div>
                        <?php } ?>

                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="kelas.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" name="update_kelas" class="btn btn-warning fw-bold text-dark shadow-sm">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </main>

<?php require "bawah.php"; ?>