<?php
require "koneksi.php"; // Pastikan koneksi dipanggil paling atas
require "atas.php";    // Memanggil template header/sidebar

// --- LOGIKA PROSES SIMPAN DATA ---
// ... require koneksi dan atas ...
require "konek_server.php"; // PANGGIL HELPER TADI

if (isset($_POST['simpan'])) {
    $name        = htmlspecialchars($_POST['name']);
    $description = htmlspecialchars($_POST['description']);
    $teacher_id  = $_POST['teacher_id'];
    $category    = htmlspecialchars($_POST['category']);
    $start_date  = $_POST['start_date'];
    $status      = $_POST['status'];
    
    // --- LOGIKA UPLOAD ---
    $final_link = ""; // Default kosong

    // 1. Cek apakah ada file yang diupload?
    if (!empty($_FILES['file_materi']['name'])) {
        $upload_result = uploadToNextcloud('file_materi');
        
        if ($upload_result != false) {
            $final_link = $upload_result; // Link dari Nextcloud
        } else {
            echo "<script>alert('Gagal upload ke Nextcloud! Cek koneksi/password.');</script>";
            // Stop proses jika gagal upload (opsional)
        }
    } 
    // 2. Jika tidak upload file, cek apakah ada link manual?
    else if (!empty($_POST['link_materi'])) {
        $final_link = htmlspecialchars($_POST['link_materi']);
    }

    // Simpan ke Database (Kolom link_materi diisi $final_link)
    $query_insert = "INSERT INTO courses (name, description, link_materi, teacher_id, category, start_date, status) 
                     VALUES ('$name', '$description', '$final_link', '$teacher_id', '$category', '$start_date', '$status')";

    if (mysqli_query($koneksi, $query_insert)) {
        echo "<script>alert('Kelas & Materi Berhasil Disimpan!'); window.location.href = 'kelas.php';</script>";
    } else {
        echo "<script>alert('Error DB: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Tambah Kelas Baru</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Tambah Kelas</li>
            </ol>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-edit me-1"></i>
                    Formulir Kelas
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Kelas / Mata Pelajaran</label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="Contoh: Matematika Diskrit">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi Singkat</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Jelaskan tentang kelas ini..."></textarea>
                        </div>

         
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Kategori</label>
                                <input type="text" class="form-control" id="category" name="category" placeholder="Misal: Teknologi, Bahasa, Sains">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="teacher_id" class="form-label">Pengajar</label>
                                <select class="form-select" name="teacher_id" required>
                                    <option value="">-- Pilih Pengajar --</option>
                                    <?php
                                    // Ambil data user yang role-nya 'teacher'
                                    $query_guru = mysqli_query($koneksi, "SELECT * FROM users WHERE role='teacher'");
                                    while ($guru = mysqli_fetch_assoc($query_guru)) {
                                        echo "<option value='" . $guru['id'] . "'>" . $guru['name'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status Kelas</label>
                                <select class="form-select" name="status">
                                    <option value="registration">Pendaftaran (Registration)</option>
                                    <option value="active">Aktif (Active)</option>
                                    <option value="finished">Selesai (Finished)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="simpan" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Kelas
                            </button>
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </main>

<?php require "bawah.php"; ?>