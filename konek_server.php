<?php
function uploadToNextcloud($fileInputName) {
    // --- KONFIGURASI NEXTCLOUD ---
    // $nc_host = "https://solid-skripsi.skripsijanuari.my.id/"; // Ganti URL Nextcloud (JANGAN pakai tanda / di akhir)
    $nc_host = "192.168.10.168:7580"; // Ganti URL Nextcloud (JANGAN pakai tanda / di akhir)
    $nc_user = "casaos";           // Ganti Username
    $nc_pass = "casaos";   // Ganti Password
    $nc_folder = "materi_e-learning";           // Folder tujuan

    // Cek apakah file ada error saat upload PHP
    if ($_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $tmp_file = $_FILES[$fileInputName]['tmp_name'];
    $filename = time() . "_" . str_replace(" ", "_", $_FILES[$fileInputName]['name']); // Ganti spasi dengan _
    
    // --- LANGKAH 1: UPLOAD VIA WEBDAV ---
    // Pastikan URL bersih
    $webdav_url = "$nc_host/remote.php/dav/files/$nc_user/$nc_folder/$filename";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webdav_url);
    curl_setopt($ch, CURLOPT_USERPWD, "$nc_user:$nc_pass");
    curl_setopt($ch, CURLOPT_PUT, 1);
    curl_setopt($ch, CURLOPT_INFILE, fopen($tmp_file, 'r'));
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($tmp_file));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response_upload = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Cek sukses upload (Code 201 = Created, 204 = No Content)
    if ($http_code >= 200 && $http_code < 300) {
        
        // --- LANGKAH 2: GENERATE PUBLIC LINK (OCS API) ---
        // Tambahkan ?format=json agar respon rapi
        $api_url = "$nc_host/ocs/v2.php/apps/files_sharing/api/v1/shares?format=json";
        $path_to_share = "/$nc_folder/$filename";

        $post_data = [
            'path' => $path_to_share,
            'shareType' => 3, // 3 = Public Link
            'permissions' => 1 // 1 = Read Only
        ];

        $ch_api = curl_init();
        curl_setopt($ch_api, CURLOPT_URL, $api_url);
        curl_setopt($ch_api, CURLOPT_USERPWD, "$nc_user:$nc_pass");
        curl_setopt($ch_api, CURLOPT_POST, 1);
        curl_setopt($ch_api, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch_api, CURLOPT_RETURNTRANSFER, true);
        // Header wajib OCS
        curl_setopt($ch_api, CURLOPT_HTTPHEADER, ['OCS-APIRequest: true']); 

        $api_response = curl_exec($ch_api);
        curl_close($ch_api);

        // --- LANGKAH 3: PARSING HASIL (PENYARINGAN) ---
        $json = json_decode($api_response, true);
        
        // Cara 1: Coba ambil dari JSON Murni
        if (isset($json['ocs']['data']['url'])) {
            return $json['ocs']['data']['url'];
        } 
        
        // Cara 2 (Cadangan): Jika JSON gagal, cari URL pakai Regex di dalam teks sampah
        // Mencari pola http:// atau https:// diikuti karakter valid
        preg_match('/\bhttps?:\/\/[^\s<"]+/', $api_response, $matches);
        if (isset($matches[0])) {
            return $matches[0]; // Kembalikan hanya link-nya
        }

        return false; // Gagal mendapatkan link

    } else {
        return false; // Gagal Upload WebDAV
    }
}
?>