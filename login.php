<?php
session_start(); // Wajib ada di baris pertama untuk memulai sesi
require "koneksi.php";

// Jika user sudah login, jangan biarkan masuk ke halaman login lagi
if (isset($_SESSION['is_login']) && $_SESSION['is_login'] == true) {
    header("Location: index.php");
    exit;
}

// --- PROSES LOGIN ---
if (isset($_POST['login'])) {
    $email    = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']); 

    // Cek email user di database
    $query  = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
    $cek    = mysqli_num_rows($query);

    if ($cek > 0) {
        $data = mysqli_fetch_assoc($query);

        // Cek Password (disesuaikan dengan plain text '123' dari data dummy kita)
        // Jika nanti pakai hash, gunakan: if (password_verify($password, $data['password']))
        if ($password == $data['password']) {
            
            // SET SESSION (Menyimpan data user di memori browser sementara)
            $_SESSION['is_login'] = true;
            $_SESSION['id_user']  = $data['id'];
            $_SESSION['name']     = $data['name'];
            $_SESSION['role']     = $data['role']; // Ini yang membedakan admin/guru/siswa

            // Redirect berdasarkan Role (Opsional, saat ini semua ke index.php dulu)
            if($data['role'] == 'admin'){
                // echo "Login sebagai Admin";
                header("Location: index.php");
            } else if($data['role'] == 'teacher'){
                // echo "Login sebagai Guru";
                header("Location: index.php");
            } else if($data['role'] == 'student'){
                // echo "Login sebagai Siswa";
                header("Location: index.php");
            }
            exit;

        } else {
            echo "<script>alert('Password salah!');</script>";
        }
    } else {
        echo "<script>alert('Email tidak ditemukan!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Login - E-Learning</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header">
                                        <h3 class="text-center font-weight-light my-4">Login E-Learning</h3>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="">
                                            
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="inputEmail" type="email" name="email" placeholder="name@example.com" required />
                                                <label for="inputEmail">Email Address</label>
                                            </div>
                                            
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="inputPassword" type="password" name="password" placeholder="Password" required />
                                                <label for="inputPassword">Password</label>
                                            </div>
                                            
                                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                                <a class="small" href="#">Lupa Password?</a>
                                                <button type="submit" name="login" class="btn btn-primary">Login</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-center py-3">
                                        <div class="small"><a href="register.php">Belum punya akun? Daftar sebagai Siswa</a></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    </body>
</html>