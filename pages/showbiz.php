<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/init_lang.php';

$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ru';
$category_name = 'Шоу-бизнес';

$sql = "SELECT n.id, n.image, n.views, n.created_at, t.title, t.content 
        FROM news n
        JOIN categories c ON n.category_id = c.id
        JOIN news_translations t ON n.id = t.news_id
        WHERE c.name = ? 
        AND n.status = 'approved' 
        AND t.language = ?
        ORDER BY n.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $category_name, $lang);
$stmt->execute();
$query_result = $stmt->get_result();

$result = [];

while ($row = $query_result->fetch_assoc()) {
    $n_id = (int)$row['id'];
    $c_res = $conn->query("SELECT COUNT(*) as count FROM comments WHERE news_id = $n_id");
    $row['comments_count'] = $c_res->fetch_assoc()['count'] ?? 0;
    $result[] = $row;
}

// Fetch top authors from DB
$authors_result = $conn->query("SELECT name FROM users WHERE role != 'admin' ORDER BY rating DESC LIMIT 7");
$dbAuthors = [];
if ($authors_result && $authors_result->num_rows > 0) {
    while ($a = $authors_result->fetch_assoc()) {
        $dbAuthors[] = $a;
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="unit">Шоу-бизнес | Logotip news</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/layout.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/pages/category.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
</head>

<body class="page-category page-showbiz">
    <header class="main-header">
        <div class="container header-top-row">
            <div class="header-empty"></div>
            <div class="logo-container">
                <a href="/pages/index.php" class="logo-link" style="text-decoration: none; color: inherit;">
                    <h1 class="logo">Logotip news</h1>
                </a>
            </div>
            <div class="header-actions">
                <a href="/admin/create.php" class="btn-suggest" id="suggest-news-btn">Предложить новость</a>
                <div class="lang-switcher">
                    <div class="lang-dropdown">
                        <button class="lang-btn">
                            <span class="lang-text"><?= $lang ?></span>
                            <span class="lang-arrow">▼</span>
                        </button>
                        <div class="lang-list">
                            <a href="#kz" class="lang-item">KZ</a>
                            <a href="#ru" class="lang-item">RU</a>
                            <a href="#en" class="lang-item">EN</a>
                        </div>
                    </div>
                </div>
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="/auth/logout.php" class="auth-btn-black" id="logout">Выйти</a>
                <?php else: ?>
                    <a href="/auth/login.html" class="auth-btn-black" id="login">Авторизоваться</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="container header-search-row">
            <div class="search-bar">
                <input type="text" class="search-input" placeholder="Поиск по сайту">
            </div>
        </div>

        <div class="container padding_786">
            <nav class="navbar navbar-toggleable-md navbar-light">
                <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="fa-solid fa-bars"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/index.php" id="home">Главная</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/politics.php" id="politics">Политика</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/analytics.php" id="analytics">Аналитика</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/world.php" id="world">Мировые новости</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/showbiz.php" id="showbiz">Шоу-бизнес</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/sport.php" id="sports">Спорт</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/feed.php" id="my_lenta">Моя лента</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>
    <main class="section category-layout">
        <div class="container category-container">
            <div class="category-main">
                <div class="category-heading">
                    <h1 class="cat-4">Шоу-бизнес</h1>
                    <p class="cat-desc-4">Новости культуры, знаменитостей, музыки, кино и развлекательной индустрии.</p>
                </div>
                <?php if (!empty($result)): ?>
                    <?php $featured = $result[0]; ?>
                    <article class="featured-news-card">
                        <a href="/pages/article.php?id=<?= $featured['id'] ?>" class="featured-news-link">
                            <img
                            src="<?= htmlspecialchars($featured['image']) ?>"
                            class="featured-news-image"
                            alt="news">
                            <div class="featured-news-content">
                                <span class="featured-news-category cat-4">Шоу-бизнес</span>
                                <h2 class="featured-news-title">
                                    <?= htmlspecialchars($featured['title']) ?>
                                </h2>
                                <p class="featured-news-excerpt">
                                    <?= mb_strimwidth(strip_tags($featured['content']), 0, 180, "...") ?>
                                </p>
                                <div class="featured-news-meta">
                                <span>
                                    <?= date('d.m.Y', strtotime($featured['created_at'])) ?>
                                </span>
                                <span>
                                    <i class="far fa-eye"></i>
                                    <?= $featured['views'] ?>
                                </span>
                                <span>
                                    <i class="far fa-eye"></i>
                                    <?= $featured['comments_count'] ?>
                                </span>
                                </div>
                            </div>
                        </a>
                    </article>
                    <div class="category-news-list">
                        <?php foreach (array_slice($result, 1) as $row): ?>
                            <article class="category-news-item">
                                <a href="/pages/article.php?id=<?= $row['id'] ?>" class="category-news-link">
                                    <img
                                    src="<?= htmlspecialchars($row['image']) ?>"
                                    class="category-news-thumb"
                                    alt="news">
                                    <div class="category-news-info">
                                        <span class="category-news-label cat-4">Шоу-бизнес</span>
                                        <h3 class="category-news-title">
                                            <?= htmlspecialchars($row['title']) ?>
                                        </h3>
                                        <div class="category-news-meta">
                                        <span>
                                            <?= date('d.m.Y', strtotime($row['created_at'])) ?>
                                        </span>
                                        <span>
                                            <i class="far fa-eye"></i>
                                            <?= $row['views'] ?>
                                        </span>
                                        <span>
                                            <i class="far fa-comment"></i>
                                            <?= $row['comments_count'] ?>
                                        </span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <aside class="category-sidebar">
                <div class="sidebar-box">
                    <h3 class="sidebar-title sidebar-authors-title" id="sidebar-authors-title">Авторы</h3>
                    <ol class="author-list" id="best-authors-list">
                        <?php if (!empty($dbAuthors)): ?>
                            <?php foreach ($dbAuthors as $author):
                                $nameParts = explode(' ', trim($author['name']));
                                $displayName = isset($nameParts[1]) ? $nameParts[0] . ' ' . $nameParts[1] : $nameParts[0];
                                ?>
                                <li class="author-item">
                                    <span class="author-name"><?= htmlspecialchars($displayName) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-data" id="no-data">Список пуст</p>
                        <?php endif; ?>
                    </ol>
                </div>
            </aside>
        </div>
    </main>

    <footer class="main-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <div class="footer-logo">LOGOTIP NEWS</div>
                <div class="footer-legal-info">
                    <p id="certificate1">Свидетельство о постановке на учет №KZ05VFY00030397</p>
                    <p id="certificate2">Выдано 22.12.2020</p>
                </div>
                <div class="footer-socials">
                    <a href="#" class="social-link" aria-label="Instagram">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                        <span>Instagram</span>
                    </a>
                    <a href="#" class="social-link" aria-label="TikTok">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>
                        <span>TikTok</span>
                    </a>
                </div>
            </div>

            <div class="footer-nav-section">
                <h4 class="footer-title">Компания</h4>
                <nav class="footer-links">
                    <a href="/pages/about.php" id="about_us">Про нас</a>
                    <a href="#" id="redaction">Редакция</a>
                    <a href="#" id="vacancy">Вакансии</a>
                </nav>
            </div>

            <div class="footer-nav-section">
                <h4 class="footer-title">Информация</h4>
                <nav class="footer-links">
                    <a href="/pages/contacts.php" id="contact">Контакты</a>
                    <a href="#" id="advertisement">Реклама</a>
                    <a href="#" id="support">Поддержка</a>
                </nav>
            </div>

            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                <div class="footer-nav-section">
                    <h4 class="footer-title admin-panel-title">Админ-панель</h4>
                    <nav class="footer-links">
                        <a href="/admin/admin.php" id="admin-site-mgmt" style="color: var(--color-accent); font-weight: bold;">Управление сайтом</a>
                        <a href="/admin/admin_actions.php" id="admin-news-mgmt">Управление новостями</a>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </footer>

    <script src="/assets/js/lang.js"></script>
    <script src="/assets/js/main.js"></script>
</body>

</html>