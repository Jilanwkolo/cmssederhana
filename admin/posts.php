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
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

// Proses aksi-aksi yang ada
switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = sanitize_input($_POST['title']);
            $content = $_POST['content'];
            $slug = sanitize_input(str_replace(' ', '-', strtolower($title)));

            $sql = "INSERT INTO posts (title, slug, content) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $title, $slug, $content);

            if ($stmt->execute()) {
                $message = "Postingan berhasil ditambahkan!";
                redirect('posts.php'); // Redirect kembali ke halaman daftar
            } else {
                $message = "Gagal menambahkan postingan: " . $stmt->error;
            }
        }
        break;

    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $post_id > 0) {
            $title = sanitize_input($_POST['title']);
            $content = $_POST['content'];
            $slug = sanitize_input(str_replace(' ', '-', strtolower($title)));

            $sql = "UPDATE posts SET title=?, slug=?, content=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $title, $slug, $content, $post_id);

            if ($stmt->execute()) {
                $message = "Postingan berhasil diperbarui!";
                redirect('posts.php');
            } else {
                $message = "Gagal memperbarui postingan: " . $stmt->error;
            }
        }
        
        // Ambil data postingan yang akan diedit
        $sql = "SELECT * FROM posts WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $post_to_edit = $result->fetch_assoc();

        if (!$post_to_edit) {
            $message = "Postingan tidak ditemukan.";
            $action = 'list';
        }
        break;

    case 'delete':
        if ($post_id > 0) {
            $sql = "DELETE FROM posts WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $post_id);

            if ($stmt->execute()) {
                $message = "Postingan berhasil dihapus!";
            } else {
                $message = "Gagal menghapus postingan: " . $stmt->error;
            }
        }
        $action = 'list'; // Kembali ke tampilan daftar setelah penghapusan
        break;

    case 'list':
    default:
        // Ambil semua postingan dari database
        $sql = "SELECT * FROM posts ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $posts = $result->fetch_all(MYSQLI_ASSOC);
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Postingan Blog</title>
    <link rel="stylesheet" href="../assets/css/style_admin.css">
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'header_admin.php'; ?>

        <main class="content">
            <h1>Kelola Postingan Blog</h1>
            <?php if (!empty($message)): ?>
                <p class="success-message"><?= $message ?></p>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <a href="posts.php?action=add" class="btn btn-primary">Tambah Postingan Baru</a>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?= $post['id'] ?></td>
                                <td><?= htmlspecialchars($post['title']) ?></td>
                                <td><?= date('d M Y', strtotime($post['created_at'])) ?></td>
                                <td>
                                    <a href="posts.php?action=edit&id=<?= $post['id'] ?>">Edit</a> |
                                    <a href="posts.php?action=delete&id=<?= $post['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus postingan ini?');">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php elseif ($action === 'add' || $action === 'edit'): ?>
                <form action="posts.php?action=<?= $action ?><?= $post_id > 0 ? '&id=' . $post_id : '' ?>" method="POST">
                    <div class="form-group">
                        <label for="title">Judul Postingan:</label>
                        <input type="text" id="title" name="title" value="<?= isset($post_to_edit) ? htmlspecialchars($post_to_edit['title']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="content">Isi Postingan:</label>
                        <textarea id="content" name="content"><?= isset($post_to_edit) ? htmlspecialchars($post_to_edit['content']) : '' ?></textarea>
                    </div>
                    <button type="submit">Simpan Postingan</button>
                    <a href="posts.php" class="btn btn-secondary">Batal</a>
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
