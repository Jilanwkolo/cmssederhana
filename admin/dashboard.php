<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Cek apakah admin sudah login, jika belum arahkan ke halaman login
if (!is_logged_in()) {
    redirect('index.php');
}

// Tambahkan kode untuk mengambil data dari database jika diperlukan
// Misalnya, jumlah halaman atau postingan
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/css/style_admin.css"> </head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'header_admin.php'; // Menyertakan header admin ?>

        <main class="content">
            <h1>Selamat datang di Dashboard, <?= $_SESSION['username'] ?>!</h1>
            <p>Ini adalah halaman utama panel admin Anda. Di sini Anda dapat melihat ringkasan data dan mengelola konten website.</p>
            
            <div class="summary-cards">
                <div class="card">
                    <h3>Total Halaman</h3>
                    <p>5</p> <a href="pages.php">Kelola Halaman</a>
                </div>
                <div class="card">
                    <h3>Total Postingan</h3>
                    <p>12</p> <a href="posts.php">Kelola Postingan</a>
                </div>
                <div class="card">
                    <h3>Menu Navigasi</h3>
                    <p>4 item</p> <a href="menus.php">Kelola Menu</a>
                </div>
            </div>
        </main>

        <?php include 'footer_admin.php'; // Menyertakan footer admin ?>
    </div>
</body>
</html>
