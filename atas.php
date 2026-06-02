<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] != true) {
    header("Location: login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);

// 3. Logika redirect khusus Siswa: 
// Jika yang login adalah siswa dan dia mencoba membuka 'index.php', arahkan ke dasbor siswa
if ($_SESSION['role'] == 'student' && $current_page == 'index.php') {
    header("Location: dashboard_siswa.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Dashboard - E-Learning</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <a class="navbar-brand ps-3" href="index.php">E-Learning</a>
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                    <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user fa-fw"></i> <?= htmlspecialchars($_SESSION['name']); ?> </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#!">Settings</a></li>
                        <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading">Menu Utama</div>
                            <a class="nav-link" href="index.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>

                            <div class="sb-sidenav-menu-heading">Data Master</div>
                            
                           

                            <a class="nav-link" href="kelas.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Data Kelas
                            </a>

                            <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'teacher') { ?>
                            <a class="nav-link" href="manajemen_siswa.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-graduate"></i></div>
                                Data Siswa
                            </a>
                             <a class="nav-link" href="pengajar.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                Data Pengajar
                            </a>
                             <a class="nav-link" href="absen.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                                absen
                            </a>
                            <?php } ?>


                            <?php if ($_SESSION['role'] == 'admin') { ?>
                            <div class="sb-sidenav-menu-heading">Admin Area</div>
                            <a class="nav-link" href="tambah_user.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-plus"></i></div>
                                Tambah User
                            </a>
                            <a class="nav-link" href="manajemen_siswa_kelas.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-plus"></i></div>
                                Kelola siswa
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        <strong><?= htmlspecialchars($_SESSION['name']); ?></strong><br>
                        (<?= ucfirst(htmlspecialchars($_SESSION['role'])); ?>)
                    </div>
                    
                </nav>
            </div>