<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$slug = isset($_GET['slug']) ? sanitize_input($_GET['slug']) : null;

if (!$slug) {
    redirect('index.php');
}

// Ambil konten postingan berdasarkan slug
$sql = "SELECT * FROM posts WHERE slug = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    http_response_code(404);
    die("Postingan tidak ditemukan.");
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
    <title><?= htmlspecialchars($post['title']) ?> - CMS Sederhana</title>
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

    <div class="container one_column"> <main class="main-content">
            <article>
                <h2><?= htmlspecialchars($post['title']) ?></h2>
                <small>Diposting pada: <?= date('d M Y', strtotime($post['created_at'])) ?></small>
                <div class="post-content">
                    <?= $post['content'] ?>
                </div>
            </article>
        </main>
    </div>
    
    <footer>
        <p>&copy; <?= date('Y') ?> CMS Sederhana. Hak Cipta Dilindungi.</p>
    </footer>
</body>
</html>
