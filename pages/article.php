<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/init_lang.php';

$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$lang = $_SESSION['lang'] ?? 'ru';

if ($news_id <= 0) {
    header("Location: /pages/index.php");
    exit;
}

// ── Fetch the article from the real DB ──────────────────────────────────────
$sql = "SELECT n.*, t.title, t.content,
               c.name AS category_name,
               COALESCE(u.name, 'Редакция') AS author_name
        FROM news n
        JOIN categories c ON n.category_id = c.id
        JOIN news_translations t ON n.id = t.news_id AND t.language = ?
        LEFT JOIN users u ON n.author_id = u.id
        WHERE n.id = ? AND n.status = 'approved'
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $lang, $news_id);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();

if (!$article) {
    die("Статья не найдена или еще не одобрена.");
}

// ── Increment view count in DB ───────────────────────────────────────────────
$conn->query("UPDATE news SET views = views + 1 WHERE id = $news_id");
$article['views'] = (int)$article['views'] + 1;

// ── Fetch real comments ──────────────────────────────────────────────────────
$comments_sql = "SELECT u.name, cm.content, cm.created_at
                 FROM comments cm
                 LEFT JOIN users u ON cm.user_id = u.id
                 WHERE cm.news_id = ?
                 ORDER BY cm.created_at ASC";
$cstmt = $conn->prepare($comments_sql);
$cstmt->bind_param("i", $news_id);
$cstmt->execute();
$comments_result = $cstmt->get_result();
$comments = [];
while ($c = $comments_result->fetch_assoc()) {
    $comments[] = $c;
}

// ── Fetch similar articles (same category, excluding current) ────────────────
$sim_sql = "SELECT n.id, n.image, n.created_at, t.title
            FROM news n
            JOIN news_translations t ON n.id = t.news_id AND t.language = ?
            WHERE n.category_id = ? AND n.status = 'approved' AND n.id != ?
            ORDER BY n.created_at DESC
            LIMIT 5";
$sstmt = $conn->prepare($sim_sql);
$sstmt->bind_param("sii", $lang, $article['category_id'], $news_id);
$sstmt->execute();
$sim_result = $sstmt->get_result();
$similar_articles = [];
while ($s = $sim_result->fetch_assoc()) {
    $similar_articles[] = $s;
}

?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title']) ?> | Logotip news</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/layout.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/pages/article.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="page-article">

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
                        <li class="nav-item <?= $article['category_id'] == 1 ? 'active' : '' ?>">
                            <a class="nav-link" href="/pages/politics.php" id="politics">Политика</a>
                        </li>
                        <li class="nav-item <?= $article['category_id'] == 2 ? 'active' : '' ?>">
                            <a class="nav-link" href="/pages/analytics.php" id="analytics">Аналитика</a>
                        </li>
                        <li class="nav-item <?= $article['category_id'] == 3 ? 'active' : '' ?>">
                            <a class="nav-link" href="/pages/world.php" id="world">Мировые новости</a>
                        </li>
                        <li class="nav-item <?= $article['category_id'] == 4 ? 'active' : '' ?>">
                            <a class="nav-link" href="/pages/showbiz.php" id="showbiz">Шоу-бизнес</a>
                        </li>
                        <li class="nav-item <?= $article['category_id'] == 5 ? 'active' : '' ?>">
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

    <main class="container article-layout">
        <div class="article-main-content">
            <a href="javascript:history.back()" class="back-link"><i class="fas fa-arrow-left"></i> Назад</a>

            <header class="article-header">
                <span class="article-category"><?= htmlspecialchars($article['category_name']) ?></span>
                <h1 class="article-title"><?= htmlspecialchars($article['title']) ?></h1>
                <div class="article-meta">
                    <span class="meta-item"><i class="far fa-user"></i> <?= htmlspecialchars($article['author_name']) ?></span>
                    <span class="meta-item"><i class="far fa-calendar"></i> <?= date('d.m.Y, H:i', strtotime($article['created_at'])) ?></span>
                    <span class="meta-item"><i class="far fa-eye"></i> <?= $article['views'] ?></span>
                </div>
            </header>

            <?php if ($article['image']): ?>
                <div class="article-img-wrapper">
                    <img src="<?= htmlspecialchars($article['image']) ?>" alt="Main Photo" class="article-main-img">
                </div>
            <?php endif; ?>

            <div class="article-body">
                <?php
                $content = $article['content'];
                $inner_image = $article['inner_image'] ?? null;

                if ($inner_image) {
                    $length = mb_strlen($content);
                    $half = floor($length / 2);
                    $breakpoint = mb_strpos($content, ". ", $half);

                    if ($breakpoint !== false) {
                        $first_part = mb_substr($content, 0, $breakpoint + 1);
                        $second_part = mb_substr($content, $breakpoint + 1);

                        echo nl2br(htmlspecialchars($first_part));
                        echo '<img src="/uploads/news/' . htmlspecialchars($inner_image) . '" alt="Inner Photo" class="article-inner-img">';
                        echo nl2br(htmlspecialchars($second_part));
                    } else {
                        echo nl2br(htmlspecialchars($content));
                        echo '<img src="/uploads/news/' . htmlspecialchars($inner_image) . '" alt="Inner Photo" class="article-inner-img">';
                    }
                } else {
                    echo nl2br(htmlspecialchars($content));
                }
                ?>
            </div>

            <section id="comments" class="comments-section">
                <h3 class="comments-title">Комментарии (<?= count($comments) ?>)</h3>
                
                <div class="comment-form">
                    <?php if (isset($_SESSION['user'])): ?>
                        <form action="/actions/news/comment.php" method="POST">
                            <input type="hidden" name="news_id" value="<?= $news_id ?>">
                            <textarea name="comment_text" placeholder="Напишите ваш комментарий..." required></textarea>
                            <div class="comment-form-footer">
                                <button type="submit" class="btn-submit">Отправить</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="login-to-comment">
                            Чтобы оставить комментарий, &nbsp; <a href="/auth/login.html">войдите</a>.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="comments-list">
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item">
                            <div class="comment-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="comment-content">
                                <div class="comment-header">
                                    <span class="comment-author"><?= htmlspecialchars($comment['name']) ?></span>
                                    <span class="comment-date"><?= date('d.m.Y, H:i', strtotime($comment['created_at'])) ?></span>
                                </div>
                                <p class="comment-text"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>  
        </div>

        <aside class="article-sidebar">
            <div class="sidebar-box side-news">
                <h3 class="sidebar-title">Похожие материалы <span class="dot-accent"></span></h3>
                <div class="side-news-list">
                    <?php if (!empty($similar_articles)): ?>
                        <?php foreach ($similar_articles as $sim): ?>
                            <div class="side-news-item">
                                <a href="/pages/article.php?id=<?= $sim['id'] ?>" class="side-news-link">
                                    <div class="side-news-content">
                                        <p class="side-news-title"><?= htmlspecialchars($sim['title']) ?></p>
                                        <span class="side-news-date"><?= date('d.m.Y, H:i', strtotime($sim['created_at'])) ?></span>
                                    </div>
                                    <?php if ($sim['image']): ?>
                                        <img src="<?= htmlspecialchars($sim['image']) ?>" class="side-news-thumb">
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-data">Похожих новостей нет</p>
                    <?php endif; ?>
                </div>
                <a href="/pages/index.php" class="view-all-link">Все материалы <i class="fas fa-arrow-right"></i></a>
            </div>
        </aside>
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