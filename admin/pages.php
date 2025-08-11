<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Cek apakah admin sudah login, jika belum arahkan ke halaman login
if (!is_logged_in()) {
    redirect('index.php');
}

// Tentukan action (list, add, edit, delete)
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$page_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

// Proses aksi-aksi yang ada
switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = sanitize_input($_POST['title']);
            $content = $_POST['content']; // HTML content, hati-hati
            $layout = sanitize_input($_POST['layout']);
            $slug = sanitize_input(str_replace(' ', '-', strtolower($title)));

            $sql = "INSERT INTO pages (title, slug, content, layout) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $title, $slug, $content, $layout);

            if ($stmt->execute()) {
                $message = "Halaman berhasil ditambahkan!";
                redirect('pages.php'); // Redirect kembali ke halaman daftar
            } else {
                $message = "Gagal menambahkan halaman: " . $stmt->error;
            }
        }
        break;

    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $page_id > 0) {
            $title = sanitize_input($_POST['title']);
            $content = $_POST['content'];
            $layout = sanitize_input($_POST['layout']);
            $slug = sanitize_input(str_replace(' ', '-', strtolower($title)));

            $sql = "UPDATE pages SET title=?, slug=?, content=?, layout=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $title, $slug, $content, $layout, $page_id);

            if ($stmt->execute()) {
                $message = "Halaman berhasil diperbarui!";
                redirect('pages.php');
            } else {
                $message = "Gagal memperbarui halaman: " . $stmt->error;
            }
        }
        
        // Ambil data halaman yang akan diedit
        $sql = "SELECT * FROM pages WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $page_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $page_to_edit = $result->fetch_assoc();

        if (!$page_to_edit) {
            $message = "Halaman tidak ditemukan.";
            $action = 'list';
        }
        break;

    case 'delete':
        if ($page_id > 0) {
            $sql = "DELETE FROM pages WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $page_id);

            if ($stmt->execute()) {
                $message = "Halaman berhasil dihapus!";
            } else {
                $message = "Gagal menghapus halaman: " . $stmt->error;
            }
        }
        $action = 'list'; // Kembali ke tampilan daftar setelah penghapusan
        break;

    case 'list':
    default:
        // Ambil semua halaman dari database
        $sql = "SELECT * FROM pages ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $pages = $result->fetch_all(MYSQLI_ASSOC);
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Halaman</title>
    <link rel="stylesheet" href="../assets/css/style_admin.css">
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script> </head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'header_admin.php'; ?>

        <main class="content">
            <h1>Kelola Halaman</h1>
            <?php if (!empty($message)): ?>
                <p class="success-message"><?= $message ?></p>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <a href="pages.php?action=add" class="btn btn-primary">Tambah Halaman Baru</a>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Layout</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $page): ?>
                            <tr>
                                <td><?= $page['id'] ?></td>
                                <td><?= htmlspecialchars($page['title']) ?></td>
                                <td><?= htmlspecialchars($page['layout']) ?></td>
                                <td><?= date('d M Y', strtotime($page['created_at'])) ?></td>
                                <td>
                                    <a href="pages.php?action=edit&id=<?= $page['id'] ?>">Edit</a> |
                                    <a href="pages.php?action=delete&id=<?= $page['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus halaman ini?');">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php elseif ($action === 'add' || $action === 'edit'): ?>
                <form action="pages.php?action=<?= $action ?><?= $page_id > 0 ? '&id=' . $page_id : '' ?>" method="POST">
                    <div class="form-group">
                        <label for="title">Judul Halaman:</label>
                        <input type="text" id="title" name="title" value="<?= isset($page_to_edit) ? htmlspecialchars($page_to_edit['title']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="content">Isi Halaman:</label>
                        <textarea id="content" name="content"><?= isset($page_to_edit) ? htmlspecialchars($page_to_edit['content']) : '' ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="layout">Pilih Layout:</label>
                        <select name="layout" id="layout" required>
                            <option value="one_column" <?= (isset($page_to_edit) && $page_to_edit['layout'] === 'one_column') ? 'selected' : '' ?>>1 Kolom</option>
                            <option value="two_column_sidebar" <?= (isset($page_to_edit) && $page_to_edit['layout'] === 'two_column_sidebar') ? 'selected' : '' ?>>2 Kolom (dengan Sidebar)</option>
                            <option value="three_column" <?= (isset($page_to_edit) && $page_to_edit['layout'] === 'three_column') ? 'selected' : '' ?>>3 Kolom</option>
                        </select>
                    </div>
                    <button type="submit">Simpan Halaman</button>
                    <a href="pages.php" class="btn btn-secondary">Batal</a>
                </form>
                <script>
                    CKEDITOR.replace('content');
                </script>
            <?php endif; ?>
        </main>

        <?php include 'footer_admin.php'; ?>
    </div>
</body>
</html>
