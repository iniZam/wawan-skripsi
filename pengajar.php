<?php
require "koneksi.php";
require "atas.php";
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Data Pengajar</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Pengajar</li>
            </ol>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-table me-1"></i> Daftar Guru / Pengajar</div>
                    
                    <?php if($_SESSION['role'] == 'admin'){ ?>
                        <a href="tambah_pengajar.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah Baru
                        </a>
                    <?php } ?>
                </div>
                <div class="card-body">
                    <table id="datatablesSimple">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Bergabung Sejak</th>
                                <?php if($_SESSION['role'] == 'admin'){ ?>
                                    <th>Aksi</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            // Ambil user yang role-nya teacher
                            $query = mysqli_query($koneksi, "SELECT * FROM users WHERE role='teacher' ORDER BY name ASC");
                            while ($row = mysqli_fetch_assoc($query)) {
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $row['name'] ?></td>
                                <td><?= $row['email'] ?></td>
                                <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                
                                <?php if($_SESSION['role'] == 'admin'){ ?>
                                <td>
                                    <a href="#" class="btn btn-warning btn-sm" onclick="alert('Fitur Edit User belum dibuat')"><i class="fas fa-edit"></i></a>
                                    <a href="#" class="btn btn-danger btn-sm" onclick="return confirm('Hapus pengajar ini?')"><i class="fas fa-trash"></i></a>
                                </td>
                                <?php } ?>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

<?php require "bawah.php"; ?>