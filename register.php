<?php
require "koneksi.php";

// --- PROSES REGISTRASI ---
if (isset($_POST['register'])) {
    $name           = htmlspecialchars($_POST['name']);
    $email          = htmlspecialchars($_POST['email']);
    $password       = htmlspecialchars($_POST['password']);
    $password_conf  = htmlspecialchars($_POST['password_conf']);
    $role           = 'student'; // Otomatis jadi siswa

    // 1. Validasi Password
    if ($password != $password_conf) {
        echo "<script>alert('Password dan Konfirmasi Password tidak sama!');</script>";
    } else {
        // 2. Cek apakah email sudah ada
        $cek_email = mysqli_query($koneksi, "SELECT email FROM users WHERE email = '$email'");
        if (mysqli_num_rows($cek_email) > 0) {
            echo "<script>alert('Email sudah terdaftar! Silakan gunakan email lain atau login.');</script>";
        } else {
            // 3. Simpan ke Database
            // Catatan: Di project real, gunakan password_hash($password, PASSWORD_DEFAULT)
            $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
            
            if (mysqli_query($koneksi, $query)) {
                echo "<script>
                        alert('Registrasi Berhasil! Silakan Login.');
                        window.location.href = 'login.php'; 
                      </script>";
            } else {
                echo "<script>alert('Gagal mendaftar: " . mysqli_error($koneksi) . "');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Registrasi Siswa - E-Learning</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" /> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-7">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header">
                                        <h3 class="text-center font-weight-light my-4">Buat Akun Siswa</h3>
                                    </div>
                                    <div class="card-body">
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
                                                        <input class="form-control" id="inputPassword" type="password" name="password" placeholder="Password" required />
                                                        <label for="inputPassword">Password</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating mb-3 mb-md-0">
                                                        <input class="form-control" id="inputPasswordConfirm" type="password" name="password_conf" placeholder="Confirm Password" required />
                                                        <label for="inputPasswordConfirm">Konfirmasi Password</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-4 mb-0">
                                                <div class="d-grid">
                                                    <button type="submit" name="register" class="btn btn-primary btn-block">Daftar Akun</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-center py-3">
                                        <div class="small"><a href="login.php">Sudah punya akun? Login disini</a></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; E-Learning Saya 2023</div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    </body>
</html>