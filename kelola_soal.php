<?php
require "koneksi.php";
require "atas.php";

$role = $_SESSION['role'];
$id_user = $_SESSION['id_user'];

// 1. Validasi ID Ujian
if (!isset($_GET['ujian_id'])) {
    echo "<script>alert('ID Ujian tidak valid!'); window.location='index.php';</script>";
    exit;
}
$ujian_id = $_GET['ujian_id'];

// 2. Ambil Data Ujian & Kelas
$q_ujian = mysqli_query($koneksi, "SELECT u.*, c.name as nama_kelas, c.teacher_id 
                                   FROM ujian u 
                                   JOIN courses c ON u.course_id = c.id 
                                   WHERE u.id = '$ujian_id'");
$ujian = mysqli_fetch_assoc($q_ujian);

if (!$ujian) {
    die("Data ujian tidak ditemukan.");
}

// 3. Proteksi Keamanan
if ($role == 'student') {
    echo "<script>alert('Akses ditolak!'); window.location='index.php';</script>"; exit;
}
if ($role == 'teacher' && $ujian['teacher_id'] != $id_user) {
    echo "<script>alert('Anda tidak berhak mengelola soal di kelas ini!'); window.location='index.php';</script>"; exit;
}

// 4. Proses Hapus Soal
if (isset($_GET['hapus'])) {
    $id_soal = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM soal_ujian WHERE id='$id_soal' AND ujian_id='$ujian_id'");
    echo "<script>alert('Soal berhasil dihapus!'); window.location='kelola_soal.php?ujian_id=$ujian_id';</script>";
}

// 5. Proses Tambah Soal Baru
if (isset($_POST['simpan_soal'])) {
    $pertanyaan = htmlspecialchars($_POST['pertanyaan']);
    $opsi_a = htmlspecialchars($_POST['opsi_a']);
    $opsi_b = htmlspecialchars($_POST['opsi_b']);
    $opsi_c = htmlspecialchars($_POST['opsi_c']);
    $opsi_d = htmlspecialchars($_POST['opsi_d']);
    $kunci = $_POST['kunci_jawaban'];

    $q_insert = "INSERT INTO soal_ujian (ujian_id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, kunci_jawaban) 
                 VALUES ('$ujian_id', '$pertanyaan', '$opsi_a', '$opsi_b', '$opsi_c', '$opsi_d', '$kunci')";
    
    if (mysqli_query($koneksi, $q_insert)) {
        echo "<script>alert('Soal berhasil ditambahkan!'); window.location='kelola_soal.php?ujian_id=$ujian_id';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            
            <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
                <div>
                    <h2 class="m-0">Kelola Bank Soal</h2>
                    <div class="text-muted"><?= $ujian['nama_kelas'] ?> - <?= $ujian['judul'] ?></div>
                </div>
                <a href="kelas.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Selesai & Kembali
                </a>
            </div>

            <div class="alert alert-info border-info d-flex align-items-center mb-4">
                <i class="fas fa-info-circle fa-2x me-3"></i>
                <div>
                    <strong>Tanggal Ujian:</strong> <?= date('d M Y, H:i', strtotime($ujian['tanggal_ujian'])) ?> WIB <br>
                    <strong>Durasi:</strong> <?= $ujian['durasi'] ?> Menit
                </div>
            </div>

            <div class="row">
                <div class="col-lg-5">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-danger text-white fw-bold">
                            <i class="fas fa-plus-circle"></i> Tambah Soal Baru
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pertanyaan <span class="text-danger">*</span></label>
                                    <textarea name="pertanyaan" class="form-control" rows="3" required placeholder="Ketik pertanyaan di sini..."></textarea>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label text-muted small mb-1">Pilihan A</label>
                                    <input type="text" name="opsi_a" class="form-control form-control-sm" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-muted small mb-1">Pilihan B</label>
                                    <input type="text" name="opsi_b" class="form-control form-control-sm" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-muted small mb-1">Pilihan C</label>
                                    <input type="text" name="opsi_c" class="form-control form-control-sm" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Pilihan D</label>
                                    <input type="text" name="opsi_d" class="form-control form-control-sm" required>
                                </div>

                                <div class="mb-3 p-2 bg-light border rounded">
                                    <label class="form-label fw-bold text-success">Kunci Jawaban Benar <span class="text-danger">*</span></label>
                                    <select name="kunci_jawaban" class="form-select border-success" required>
                                        <option value="">-- Pilih Kunci Jawaban --</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                    </select>
                                </div>

                                <button type="submit" name="simpan_soal" class="btn btn-primary w-100">
                                    <i class="fas fa-save"></i> Simpan Soal
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-list"></i> Daftar Soal Tersimpan</span>
                            <?php
                            $q_hitung = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM soal_ujian WHERE ujian_id='$ujian_id'");
                            $total_soal = mysqli_fetch_assoc($q_hitung)['total'];
                            ?>
                            <span class="badge bg-danger rounded-pill"><?= $total_soal ?> Soal</span>
                        </div>
                        <div class="card-body p-0">
                            
                            <ul class="list-group list-group-flush">
                                <?php
                                $q_soal = mysqli_query($koneksi, "SELECT * FROM soal_ujian WHERE ujian_id='$ujian_id' ORDER BY id ASC");
                                
                                if (mysqli_num_rows($q_soal) > 0) {
                                    $no = 1;
                                    while ($soal = mysqli_fetch_assoc($q_soal)) {
                                ?>
                                    <li class="list-group-item p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <strong><?= $no++ ?>. <?= nl2br($soal['pertanyaan']) ?></strong>
                                            
                                            <a href="kelola_soal.php?ujian_id=<?= $ujian_id ?>&hapus=<?= $soal['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Yakin ingin menghapus soal ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                        
                                        <ol type="A" class="mb-0 text-muted small ps-3">
                                            <li class="<?= $soal['kunci_jawaban'] == 'A' ? 'text-success fw-bold' : '' ?>"><?= $soal['opsi_a'] ?> <?= $soal['kunci_jawaban'] == 'A' ? '<i class="fas fa-check-circle"></i>' : '' ?></li>
                                            <li class="<?= $soal['kunci_jawaban'] == 'B' ? 'text-success fw-bold' : '' ?>"><?= $soal['opsi_b'] ?> <?= $soal['kunci_jawaban'] == 'B' ? '<i class="fas fa-check-circle"></i>' : '' ?></li>
                                            <li class="<?= $soal['kunci_jawaban'] == 'C' ? 'text-success fw-bold' : '' ?>"><?= $soal['opsi_c'] ?> <?= $soal['kunci_jawaban'] == 'C' ? '<i class="fas fa-check-circle"></i>' : '' ?></li>
                                            <li class="<?= $soal['kunci_jawaban'] == 'D' ? 'text-success fw-bold' : '' ?>"><?= $soal['opsi_d'] ?> <?= $soal['kunci_jawaban'] == 'D' ? '<i class="fas fa-check-circle"></i>' : '' ?></li>
                                        </ol>
                                    </li>
                                <?php
                                    }
                                } else {
                                    echo "<div class='p-5 text-center text-muted'>Belum ada soal. Silakan input soal pada form di sebelah kiri.</div>";
                                }
                                ?>
                            </ul>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

<?php require "bawah.php"; ?>