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
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/layout.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/pages/home.css">
</head>

<body class="page-home">

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
        <!-- <nav class="main-nav">
            <div class="container nav-flex">
                <ul class="nav-links">
                    <li><a href="/index.php" class="active">Главная</a></li>
                    <li><a href="/pages/politics.php" id="politics">Политика</a></li>
                    <li><a href="/pages/analytics.php" id="analytics">Аналитика</a></li>
                    <li><a href="/pages/world.php" id="world">Мировые новости</a></li>
                    <li><a href="/pages/showbiz.php" id="showbiz">Шоу-бизнес</a></li>
                    <li><a href="/pages/sport.php" id="sports">Спорт</a></li>
                    <li><a href="/pages/feed.php" id="my_lenta">Моя лента</a></li>
                </ul>
            </div>
        </nav> -->

        <div class="container padding_786">
                <nav class="navbar navbar-toggleable-md navbar-light">
                    <button class="navbar-toggler navbar-toggler-right mt-3" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" 
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="fa fa-bars">☰</span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item active">
                                <a class="nav-link" id="home">Главная</a>
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

            <section class="hero-section">
                <div class="container">
                    <div class="hero-carousel">
                        <?php 
                        $carousel_news = array_slice($news_result, 0, 3);
                        foreach($carousel_news as $index => $item): 
                        ?>
                        <div class="hero-item <?= $index === 0 ? 'active' : '' ?>">
                            <div class="hero-content">
                                <span class="hero-category"><?= htmlspecialchars($item['category_name']) ?></span>
                                <h2 class="hero-title"><?= htmlspecialchars($item['title']) ?></h2>
                                <p class="hero-excerpt"><?= mb_strimwidth(strip_tags($item['content']), 0, 200, "...") ?></p>
                                <div class="hero-meta">
                                    <span class="hero-meta-item hero-date"><?= date('d.m.Y, H:i', strtotime($item['created_at'])) ?></span>
                                    <span class="hero-meta-item hero-views">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
                                        stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/></svg>
                                        <?= $item['views'] ?>
                                    </span>
                                    <span class="hero-meta-item hero-comments">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" 
                                        stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                        <?= $mockCommentsCount[$item['id']] ?? 0 ?>
                                    </span>
                                </div>
                            </div>
                            <div class="hero-image-wrapper">
                                <img src="/uploads/news/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="hero-img">
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="carousel-nav">
                            <button class="carousel-prev" aria-label="Previous">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" 
                                stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                            </button>
                            <button class="carousel-next" aria-label="Next">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" 
                                stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                            </button>
                        </div>

                        <div class="carousel-dots">
                            <?php foreach($carousel_news as $index => $item): ?>
                                <span class="dot <?= $index === 0 ? 'active' : '' ?>"></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>


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
                        <?php 
                        $main_news_items = array_slice($news_result, 0, 3);
                        foreach ($main_news_items as $item):
                        ?>
                        <!-- Main News Card -->
                        <article class="featured-news-card">
                            <a href="/pages/article.php?id=<?= $item['id'] ?>" class="featured-img-wrapper">
                                <img src="/uploads/news/<?= htmlspecialchars($item['image']) ?>" alt="" class="featured-img">
                                <span class="category-badge-overlay"><?= htmlspecialchars($item['category_name']) ?></span>
                            </a>
                            <div class="featured-content">
                                <h2 class="featured-title">
                                    <a href="/pages/article.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a>
                                </h2>
                                <p class="featured-excerpt">
                                    <?= mb_strimwidth(strip_tags($item['content']), 0, 250, "...") ?>
                                </p>
                                <div class="featured-footer">
                                    <div class="footer-left">
                                        <span class="meta-item"><?= date('d.m.Y, H:i', strtotime($item['created_at'])) ?></span>
                                        <span class="meta-item">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
                                            stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <?= $item['views'] ?>
                                        </span>
                                        <span class="meta-item">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                            <?= $mockCommentsCount[$item['id']] ?? 0 ?>
                                        </span>
                                    </div>
                                    <div class="footer-right">
                                        <button class="bookmark-btn" aria-label="Save">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                                        </button>
                                    </div>
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
                                            <div class="side-news-content">
                                                <p class="side-news-title"><?= htmlspecialchars($side_row['title']) ?></p>
                                                <span class="side-news-date"><?= date('d.m.Y, H:i', strtotime($side_row['created_at'])) ?></span>
                                            </div>
                                            <?php if ($side_row['image']): ?>
                                                <img src="/uploads/news/<?= htmlspecialchars($side_row['image']) ?>" class="side-news-thumb">
                                            <?php endif; ?>
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

            <!-- Other News Section (Full Width) -->
            <?php if (!empty($news_result)): 
                $other_news = array_slice($news_result, 3, 5);
                if (!empty($other_news)):
            ?>
            <div class="other-news-wrapper">
                <div class="container">
                    <section class="other-news-section-full">
                        <div class="section-header">
                            <h3 class="section-title">Другие новости</h3>
                            <div class="section-nav">
                                <button class="nav-btn prev"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                 <polyline points="15 18 9 12 15 6"/></svg></button>
                                <button class="nav-btn next"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" 
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"/></svg></button>
                            </div>
                        </div>
                        <div class="other-news-grid">
                            <?php foreach ($other_news as $item): ?>
                                <article class="small-news-card">
                                    <a href="/pages/article.php?id=<?= $item['id'] ?>" class="small-img-wrapper">
                                        <img src="/uploads/news/<?= htmlspecialchars($item['image']) ?>" alt="" class="small-img">
                                        <span class="category-badge-small"><?= htmlspecialchars($item['category_name']) ?></span>
                                    </a>
                                    <div class="small-content">
                                        <h4 class="small-title">
                                            <a href="/pages/article.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a>
                                        </h4>
                                        <span class="small-date"><?= date('d.m.Y, H:i', strtotime($item['created_at'])) ?></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
            </div>
            <?php endif; endif; ?>

    <footer class="main-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <div class="footer-logo">LOGOTIP NEWS</div>
                <p class="footer-tagline">Свежие новости и аналитика каждый день в вашем распоряжении.</p>
                <div class="footer-socials">
                    <a href="#" class="social-link" aria-label="Instagram">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
                        stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                    </a>
                    <a href="#" class="social-link" aria-label="Telegram">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
                        stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13"/><path d="M22 2L15 22L11 13L2 9L22 2Z"/></svg>
                    </a>
                    <a href="#" class="social-link" aria-label="Facebook">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                </div>
            </div>
            
            <div class="footer-nav-section">
                <h4 class="footer-title">Компания</h4>
                <nav class="footer-links">
                    <a href="/pages/about.php">Про нас</a>
                    <a href="#">Редакция</a>
                    <a href="#">Вакансии</a>
                    <a href="/pages/contacts.php">Контакты</a>
                </nav>
            </div>

            <div class="footer-nav-section">
                <h4 class="footer-title">Информация</h4>
                <nav class="footer-links">
                    <a href="#">Реклама</a>
                    <a href="#">Поддержка</a>
                    <a href="#">Конфиденциальность</a>
                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                        <a href="/admin/admin.php" style="color: var(--color-accent); font-weight: bold;">Admin Panel</a>
                    <?php endif; ?>
                </nav>
            </div>

            <div class="footer-newsletter">
                <h4 class="footer-title">Подписка</h4>
                <p>Получайте лучшие новости и эксклюзивы на вашу почту.</p>
                <form class="newsletter-form" onsubmit="return false;">
                    <input type="email" placeholder="Email адрес" required>
                    <button type="submit" aria-label="Subscribe">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polyline points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container footer-bottom-content">
                <div class="footer-legal-info">
                    <p>Свидетельство о постановке на учет №KZ05VFY00030397 выдано 22.12.2020</p>
                </div>
                <p class="footer-copyright">© <?= date('Y') ?> Logotip News. Все права защищены.</p>
            </div>
        </div>
    </footer>
    <script src="/assets/js/lang.js"></script>
    <script src="/assets/js/main.js"></script>
</body>

</html>