<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/init_lang.php';
require_once __DIR__ . '/../config/mock_data.php';

$lang = $_SESSION['lang'] ?? 'ru';

$news_result = array_values(array_filter($mockNews, function ($news) use ($lang) {
    return ($news['status'] ?? '') === 'approved'
            && ($news['language'] ?? 'ru') === $lang;
}));

usort($news_result, function ($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});

$mockAuthors = [
        ['name' => 'Алихан Сейсенов'],
        ['name' => 'Мария Ким'],
        ['name' => 'Рустем Ахметов'],
        ['name' => 'Дана Омарова'],
        ['name' => 'Ерасыл Нурлан'],
];

$mockCommentsCount = [
        1 => 4,
        2 => 2,
        3 => 7,
];
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logotip news</title>
    <link rel="stylesheet" href="/assets/css/style.css">
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
            <h1 class="logo">Logotip news</h1>
        </div>

        <nav class="main-nav">
            <div class="container nav-flex">
                <a href="/admin/create.php" class="btn-suggest" id="suggest-news-btn">Предложить новость 📢</a>
                <ul class="nav-links">
                    <li><a href="/pages/politics.php" id="politics">Политика</a></li>
                    <li><a href="/pages/analytics.php" id="analytics">Аналитика</a></li>
                    <li><a href="/pages/world.php" id="world">Мировые новости</a></li>
                    <li><a href="/pages/showbiz.php" id="showbiz">Шоу-бизнес</a></li>
                    <li><a href="/pages/sport.php" id="sports">Спорт</a></li>
                    <li><a href="/pages/feed.php" id="my_lenta">Моя лента</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <div class="container main-layout">
        <aside class="sidebar-left">
            <div class="sidebar-box">
                <h3 class="sidebar-title" id="best-authors-title">Лучшие авторы месяца</h3>
                <ol class="author-list" id="best-authors-list">
                    <?php if (!empty($mockAuthors)): ?>
                        <?php foreach ($mockAuthors as $author):
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

        <main class="content-center">
            <?php if (!empty($news_result)): ?>
                <?php foreach ($news_result as $row):
                    $n_id = $row['id'];
                    // Получаем только кол-во комментариев
                    $comm_count = $mockCommentsCount[$n_id] ?? 0;
                ?>
                    <article class="news-card">
                        <a href="/pages/article.php?id=<?= $row['id'] ?>" class="news-link-container">
                            <?php if ($row['image']): ?>
                                <img src="/uploads/news/<?= htmlspecialchars($row['image']) ?>"
                                     class="news-main-img"
                                     loading="lazy">
                            <?php endif; ?>

                            <div class="news-info">
                                <span class="news-category-badge"><?= htmlspecialchars($row['category_name']) ?></span>
                                <h2 class="news-title"><?= htmlspecialchars($row['title']) ?></h2>
                                <p class="news-excerpt">
                                    <?= mb_strimwidth(strip_tags($row['content']), 0, 180, "...") ?>
                                </p>
                            </div>
                        </a>

                        <div class="news-footer">
                            <div class="news-interactions">
                                <a href="/pages/article.php?id=<?= $n_id ?>#comments" class="int-btn">
                                    <svg class="icon-svg" viewBox="0 0 24 24">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                    </svg>
                                    <span class="int-count"><?= $comm_count ?></span>
                                </a>

                                <div class="int-btn views-only">
                                    <svg class="icon-svg" viewBox="0 0 24 24">
                                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" />
                                    </svg>
                                    <span class="int-count"><?= $row['views'] ?></span>
                                </div>

                                <span class="news-date-right"><?= date('d.m.Y', strtotime($row['created_at'])) ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-news">Новостей пока нет.</div>
            <?php endif; ?>
        </main>

        <aside class="sidebar-right">
            <div class="sidebar-box side-news">
                <h3 class="sidebar-title" id="all-news">Все новости</h3>
                <div class="side-news-list">
                    <?php if (!empty($news_result)): ?>
                        <?php foreach (array_slice($news_result, 0, 5) as $side_row): ?>
                            <div class="side-news-item">
                                <a href="/pages/article.php?id=<?= $side_row['id'] ?>" class="side-news-link">
                                    <span class="side-news-date"><?= date('H:i', strtotime($side_row['created_at'])) ?></span>
                                    <p class="side-news-title"><?= htmlspecialchars($side_row['title']) ?></p>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-data">Новостей нет</p>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
    </div>

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