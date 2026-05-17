<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/init_lang.php';

$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ru';

$sql = "SELECT n.id, n.image, n.views, n.created_at, t.title, t.content, c.name as category_name
        FROM news n
        JOIN categories c ON n.category_id = c.id
        JOIN news_translations t ON n.id = t.news_id
        WHERE n.status = 'approved'     
        AND t.language = ? 
        ORDER BY n.created_at DESC";



$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $lang);
$stmt->execute();
$news_result = $stmt->get_result();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        <div class="container padding_786">
                <nav class="navbar navbar-toggleable-md navbar-light">
                    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" 
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="fa-solid fa-bars"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item active">
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
                    
            <section class="hero-section">
                <div class="container">
                    <div class="hero-carousel">
                        <?php 
                        $carousel_news = array_slice($news_result, 0, 3);
                        foreach($carousel_news as $index => $item): 
                        ?>
                        <a href="/pages/article.php?id=<?= $item['id'] ?>" class="hero-item <?= $index === 0 ? 'active' : '' ?>">
                            <div class="hero-content">
                                <span class="hero-category"><?= htmlspecialchars($item['category_name']) ?></span>
                                <h2 class="hero-title"><?= htmlspecialchars($item['title']) ?></h2>
                                <p class="hero-excerpt"><?= mb_strimwidth(strip_tags($item['content']), 0, 200, "...") ?></p>
                                <div class="hero-meta">
                                    <span class="hero-date"><?= date('d.m.Y, H:i', strtotime($item['created_at'])) ?></span>
                                    <span class="hero-views">
                                        <i class="far fa-eye"></i>
                                        <?= $item['views'] ?>
                                    </span>
                                    <span class="hero-comments">
                                        <i class="far fa-comment"></i>
                                        <?= $mockCommentsCount[$item['id']] ?? 0 ?>
                                    </span>
                                </div>
                            </div>
                            <div class="hero-image-wrapper">
                                <img src="/uploads/news/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="hero-img">
                            </div>
                        </a>
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
                                            <i class="far fa-eye"></i>
                                            <?= $item['views'] ?>
                                        </span>
                                        <span class="meta-item">
                                            <i class="far fa-comment"></i>
                                            <?= $mockCommentsCount[$item['id']] ?? 0 ?>
                                        </span>
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
                $other_news = array_slice($news_result, 3, 10);
                if (!empty($other_news)): ?>
            <div class="other-news-wrapper">
                <div class="container">
                    <section class="other-news-section-full">
                        <div class="section-header">
                            <h3 class="section-title">Другие новости</h3>
                            <div class="section-nav">
                                <div class="section-dots" id="other-news-dots"></div>
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
                <h4 class="footer-title">Админ-панель</h4>
                <nav class="footer-links">
                    <a href="/admin/admin.php" style="color: var(--color-accent); font-weight: bold;">Управление сайтом</a>
                    <a href="/admin/admin_actions.php">Управление новостями</a>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </footer>
    <script src="/assets/js/lang.js"></script>
    <script src="/assets/js/main.js"></script>
</body>

</html>