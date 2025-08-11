<?php

$host = 'localhost';
$username = 'root';
$password = ''; // Kosongkan jika tidak ada password
$database = 'cms_sederhana';

// Buat koneksi ke database
$conn = new mysqli($host, $username, $password, $database);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

?>
