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
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
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

    <main class="section">
        <div class="container">
            <ul class="news-grid-list">
                <?php if (!empty($result)): ?>
                    <?php foreach ($result as $row):
                        $n_id = $row['id'];
                        $comm_count = $mockCommentsCount[$n_id] ?? 0;
                    ?>
                        <li class="news-item-card">
                            <a href="/pages/article.php?id=<?= $row['id'] ?>" class="news-link">
                                <img src="/uploads/news/<?= htmlspecialchars($row['image'] ?: 'news_69bfb8a8c3a62.jpeg') ?>" alt="img" class="news-item__img" loading="lazy">

                                <h3 class="news-item__title">
                                    <?= htmlspecialchars($row['title']) ?>
                                </h3>

                                <p class="news-item__excerpt">
                                    <?= mb_strimwidth(strip_tags($row['content']), 0, 130, "...") ?>
                                </p>
                            </a>
                            
                            <div class="news-item__footer" style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid #f0f0f0; margin-top: auto;">
                                <div class="news-stats" style="display: flex; gap: 15px;">
                                    <span title="Комментарии" style="color: #7f8c8d; font-size: 0.85rem; display: flex; align-items: center; gap: 5px;">
                                        <i class="far fa-comment"></i> <?= $comm_count ?>
                                    </span>
                                    <span title="Просмотры" style="color: #7f8c8d; font-size: 0.85rem; display: flex; align-items: center; gap: 5px;">
                                        <i class="far fa-eye"></i> <?= $row['views'] ?>
                                    </span>
                                </div>
                                <span style="color: #95a5a6; font-size: 0.85rem;">
                                    <i class="far fa-calendar-alt"></i> <?= date('d.m.Y', strtotime($row['created_at'])) ?>
                                </span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-news" id="no_news_found">Пока нет аналитических новостей на выбранном языке.</div>
                <?php endif; ?>
            </ul>
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