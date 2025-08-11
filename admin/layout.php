<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Cek apakah admin sudah login
if (!is_logged_in()) {
    redirect('index.php');
}

$message = '';

// Proses form jika ada data yang dikirimkan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $homepage_layout = sanitize_input($_POST['homepage_layout']);

    // Cek apakah setting sudah ada
    $sql = "SELECT * FROM settings WHERE setting_name = 'homepage_layout'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Update setting yang sudah ada
        $sql = "UPDATE settings SET setting_value=? WHERE setting_name='homepage_layout'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $homepage_layout);
    } else {
        // Tambah setting baru
        $sql = "INSERT INTO settings (setting_name, setting_value) VALUES ('homepage_layout', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $homepage_layout);
    }

    if ($stmt->execute()) {
        $message = "Layout homepage berhasil diperbarui!";
    } else {
        $message = "Gagal memperbarui layout: " . $stmt->error;
    }
}

// Ambil layout yang saat ini aktif
$sql = "SELECT setting_value FROM settings WHERE setting_name = 'homepage_layout'";
$result = $conn->query($sql);
$current_layout = 'one_column'; // Default
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $current_layout = $row['setting_value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Layout Halaman</title>
    <link rel="stylesheet" href="../assets/css/style_admin.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'header_admin.php'; ?>

        <main class="content">
            <h1>Pengaturan Layout</h1>
            <?php if (!empty($message)): ?>
                <p class="success-message"><?= $message ?></p>
            <?php endif; ?>

            <form action="layout.php" method="POST">
                <div class="form-group">
                    <label for="homepage_layout">Pilih Layout untuk Halaman Utama:</label>
                    <select name="homepage_layout" id="homepage_layout" required>
                        <option value="one_column" <?= ($current_layout === 'one_column') ? 'selected' : '' ?>>1 Kolom</option>
                        <option value="two_column_sidebar" <?= ($current_layout === 'two_column_sidebar') ? 'selected' : '' ?>>2 Kolom (dengan Sidebar)</option>
                        <option value="three_column" <?= ($current_layout === 'three_column') ? 'selected' : '' ?>>3 Kolom</option>
                    </select>
                </div>
                <button type="submit">Simpan Pengaturan</button>
            </form>
        </main>

        <?php include 'footer_admin.php'; ?>
    </div>
</body>
</html>
