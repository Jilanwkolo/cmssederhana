<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Cek apakah admin sudah login, jika belum arahkan ke halaman login
if (!is_logged_in()) {
    redirect('index.php');
}

$message = '';

// Proses form jika ada data yang dikirimkan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_menu'])) {
        $title = sanitize_input($_POST['title']);
        $url = sanitize_input($_POST['url']);
        $sort_order = (int)$_POST['sort_order'];

        $sql = "INSERT INTO menus (title, url, sort_order) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $url, $sort_order);

        if ($stmt->execute()) {
            $message = "Menu berhasil ditambahkan!";
        } else {
            $message = "Gagal menambahkan menu: " . $stmt->error;
        }
    } elseif (isset($_POST['edit_menu'])) {
        $menu_id = (int)$_POST['menu_id'];
        $title = sanitize_input($_POST['title']);
        $url = sanitize_input($_POST['url']);
        $sort_order = (int)$_POST['sort_order'];

        $sql = "UPDATE menus SET title=?, url=?, sort_order=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $title, $url, $sort_order, $menu_id);

        if ($stmt->execute()) {
            $message = "Menu berhasil diperbarui!";
        } else {
            $message = "Gagal memperbarui menu: " . $stmt->error;
        }
    }
}

// Proses hapus menu
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $menu_id = (int)$_GET['id'];
    $sql = "DELETE FROM menus WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $menu_id);

    if ($stmt->execute()) {
        $message = "Menu berhasil dihapus!";
        redirect('menus.php');
    } else {
        $message = "Gagal menghapus menu: " . $stmt->error;
    }
}

// Ambil semua menu untuk ditampilkan
$sql = "SELECT * FROM menus ORDER BY sort_order ASC, title ASC";
$result = $conn->query($sql);
$menus = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Menu Navigasi</title>
    <link rel="stylesheet" href="../assets/css/style_admin.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'header_admin.php'; ?>

        <main class="content">
            <h1>Kelola Menu Navigasi</h1>
            <?php if (!empty($message)): ?>
                <p class="success-message"><?= $message ?></p>
            <?php endif; ?>

            <h2>Tambah Menu Baru</h2>
            <form action="menus.php" method="POST">
                <div class="form-group">
                    <label for="title">Judul Menu:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="url">URL:</label>
                    <input type="text" id="url" name="url" required>
                    <small>Contoh: `index.php` atau `page.php?slug=tentang-kami`</small>
                </div>
                <div class="form-group">
                    <label for="sort_order">Urutan:</label>
                    <input type="number" id="sort_order" name="sort_order" value="0">
                </div>
                <button type="submit" name="add_menu">Tambah Menu</button>
            </form>

            <hr>

            <h2>Daftar Menu</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>URL</th>
                        <th>Urutan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menus as $menu): ?>
                        <tr>
                            <td><?= $menu['id'] ?></td>
                            <td><?= htmlspecialchars($menu['title']) ?></td>
                            <td><?= htmlspecialchars($menu['url']) ?></td>
                            <td><?= $menu['sort_order'] ?></td>
                            <td>
                                <a href="#" onclick="showEditForm(<?= $menu['id'] ?>, '<?= htmlspecialchars($menu['title']) ?>', '<?= htmlspecialchars($menu['url']) ?>', '<?= $menu['sort_order'] ?>')">Edit</a> |
                                <a href="menus.php?action=delete&id=<?= $menu['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini?');">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div id="edit-form-container" style="display:none;">
                <h3>Edit Menu</h3>
                <form action="menus.php" method="POST">
                    <input type="hidden" name="menu_id" id="edit-menu-id">
                    <div class="form-group">
                        <label for="edit-title">Judul Menu:</label>
                        <input type="text" id="edit-title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-url">URL:</label>
                        <input type="text" id="edit-url" name="url" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-sort_order">Urutan:</label>
                        <input type="number" id="edit-sort_order" name="sort_order">
                    </div>
                    <button type="submit" name="edit_menu">Perbarui Menu</button>
                    <button type="button" onclick="hideEditForm()">Batal</button>
                </form>
            </div>
        </main>

        <?php include 'footer_admin.php'; ?>
    </div>

    <script>
        function showEditForm(id, title, url, sort_order) {
            document.getElementById('edit-form-container').style.display = 'block';
            document.getElementById('edit-menu-id').value = id;
            document.getElementById('edit-title').value = title;
            document.getElementById('edit-url').value = url;
            document.getElementById('edit-sort_order').value = sort_order;
        }
        function hideEditForm() {
            document.getElementById('edit-form-container').style.display = 'none';
        }
    </script>
</body>
</html>
