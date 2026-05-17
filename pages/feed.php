<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/init_lang.php';
$lang = $_SESSION['lang'] ?? 'ru';

$sql = "SELECT n.id, n.image, n.views, n.created_at, n.category_id, t.title, t.content, c.name as category_name
        FROM news n
        JOIN categories c ON n.category_id = c.id
        JOIN news_translations t ON n.id = t.news_id
        WHERE n.status = 'approved'
        AND t.language = ?
        ORDER BY n.created_at DESC
        LIMIT 20";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $lang);
$stmt->execute();
$result_obj = $stmt->get_result();

$result = [];
while ($row = $result_obj->fetch_assoc()) {
    $result[] = $row;
}

$commentsCount = [];
foreach ($result as $row) {
    $nid = (int)$row['id'];
    $c_sql = "SELECT COUNT(*) as count FROM comments WHERE news_id = ?";
    $c_stmt = $conn->prepare($c_sql);
    $c_stmt->bind_param("i", $nid);
    $c_stmt->execute();
    $c_res = $c_stmt->get_result();
    $c_row = $c_res->fetch_assoc();
    $commentsCount[$nid] = (int)$c_row['count'];
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="title_my_lenta">Моя лента | Logotip news</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/layout.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/pages/feed.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
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
                <input type="text" id="search-input" placeholder="Поиск по сайту">
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
            <?php if (!isset($_SESSION['user'])): ?>
                <div class="feed-auth-box">
                    <h2 id="lenta_auth_title">Ваша персональная лента</h2>
                    <p id="lenta_auth_text">Авторизуйтесь, чтобы видеть новости, подобранные специально для вас.</p>
                    <a href="/auth/login.html" class="auth-btn-black" id="login_to_feed" style="display: flex; justify-content: center; margin-top: 15px;">Войти</a>
                </div>
            <?php else: ?>
                <ul class="news-grid-list">
                    <?php if (!empty($result)): ?>
                        <?php foreach ($result as $row):
                            $n_id = $row['id'];
                            $comm_count = $commentsCount[$n_id] ?? 0;
                        ?>
                            <li class="news-item-card">
                                <a href="/pages/article.php?id=<?= $row['id'] ?>" class="news-link">
                                    <?php if ($row['image']): ?>
                                        <img src="<?= htmlspecialchars($row['image']) ?>"
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
                <h4 class="footer-title company">Компания</h4>
                <nav class="footer-links">
                    <a href="/pages/about.php" id="about_us">Про нас</a>
                    <a href="#" id="redaction">Редакция</a>
                    <a href="#" id="vacancy">Вакансии</a>
                </nav>
            </div>

            <div class="footer-nav-section">
                <h4 class="footer-title information">Информация</h4>
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