<?php
require "koneksi.php";
require "atas.php";

// Proteksi Keamanan: HANYA ADMIN yang boleh mengakses halaman ini
if ($_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses Ditolak! Hanya Admin yang berhak menambah pengguna.'); window.location='index.php';</script>";
    exit;
}

// Proses Simpan Data Pengguna Baru
if (isset($_POST['simpan_pengguna'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password']; // Jika sistem loginmu menggunakan MD5, ubah menjadi md5($_POST['password'])
    $role = $_POST['role'];

    // Validasi: Cek apakah email sudah pernah didaftarkan sebelumnya
    $cek_email = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek_email) > 0) {
        echo "<script>alert('Gagal! Email tersebut sudah digunakan oleh pengguna lain.');</script>";
    } else {
        // Eksekusi query insert ke tabel users
        $query_insert = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
        
        if (mysqli_query($koneksi, $query_insert)) {
            echo "<script>alert('Pengguna baru berhasil ditambahkan!'); window.location.href='index.php';</script>";
            // Catatan: Arahkan window.location ke halaman tabel daftar pengguna (misal: data_pengguna.php) jika kamu sudah membuatnya
        } else {
            echo "<script>alert('Terjadi kesalahan: " . mysqli_error($koneksi) . "');</script>";
        }
    }
}
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Tambah Pengguna Baru</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Tambah Pengguna</li>
            </ol>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0 rounded-lg mt-3 mb-5">
                        <div class="card-header bg-dark text-white text-center">
                            <h5 class="font-weight-light my-2"><i class="fas fa-user-plus me-2"></i>Form Registrasi Pengguna</h5>
                        </div>
                        <div class="card-body p-4">
                            
                            <form method="POST" action="">
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="inputName" type="text" name="name" placeholder="Nama Lengkap" required />
                                    <label for="inputName">Nama Lengkap</label>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <input class="form-control" id="inputEmail" type="email" name="email" placeholder="name@example.com" required />
                                    <label for="inputEmail">Alamat Email</label>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3 mb-md-0">
                                            <input class="form-control" id="inputPassword" type="password" name="password" placeholder="Buat Password" required />
                                            <label for="inputPassword">Password</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select class="form-select" id="selectRole" name="role" required>
                                                <option value="" disabled selected>-- Pilih Peran --</option>
                                                <option value="student">Siswa</option>
                                                <option value="teacher">Guru / Pengajar</option>
                                                <option value="admin">Administrator</option>
                                            </select>
                                            <label for="selectRole">Hak Akses (Role)</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info small border-0 mt-4 mb-4">
                                    <strong><i class="fas fa-info-circle"></i> Info:</strong> Pastikan alamat email aktif dan penulisannya benar, karena akan digunakan oleh pengguna untuk melakukan <em>login</em> ke dalam sistem.
                                </div>

                                <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                    <a class="btn btn-outline-secondary" href="index.php"><i class="fas fa-arrow-left"></i> Batal</a>
                                    <button type="submit" name="simpan_pengguna" class="btn btn-primary px-5 fw-bold shadow-sm">
                                        <i class="fas fa-save"></i> Simpan Pengguna
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

<?php require "bawah.php"; ?>