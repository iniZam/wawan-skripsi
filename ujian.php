<?php
require "koneksi.php";
require "atas.php";

$role = $_SESSION['role'];
$my_id = $_SESSION['id_user'];

if (!isset($_GET['id'])) {
    echo "<script>alert('Ujian tidak valid!'); window.location='kelas.php';</script>"; exit;
}
$ujian_id = $_GET['id'];

// 1. Ambil Data Ujian & Info Kelas
$q_ujian = mysqli_query($koneksi, "SELECT u.*, c.name as nama_kelas, c.id as course_id 
                                   FROM ujian u 
                                   JOIN courses c ON u.course_id = c.id 
                                   WHERE u.id = '$ujian_id'");
$ujian = mysqli_fetch_assoc($q_ujian);
if (!$ujian) { die("Data ujian tidak ditemukan!"); }

// Ambil Total Soal
$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM soal_ujian WHERE ujian_id='$ujian_id'");
$total_soal = mysqli_fetch_assoc($q_total)['total'];

// Cek apakah Siswa sudah pernah mengerjakan
$sudah_mengerjakan = false;
$nilai_saya = null;
if ($role == 'student') {
    $q_cek = mysqli_query($koneksi, "SELECT * FROM hasil_ujian WHERE ujian_id='$ujian_id' AND student_id='$my_id'");
    if (mysqli_num_rows($q_cek) > 0) {
        $sudah_mengerjakan = true;
        $nilai_saya = mysqli_fetch_assoc($q_cek);
    }
}

// ====================================================
// PROSES AUTO-GRADING (KOREKSI OTOMATIS SAAT SISWA SUBMIT)
// ====================================================
if (isset($_POST['kumpulkan_ujian']) && $role == 'student' && !$sudah_mengerjakan) {
    $jawaban_siswa = $_POST['jawaban'] ?? []; // Array [id_soal => jawaban]
    $benar = 0;
    
    // Looping semua soal untuk dicocokkan dengan kunci
    $q_soal_cek = mysqli_query($koneksi, "SELECT id, kunci_jawaban FROM soal_ujian WHERE ujian_id='$ujian_id'");
    while ($soal = mysqli_fetch_assoc($q_soal_cek)) {
        $id_s = $soal['id'];
        $kunci = $soal['kunci_jawaban'];
        
        // Jika siswa menjawab soal ini dan jawabannya sama dengan kunci
        if (isset($jawaban_siswa[$id_s]) && $jawaban_siswa[$id_s] == $kunci) {
            $benar++;
        }
    }
    
    // Hitung Nilai (Skala 0-100)
    $nilai_akhir = ($total_soal > 0) ? ($benar / $total_soal) * 100 : 0;
    
    // Simpan ke database
    $q_simpan = "INSERT INTO hasil_ujian (ujian_id, student_id, jml_benar, jml_soal, nilai) 
                 VALUES ('$ujian_id', '$my_id', '$benar', '$total_soal', '$nilai_akhir')";
    
    if (mysqli_query($koneksi, $q_simpan)) {
        echo "<script>alert('Ujian berhasil dikumpulkan! Nilai Anda: $nilai_akhir'); window.location='ujian.php?id=$ujian_id';</script>";
    }
}
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            
            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
                <div>
                    <h2 class="m-0"><?= strtoupper($ujian['judul']) ?></h2>
                    <div class="text-muted">Kelas: <?= $ujian['nama_kelas'] ?></div>
                </div>
                <div>
                    <!-- TOMBOL NAVIGASI EDIT SOAL (KHUSUS GURU & ADMIN) -->
                    <?php if ($role == 'teacher' || $role == 'admin') { ?>
                        <a href="kelola_soal.php?ujian_id=<?= $ujian_id ?>" class="btn btn-warning me-2 text-dark fw-bold shadow-sm">
                            <i class="fas fa-edit"></i> Edit Soal Ujian
                        </a>
                    <?php } ?>
                    <a href="pertemuan.php?id=<?= $ujian['course_id'] ?>" class="btn btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left"></i> Kembali ke Kelas
                    </a>
                </div>
            </div>

            <!-- INFO UJIAN -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white text-center p-3 shadow-sm">
                        <h6><i class="fas fa-calendar-alt"></i> Tanggal</h6>
                        <h5 class="mb-0"><?= date('d M Y, H:i', strtotime($ujian['tanggal_ujian'])) ?></h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white text-center p-3 shadow-sm">
                        <h6><i class="fas fa-clock"></i> Durasi</h6>
                        <h5 class="mb-0"><?= $ujian['durasi'] ?> Menit</h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white text-center p-3 shadow-sm">
                        <h6><i class="fas fa-list-ol"></i> Total Soal</h6>
                        <h5 class="mb-0"><?= $total_soal ?> Butir</h5>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAMPILAN KHUSUS GURU/ADMIN (REKAP NILAI) -->
            <!-- ========================================== -->
            <?php if ($role == 'teacher' || $role == 'admin') { ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white fw-bold">
                        <i class="fas fa-users"></i> Rekap Nilai Siswa
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped text-center align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Nama Siswa</th>
                                        <th>Waktu Selesai</th>
                                        <th>Benar</th>
                                        <th>Nilai Akhir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $q_rekap = mysqli_query($koneksi, "SELECT h.*, u.name 
                                                                       FROM hasil_ujian h 
                                                                       JOIN users u ON h.student_id = u.id 
                                                                       WHERE h.ujian_id='$ujian_id' 
                                                                       ORDER BY h.nilai DESC");
                                    if (mysqli_num_rows($q_rekap) > 0) {
                                        $no = 1;
                                        while ($r = mysqli_fetch_assoc($q_rekap)) {
                                            echo "<tr>
                                                    <td>{$no}</td>
                                                    <td class='text-start'><strong>{$r['name']}</strong></td>
                                                    <td>" . date('d/m/Y H:i', strtotime($r['waktu_selesai'])) . "</td>
                                                    <td>{$r['jml_benar']} / {$r['jml_soal']}</td>
                                                    <td><h5 class='text-success fw-bold m-0'>{$r['nilai']}</h5></td>
                                                  </tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-muted py-4'>Belum ada siswa yang mengerjakan ujian ini.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php } ?>


            <!-- ========================================== -->
            <!-- TAMPILAN KHUSUS SISWA (LEMBAR SOAL) -->
            <!-- ========================================== -->
            <?php if ($role == 'student') { 
                
                // JIKA SISWA SUDAH MENGERJAKAN
                if ($sudah_mengerjakan) { ?>
                    <div class="alert alert-success text-center py-5 shadow-sm border-0">
                        <i class="fas fa-check-circle fa-4x mb-3 text-success"></i>
                        <h3>Ujian Selesai Dikerjakan!</h3>
                        <p class="mb-4">Anda telah mengumpulkan ujian ini pada <?= date('d M Y, H:i', strtotime($nilai_saya['waktu_selesai'])) ?></p>
                        
                        <div class="d-inline-block border rounded bg-white p-4 text-center mx-auto shadow-sm">
                            <h5 class="text-muted">Nilai Anda</h5>
                            <h1 class="display-3 fw-bold text-success m-0"><?= $nilai_saya['nilai'] ?></h1>
                            <hr>
                            <span class="text-muted">Jawaban Benar: <?= $nilai_saya['jml_benar'] ?> dari <?= $nilai_saya['jml_soal'] ?> soal</span>
                        </div>
                    </div>
                
                <?php } else { 
                    // JIKA SISWA BELUM MENGERJAKAN (Tampilkan Form Soal)
                    // Cek apakah waktu ujian sudah masuk?
                    $waktu_sekarang = date('Y-m-d H:i:s');
                    if ($waktu_sekarang < $ujian['tanggal_ujian']) {
                        echo "<div class='alert alert-warning text-center py-5'>
                                <i class='fas fa-lock fa-3x mb-3'></i>
                                <h4>Ujian Belum Dimulai</h4>
                                <p>Ujian ini baru bisa diakses pada <strong>" . date('d M Y, H:i', strtotime($ujian['tanggal_ujian'])) . " WIB</strong>.</p>
                              </div>";
                    } else if ($total_soal == 0) {
                        echo "<div class='alert alert-danger text-center'>Soal ujian belum dimasukkan oleh Guru.</div>";
                    } else {
                ?>
                    <div class="card shadow-sm border-primary mb-5">
                        <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-file-alt"></i> Lembar Soal Ujian</span>
                            <span class="badge bg-danger fs-6"><i class="fas fa-stopwatch"></i> Durasi: <?= $ujian['durasi'] ?> Menit</span>
                        </div>
                        <div class="card-body p-4 bg-light">
                            
                            <form method="POST" id="formUjian">
                                <?php
                                $q_soal = mysqli_query($koneksi, "SELECT * FROM soal_ujian WHERE ujian_id='$ujian_id' ORDER BY RAND()"); // Opsi RAND() agar soal diacak
                                $no_soal = 1;
                                while ($s = mysqli_fetch_assoc($q_soal)) {
                                ?>
                                    <div class="card mb-4 border-0 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="mb-3">
                                                <span class="badge bg-secondary me-2"><?= $no_soal ?></span> 
                                                <?= nl2br($s['pertanyaan']) ?>
                                            </h5>
                                            
                                            <!-- Pilihan Ganda -->
                                            <div class="ps-4">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" name="jawaban[<?= $s['id'] ?>]" value="A" id="a_<?= $s['id'] ?>" required>
                                                    <label class="form-check-label w-100 cursor-pointer" for="a_<?= $s['id'] ?>">A. <?= $s['opsi_a'] ?></label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" name="jawaban[<?= $s['id'] ?>]" value="B" id="b_<?= $s['id'] ?>">
                                                    <label class="form-check-label w-100 cursor-pointer" for="b_<?= $s['id'] ?>">B. <?= $s['opsi_b'] ?></label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" name="jawaban[<?= $s['id'] ?>]" value="C" id="c_<?= $s['id'] ?>">
                                                    <label class="form-check-label w-100 cursor-pointer" for="c_<?= $s['id'] ?>">C. <?= $s['opsi_c'] ?></label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" name="jawaban[<?= $s['id'] ?>]" value="D" id="d_<?= $s['id'] ?>">
                                                    <label class="form-check-label w-100 cursor-pointer" for="d_<?= $s['id'] ?>">D. <?= $s['opsi_d'] ?></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php 
                                    $no_soal++; 
                                } 
                                ?>

                                <div class="text-end">
                                    <button type="submit" name="kumpulkan_ujian" class="btn btn-success btn-lg px-5 shadow" onclick="return confirm('Yakin ingin mengumpulkan? Jawaban tidak dapat diubah lagi.')">
                                        <i class="fas fa-paper-plane"></i> Kumpulkan & Selesai
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                <?php 
                    } // end if waktu sudah masuk
                } // end if sudah mengerjakan
            } // end if role student 
            ?>

        </div>
    </main>

<?php require "bawah.php"; ?>