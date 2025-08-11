<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$slug = isset($_GET['slug']) ? sanitize_input($_GET['slug']) : null;

if (!$slug) {
    // Jika tidak ada slug, arahkan ke halaman utama
    redirect('index.php');
}

// Ambil konten halaman berdasarkan slug
$sql = "SELECT * FROM pages WHERE slug = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$page = $result->fetch_assoc();

// Jika halaman tidak ditemukan
if (!$page) {
    http_response_code(404);
    die("Halaman tidak ditemukan.");
}

// Ambil menu navigasi
$sql = "SELECT * FROM menus ORDER BY sort_order ASC, title ASC";
$result = $conn->query($sql);
$menus = $result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page['title']) ?> - CMS Sederhana</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="logo">
            <h1>Nama Website</h1>
        </div>
        <nav>
            <ul>
                <?php foreach ($menus as $menu): ?>
                    <li><a href="<?= htmlspecialchars($menu['url']) ?>"><?= htmlspecialchars($menu['title']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </header>

    <div class="container <?= htmlspecialchars($page['layout']) ?>">
        <main class="main-content">
            <h2><?= htmlspecialchars($page['title']) ?></h2>
            <div class="page-content">
                <?= $page['content'] ?>
            </div>
        </main>
        </div>
    
    <footer>
        <p>&copy; <?= date('Y') ?> CMS Sederhana. Hak Cipta Dilindungi.</p>
    </footer>
</body>
</html>
