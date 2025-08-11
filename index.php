<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Ambil pengaturan layout homepage dari database
$sql = "SELECT setting_value FROM settings WHERE setting_name = 'homepage_layout'";
$result = $conn->query($sql);
$homepage_layout = 'one_column'; // Layout default
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $homepage_layout = $row['setting_value'];
}

// Ambil konten homepage (misal, konten dari halaman dengan slug 'homepage')
$sql = "SELECT * FROM pages WHERE slug = 'homepage'";
$result = $conn->query($sql);
$homepage_content = $result->fetch_assoc();

// Ambil menu navigasi
$sql = "SELECT * FROM menus ORDER BY sort_order ASC, title ASC";
$result = $conn->query($sql);
$menus = $result->fetch_all(MYSQLI_ASSOC);

// Ambil postingan terbaru untuk sidebar (jika layout 2 atau 3 kolom)
$recent_posts = [];
if ($homepage_layout === 'two_column_sidebar' || $homepage_layout === 'three_column') {
    $sql = "SELECT title, slug FROM posts ORDER BY created_at DESC LIMIT 5";
    $result = $conn->query($sql);
    $recent_posts = $result->fetch_all(MYSQLI_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($homepage_content['title'] ?? 'Home') ?> - CMS Sederhana</title>
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
    
    <div class="container <?= $homepage_layout ?>">
        <main class="main-content">
            <?php if ($homepage_content): ?>
                <h2><?= htmlspecialchars($homepage_content['title']) ?></h2>
                <div class="page-content">
                    <?= $homepage_content['content'] ?>
                </div>
            <?php else: ?>
                <h2>Selamat Datang!</h2>
                <p>Silakan buat halaman utama dari panel admin.</p>
            <?php endif; ?>
        </main>

        <?php if ($homepage_layout === 'two_column_sidebar' || $homepage_layout === 'three_column'): ?>
            <aside class="sidebar">
                <h3>Postingan Terbaru</h3>
                <ul>
                    <?php foreach ($recent_posts as $post): ?>
                        <li><a href="post.php?slug=<?= htmlspecialchars($post['slug']) ?>"><?= htmlspecialchars($post['title']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </aside>
        <?php endif; ?>

        <?php if ($homepage_layout === 'three_column'): ?>
            <aside class="sidebar-right">
                <h3>Sidebar Kanan</h3>
                <p>Konten tambahan di sidebar kanan.</p>
            </aside>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> CMS Sederhana. Hak Cipta Dilindungi.</p>
    </footer>
</body>
</html>
