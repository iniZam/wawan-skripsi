<?php
// 1. Fungsi untuk upload file biasa (Video, Materi, Tugas, Soal Kuis)
function uploadFileLokal($input_name) {
    $target_dir = "upload/";
    
    // Pastikan folder tersedia
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Tambahkan waktu (time) di depan nama file agar tidak ada file yang namanya sama/bentrok
    $nama_file = time() . "_" . basename($_FILES[$input_name]["name"]);
    // Bersihkan spasi pada nama file agar URL tidak rusak
    $nama_file = str_replace(" ", "_", $nama_file);
    
    $target_file = $target_dir . $nama_file;

    // Pindahkan file ke folder 'uploads/'
    if (move_uploaded_file($_FILES[$input_name]["tmp_name"], $target_file)) {
        return $target_file; // Mengembalikan path (contoh: uploads/16123_tugas.pdf)
    } else {
        return false;
    }
}

// 2. Fungsi untuk menyimpan file buatan sistem (Rekap Excel)
function simpanFileLokal($file_content, $filename) {
    $target_dir = "uploads/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . $filename;

    // Tulis langsung teks ke dalam file Excel di folder lokal
    if (file_put_contents($target_file, $file_content)) {
        return $target_file;
    } else {
        return false;
    }
}
?>