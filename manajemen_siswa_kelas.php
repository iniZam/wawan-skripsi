<?php
require "koneksi.php";
require "atas.php";

// Proteksi Keamanan: HANYA ADMIN yang boleh mengakses halaman ini
if ($_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses Ditolak! Halaman ini hanya untuk Administrator.'); window.location='index.php';</script>";
    exit;
}

// =============================================================
// PROSES DELETION (BATALKAN SISWA DARI KELAS)
// =============================================================
if (isset($_GET['hapus_enroll'])) {
    $id_enroll = $_GET['hapus_enroll'];
    $query_hapus = "DELETE FROM enrollments WHERE id = '$id_enroll'";
    
    if (mysqli_query($koneksi, $query_hapus)) {
        echo "<script>alert('Siswa berhasil dikeluarkan dari kelas!'); window.location.href='manajemen_siswa_kelas.php';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($koneksi) . "');</script>";
    }
}

// =============================================================
// PROSES INSERTION (PLOTING SISWA KE KELAS)
// =============================================================
if (isset($_POST['tambah_ke_kelas'])) {
    $student_id = $_POST['student_id'];
    $course_id  = $_POST['course_id'];

    // Validasi: Cek apakah siswa tersebut sudah terdaftar di kelas tersebut
    $cek_enroll = mysqli_query($koneksi, "SELECT * FROM enrollments WHERE course_id = '$course_id' AND student_id = '$student_id'");
    
    if (mysqli_num_rows($cek_enroll) > 0) {
        echo "<script>alert('Gagal! Siswa ini sudah terdaftar di kelas tersebut.');</script>";
    } else {
        // Masukkan ke tabel enrollments
        $query_insert = "INSERT INTO enrollments (course_id, student_id) VALUES ('$course_id', '$student_id')";
        if (mysqli_query($koneksi, $query_insert)) {
            echo "<script>alert('Siswa berhasil dimasukkan ke dalam kelas!'); window.location.href='manajemen_siswa_kelas.php';</script>";
        } else {
            echo "<script>alert('Gagal: " . mysqli_error($koneksi) . "');</script>";
        }
    }
}
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Manajemen Siswa & Kelas</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Ploting Kelas Siswa</li>
            </ol>

            <div class="row">
                <div class="col-xl-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white fw-bold">
                            <i class="fas fa-user-plus me-1"></i> Daftarkan Siswa ke Kelas
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pilih Siswa</label>
                                    <select name="student_id" class="form-select" required>
                                        <option value="" disabled selected>-- Pilih Siswa --</option>
                                        <?php
                                        // Ambil semua pengguna yang rolenya adalah 'student'
                                        $q_siswa = mysqli_query($koneksi, "SELECT id, name, email FROM users WHERE role='student' ORDER BY name ASC");
                                        while ($s = mysqli_fetch_assoc($q_siswa)) {
                                            echo "<option value='{$s['id']}'>{$s['name']} ({$s['email']})</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Pilih Kelas Target</label>
                                    <select name="course_id" class="form-select" required>
                                        <option value="" disabled selected>-- Pilih Kelas --</option>
                                        <?php
                                        // Ambil seluruh data kelas yang tersedia
                                        $q_kelas = mysqli_query($koneksi, "SELECT id, name, category FROM courses ORDER BY name ASC");
                                        while ($k = mysqli_fetch_assoc($q_kelas)) {
                                            echo "<option value='{$k['id']}'>{$k['name']} - [{$k['category']}]</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <button type="submit" name="tambah_ke_kelas" class="btn btn-primary w-100 fw-bold shadow-sm">
                                    <i class="fas fa-link"></i> Hubungkan ke Kelas
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-dark text-white fw-bold">
                            <i class="fas fa-table me-1"></i> Data Pembagian Kelas Siswa
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="datatablesSimple" class="table table-bordered table-striped align-middle">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th>Nama Siswa</th>
                                            <th>Kelas / Mata Pelajaran</th>
                                            <th>Kategori</th>
                                            <th width="15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Query untuk memanggil rekap data gabungan tabel enrollments, users, dan courses
                                        $query_tabel = "SELECT e.id as id_enroll, u.name as nama_siswa, c.name as nama_kelas, c.category 
                                                        FROM enrollments e
                                                        JOIN users u ON e.student_id = u.id
                                                        JOIN courses c ON e.course_id = c.id
                                                        ORDER BY c.name ASC, u.name ASC";
                                        $result_tabel = mysqli_query($koneksi, $query_tabel);

                                        if (mysqli_num_rows($result_tabel) > 0) {
                                            $no = 1;
                                            while ($row = mysqli_fetch_assoc($result_tabel)) {
                                        ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td><strong><?= $row['nama_siswa'] ?></strong></td>
                                            <td><?= $row['nama_kelas'] ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary"><?= $row['category'] ?></span>
                                            </td>
                                            <td class="text-center">
                                                <a href="manajemen_siswa_kelas.php?hapus_enroll=<?= $row['id_enroll'] ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('Keluarkan siswa ini dari kelas?')">
                                                    <i class="fas fa-user-times"></i> Keluarkan
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center py-4 text-muted'>Belum ada siswa yang diploting ke kelas manapun.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

<?php require "bawah.php"; ?>