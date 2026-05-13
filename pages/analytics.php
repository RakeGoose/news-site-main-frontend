<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/init_lang.php';
require_once __DIR__ . '/../config/mock_data.php';

$lang = $_SESSION['lang'] ?? 'ru';
$category_slug = 'analytics';

$result = array_values(array_filter($mockNews, function ($news) use ($lang, $category_slug) {
    return ($news['status'] ?? '') === 'approved'
            && ($news['language'] ?? 'ru') === $lang
            && ($news['category_slug'] ?? '') === $category_slug;
}));

usort($result, function ($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});

$mockCommentsCount = [
        1 => 4,
        2 => 2,
        3 => 7,
];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="unit">Аналитика | Logotip news</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/layout.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/pages/category.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="page-category page-analytics">
    <header class="main-header">
        <div class="container header-top">
            <div class="header-left">
                <button class="search-btn">🔍</button>
            </div>

            <div class="header-center">
                <div class="lang-switcher">
                    <a href="#kz" class="lang-item">KAZ</a>
                    <a href="#ru" class="lang-item">RUS</a>
                    <a href="#en" class="lang-item">ENG</a>
                </div>
            </div>

            <div class="header-right">
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="/auth/logout.php" class="auth-btn-black" id="logout">Выйти</a>
                <?php else: ?>
                    <a href="/auth/login.html" class="auth-btn-black" id="login">Авторизоваться</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="logo-container">
            <a href="/pages/index.php" style="text-decoration: none; color: inherit;">
                <h1 class="logo">Logotip news</h1>
            </a>
        </div>

        <nav class="main-nav">
            <div class="container nav-flex">
                <a href="/admin/create.php" class="btn-suggest" id="suggest-news-btn">Предложить новость 📢</a>
                <ul class="nav-links">
                    <li><a href="/pages/politics.php" id="politics">Политика</a></li>
                    <li><a href="/pages/analytics.php" id="analytics" class="active">Аналитика</a></li>
                    <li><a href="/pages/world.php" id="world">Мировые новости</a></li>
                    <li><a href="/pages/showbiz.php" id="showbiz">Шоу-бизнес</a></li>
                    <li><a href="/pages/sport.php" id="sports">Спорт</a></li>
                    <li><a href="/pages/feed.php" id="my_lenta">Моя лента</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="section category-layout">
        <div class="container category-container">

            <div class="category-main">

                <div class="category-heading">
                    <h1>Аналитика</h1>
                    <p>Глубокий анализ событий, тенденций и процессов в Казахстане и мире.</p>
                </div>

                <?php if (!empty($result)): ?>

                    <?php $featured = $result[0]; ?>

                    <article class="featured-news-card">
                        <a href="/pages/article.php?id=<?= $featured['id'] ?>" class="featured-news-link">

                            <img
                                src="/uploads/news/<?= htmlspecialchars($featured['image']) ?>"
                                class="featured-news-image"
                                alt="news">

                            <div class="featured-news-content">
                                <span class="featured-news-category">Аналитика</span>

                                <h2 class="featured-news-title">
                                    <?= htmlspecialchars($featured['title']) ?>
                                </h2>

                                <p class="featured-news-excerpt">
                                    <?= mb_strimwidth(strip_tags($featured['content']), 0, 180, "...") ?>
                                </p>

                                <div class="featured-news-meta">
                                    <span>
                                        <i class="far fa-calendar-alt"></i>
                                        <?= date('d.m.Y', strtotime($featured['created_at'])) ?>
                                    </span>

                                        <span>
                                        <i class="far fa-eye"></i>
                                        <?= $featured['views'] ?>
                                    </span>
                                </div>

                            </div>
                        </a>
                    </article>

                    <!-- SMALL NEWS -->
                    <div class="category-news-list">
                        <?php foreach (array_slice($result, 1) as $row): ?>

                            <article class="category-news-item">
                                <a href="/pages/article.php?id=<?= $row['id'] ?>" class="category-news-link">

                                    <img
                                        src="/uploads/news/<?= htmlspecialchars($row['image']) ?>"
                                        class="category-news-thumb"
                                        alt="news">

                                    <div class="category-news-info">

                                        <span class="category-news-label">Аналитика</span>

                                        <h3 class="category-news-title">
                                            <?= htmlspecialchars($row['title']) ?>
                                        </h3>

                                        <div class="category-news-meta">
                                            <span>
                                                <i class="far fa-calendar-alt"></i>
                                                <?= date('d.m.Y', strtotime($row['created_at'])) ?>
                                            </span>

                                            <span>
                                                <i class="far fa-eye"></i>
                                                <?= $row['views'] ?>
                                            </span>

                                        </div>

                                    </div>

                                </a>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

            <!-- SIDEBAR -->
            <aside class="category-sidebar">

                <div class="sidebar-card">
                    <h3 class="sidebar-card-title">
                        Авторы в аналитике
                    </h3>

                    <div class="sidebar-authors">

                        <div class="sidebar-author">
                            <img src="https://i.pravatar.cc/60?img=1">
                            <div>
                                <strong>Пупкин Запупкин</strong>
                                <span>28 статей</span>
                            </div>
                        </div>

                        <div class="sidebar-author">
                            <img src="https://i.pravatar.cc/60?img=2">
                            <div>
                                <strong>Гена Петрович</strong>
                                <span>19 статей</span>
                            </div>
                        </div>

                        <div class="sidebar-author">
                            <img src="https://i.pravatar.cc/60?img=3">
                            <div>
                                <strong>Жанибек Бали</strong>
                                <span>15 статей</span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="telegram-box">
                    <h3>Будьте в курсе важных новостей</h3>
                    <p>Подписывайтесь на нашу рассылку</p>
                    <button>Подписаться</button>
                </div>

            </aside>

        </div>
    </main>

    <footer class="main-footer">
        <div class="container footer-content">
            <div class="footer-legal">
                <p id="certificate1">Свидетельство о постановке на учет №KZ05VFY00030397</p>
                <p id="certificate2">выдано 22.12.2020</p>
            </div>
            <div class="footer-nav-groups">
                <div class="footer-col"><a href="/pages/about.php" id="about_us">Про нас</a><a href="#" id="redaction">Редакция</a></div>
                <div class="footer-col"><a href="#" id="vacancy">Вакансии</a><a href="/pages/contacts.php" id="contact">Контакты</a></div>
                <div class="footer-col"><a href="#" id="advertisement">Реклама</a><a href="#" id="support">Поддержка</a></div>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                    <div class="footer-col">
                        <a href="/admin/admin.php" style="color: #009688; font-weight: bold;">Admin</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="footer-socials">
                <a href="#" class="social-icon">Instagram</a>
                <a href="#" class="social-icon">TikTok</a>
            </div>
        </div>
    </footer>
    <script src="/assets/js/lang.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>