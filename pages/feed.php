<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/init_lang.php';
require_once __DIR__ . '/../config/mock_data.php';

$lang = $_SESSION['lang'] ?? 'ru';

$result = array_values(array_filter($mockNews, function ($news) use ($lang) {
    return ($news['status'] ?? '') === 'approved'
            && ($news['language'] ?? 'ru') === $lang;
}));

usort($result, function ($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});

$result = array_slice($result, 0, 20);

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
    <title id="unit">Моя лента | Logotip news</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/layout.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/pages/feed.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="page-feed">
    <header class="main-header">
        <div class="container header-top-row">
            <div class="header-empty"></div>
            <div class="logo-container">
                <h1 class="logo">Logotip news</h1>
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
                            <a href="#kz" class="lang-item">kaz</a>
                            <a href="#ru" class="lang-item">rus</a>
                            <a href="#en" class="lang-item">eng</a>
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
                <input type="text" placeholder="Поиск по сайту">
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

    <main class="section">
        <div class="container">
            <?php if (false): ?>
                <div class="feed-auth-box">
                    <h2 id="lenta_auth_title">Ваша персональная лента</h2>
                    <p id="lenta_auth_text">Авторизуйтесь, чтобы видеть новости, подобранные специально для вас.</p>
                    <a href="/auth/login.html" class="auth-btn-black" style="display: inline-block; margin-top: 15px;">Войти</a>
                </div>
            <?php else: ?>
                <ul class="news-grid-list">
                    <?php if (!empty($result)): ?>
                        <?php foreach ($result as $row):
                            $n_id = $row['id'];
                            $comm_count = $mockCommentsCount[$n_id] ?? 0;
                        ?>
                            <li class="news-item-card">
                                <a href="/pages/article.php?id=<?= $row['id'] ?>" class="news-link">
                                    <?php if ($row['image']): ?>
                                        <img src="/uploads/news/<?= htmlspecialchars($row['image']) ?>"
                                             class="news-item__img"
                                             loading="lazy">
                                <?php endif; ?>
                                    <h3 class="news-item__title"><?= htmlspecialchars($row['title']) ?></h3>
                                    <p class="news-item__excerpt">
                                        <?= mb_strimwidth(strip_tags($row['content']), 0, 130, "...") ?>
                                    </p>
                                </a>
                                <div class="news-item__footer" style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid #eee; margin-top: auto;">
                                    <div class="stats" style="display: flex; gap: 15px;">
                                        <span title="Комментарии" style="color: #666; font-size: 0.85rem; display: flex; align-items: center; gap: 5px;">
                                            <i class="far fa-comment"></i> <?= $comm_count ?>
                                        </span>
                                        <span title="Просмотры" style="color: #666; font-size: 0.85rem; display: flex; align-items: center; gap: 5px;">
                                            <i class="far fa-eye"></i> <?= $row['views'] ?>
                                        </span>
                                    </div>
                                    <span style="color: #888; font-size: 0.85rem;">
                                        <i class="far fa-calendar-alt"></i> <?= date('d.m.Y', strtotime($row['created_at'])) ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-news" id="no_news_found">В вашей ленте пока пусто.</div>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
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
                    <div class="footer-col"><a href="/admin/admin.php" style="color: #009688; font-weight: bold;">Admin</a></div>
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