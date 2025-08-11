<?php

// Fungsi untuk membersihkan data input dari form
function sanitize_input($data) {
    global $conn; // Menggunakan koneksi database yang sudah dibuat

    // Menghapus spasi di awal dan akhir string
    $data = trim($data);
    
    // Menghapus backslash
    $data = stripslashes($data);
    
    // Mengubah karakter khusus menjadi entitas HTML
    $data = htmlspecialchars($data);
    
    // Melakukan escape string untuk mencegah SQL injection
    $data = mysqli_real_escape_string($conn, $data);

    return $data;
}

// Fungsi untuk mengarahkan pengguna ke halaman lain
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Fungsi untuk memeriksa apakah admin sudah login
function is_logged_in() {
    if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
        return true;
    }
    return false;
}

?>
