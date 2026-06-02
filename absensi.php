<?php
require "koneksi.php";
require "atas.php";

$role = $_SESSION['role'];
$id_user = $_SESSION['id_user'];

// 1. Validasi parameter ID Pertemuan/Sesi di URL
if (!isset($_GET['id'])) {
    echo "<script>alert('Data pertemuan tidak ditemukan!'); window.location='kelas.php';</script>";
    exit;
}

$id_sesi = $_GET['id'];

// 2. Ambil info sesi beserta detail kelas untuk memeriksa siapa guru pengajarnya
$q_sesi = mysqli_query($koneksi, "SELECT s.*, c.name as nama_kelas, c.id as id_kelas, c.teacher_id 
                                  FROM sessions s 
                                  JOIN courses c ON s.course_id = c.id 
                                  WHERE s.id = '$id_sesi'");
$sesi = mysqli_fetch_assoc($q_sesi);

if (!$sesi) { 
    die("Data pertemuan tidak ditemukan di database."); 
}

$id_kelas = $sesi['id_kelas'];
$teacher_id_kelas = $sesi['teacher_id'];

// =============================================================
// PROTEKSI KEAMANAN KETAT: VALIDASI GURU PENGAJAR
// =============================================================
if ($role == 'student') {
    echo "<script>alert('Akses Ditolak! Siswa tidak boleh mengisi absensi.'); window.location='index.php';</script>";
    exit;
}

if ($role == 'teacher' && $id_user != $teacher_id_kelas) {
    echo "<script>alert('Akses Ditolak! Anda bukan pengajar resmi di kelas ini.'); window.location='kelas.php';</script>";
    exit;
}
// =============================================================


// =============================================================
// PROSES SIMPAN DATA ABSENSI
// =============================================================
if (isset($_POST['simpan_absensi'])) {
    $hadir_list = $_POST['hadir'] ?? []; // Menampung array ID siswa yang dicentang hadir

    // 1. Reset data kehadiran lama khusus untuk pertemuan/sesi ini
    mysqli_query($koneksi, "DELETE FROM attendance WHERE session_id='$id_sesi'");

    // 2. Insert data kehadiran baru secara massal (Batch Insert) jika ada yang dicentang
    if (!empty($hadir_list)) {
        $query_insert = "INSERT INTO attendance (session_id, student_id, waktu_hadir) VALUES ";
        $values = [];
        foreach ($hadir_list as $sid) {
            $sid = mysqli_real_escape_string($koneksi, $sid);
            $values[] = "('$id_sesi', '$sid', NOW())";
        }
        $query_insert .= implode(", ", $values);
        mysqli_query($koneksi, $query_insert);
    }

    echo "<script>alert('Data absensi kelas berhasil diperbarui!'); window.location.href='detail_pertemuan.php?id=$id_sesi';</script>";
}
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            
            <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
                <div>
                    <h2 class="m-0"><i class="fas fa-user-check text-success"></i> Pencatatan Kehadiran</h2>
                    <div class="text-muted fw-bold">Kelas: <?= $sesi['nama_kelas'] ?> — <?= $sesi['title'] ?></div>
                </div>
                <a href="detail_pertemuan.php?id=<?= $id_sesi ?>" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white fw-bold d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span><i class="fas fa-users me-2"></i> Daftar Siswa Terdaftar di Kelas Ini</span>
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-warning btn-sm text-dark fw-bold shadow-sm" id="btnUjiSimulasi" title="Isi absensi acak untuk simulasi pengujian">
                            <i class="fas fa-flask"></i> Uji Otomatis (Simulasi)
                        </button>
                        
                        <button type="button" class="btn btn-light btn-sm text-success fw-bold shadow-sm" id="btnPilihSemua">
                            <i class="fas fa-check-double"></i> Centang Semua Hadir
                        </button>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <form method="POST" action="">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th width="15%" class="text-center">Status Kehadiran</th>
                                        <th>Nama Lengkap Siswa</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Memanggil seluruh data siswa yang sudah di-ploting masuk ke kelas ini melalui tabel enrollments
                                    $q_siswa = mysqli_query($koneksi, "
                                        SELECT u.id, u.name, u.email, 
                                        (SELECT COUNT(*) FROM attendance a WHERE a.session_id='$id_sesi' AND a.student_id=u.id) as is_hadir 
                                        FROM enrollments e 
                                        JOIN users u ON e.student_id = u.id 
                                        WHERE e.course_id = '$id_kelas' 
                                        ORDER BY u.name ASC
                                    ");

                                    $total_data_siswa = mysqli_num_rows($q_siswa);

                                    if ($total_data_siswa > 0) {
                                        $no = 1;
                                        while ($sw = mysqli_fetch_assoc($q_siswa)) {
                                            $checked = ($sw['is_hadir'] > 0) ? 'checked' : '';
                                    ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td class="text-center bg-light border-end">
                                                <input class="form-check-input fs-4 checkbox-hadir" type="checkbox" name="hadir[]" value="<?= $sw['id'] ?>" <?= $checked ?> style="cursor: pointer;">
                                            </td>
                                            <td>
                                                <strong class="nama-siswa-label <?= $checked ? 'text-success' : '' ?> fs-5"><?= $sw['name'] ?></strong>
                                            </td>
                                            <td class="text-muted small"><?= $sw['email'] ?></td>
                                        </tr>
                                    <?php 
                                        } 
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center py-5 text-muted'>
                                                <i class='fas fa-user-slash fa-2x mb-2 d-block'></i>
                                                Belum ada siswa yang didaftarkan ke dalam kelas ini oleh Administrator.
                                              </td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="p-3 bg-light border-top d-flex justify-content-end">
                            <?php if ($total_data_siswa > 0) { ?>
                                <button type="submit" name="simpan_absensi" class="btn btn-success btn-lg px-5 shadow-sm fw-bold">
                                    <i class="fas fa-save"></i> Simpan Presensi Kehadiran
                                </button>
                            <?php } ?>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>

    <script>
        // JS 1: Logika Centang Semua Hadir
        document.getElementById('btnPilihSemua').addEventListener('click', function() {
            let checkboxes = document.querySelectorAll('.checkbox-hadir');
            let labels = document.querySelectorAll('.nama-siswa-label');
            let allChecked = true;
            
            checkboxes.forEach(function(checkbox) {
                if (!checkbox.checked) allChecked = false;
            });

            checkboxes.forEach(function(checkbox, index) {
                checkbox.checked = !allChecked;
                if (!allChecked) {
                    labels[index].classList.add('text-success');
                } else {
                    labels[index].classList.remove('text-success');
                }
            });
            
            if (!allChecked) {
                this.innerHTML = '<i class="fas fa-times"></i> Batalkan Semua';
                this.classList.replace('text-success', 'text-danger');
            } else {
                this.innerHTML = '<i class="fas fa-check-double"></i> Centang Semua Hadir';
                this.classList.replace('text-danger', 'text-success');
            }
        });

        // JS 2: LOGIKA BARU - UJI OTOMATIS (SIMULASI DATA ACAK)
        document.getElementById('btnUjiSimulasi').addEventListener('click', function() {
            let checkboxes = document.querySelectorAll('.checkbox-hadir');
            let labels = document.querySelectorAll('.nama-siswa-label');
            
            checkboxes.forEach(function(checkbox, index) {
                // Menghasilkan nilai acak, memberikan peluang 75% siswa hadir (true)
                let isHadirRandom = Math.random() >= 0.25; 
                
                checkbox.checked = isHadirRandom;
                
                // Sinkronisasi warna teks nama siswa secara real-time
                if (isHadirRandom) {
                    labels[index].classList.add('text-success');
                } else {
                    labels[index].classList.remove('text-success');
                }
            });
            
            // Kembalikan status tombol "Centang Semua" ke kondisi default
            let btnPilihSemua = document.getElementById('btnPilihSemua');
            btnPilihSemua.innerHTML = '<i class="fas fa-check-double"></i> Centang Semua Hadir';
            btnPilihSemua.classList.remove('text-danger');
            btnPilihSemua.classList.add('text-success');
        });
    </script>

<?php require "bawah.php"; ?>