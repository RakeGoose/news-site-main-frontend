<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mock_data.php';

$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$lang = $_SESSION['lang'] ?? 'ru';

if ($news_id <= 0) {
    header("Location: /pages/index.php");
    exit;
}

$article = null;

foreach ($mockNews as $news) {
    if (
            (int)$news['id'] === $news_id
            && ($news['language'] ?? 'ru') === $lang
            && ($news['status'] ?? '') === 'approved'
    ) {
        $article = $news;
        break;
    }
}

if (!$article) {
    die("Статья не найдена или еще не одобрена.");
}

$article['views'] = ($article['views'] ?? 0) + 1;

$mockComments = [
        1 => [
                [
                        'name' => 'Мария Ким',
                        'content' => 'Хороший материал, удобно читать.',
                        'created_at' => '2026-05-09 13:20:00',
                ],
                [
                        'name' => 'Рустем Ахметов',
                        'content' => 'Нужно добавить больше деталей по теме.',
                        'created_at' => '2026-05-09 14:05:00',
                ],
        ],
        2 => [
                [
                        'name' => 'Дана Омарова',
                        'content' => 'Интересная спортивная новость.',
                        'created_at' => '2026-05-08 19:00:00',
                ],
        ],
];

$comments = $mockComments[$news_id] ?? [];
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

    <article class="article-container">
        <a href="javascript:history.back()" class="back-link">← Назад</a>

        <header class="article-header">
            <span class="article-category"><?= htmlspecialchars($article['cat_name']) ?></span>
            <h1 class="article-title"><?= htmlspecialchars($article['title']) ?></h1>
            <div class="article-meta">
                <span><i class="far fa-user"></i> <?= htmlspecialchars($article['author_name']) ?></span>
                <span><i class="far fa-calendar"></i> <?= date('d.m.Y H:i', strtotime($article['created_at'])) ?></span>
                <span><i class="far fa-eye"></i> <?= $article['views'] ?></span>
            </div>
        </header>

        <?php if ($article['image']): ?>
            <img src="/uploads/news/<?= htmlspecialchars($article['image']) ?>" alt="Main Photo" class="article-main-img">
        <?php endif; ?>

        <div class="article-content">
            <?php
            $content = $article['content'];
            $inner_image = $article['inner_image'];

            if ($inner_image) {
                // Пытаемся разделить текст примерно пополам
                $length = mb_strlen($content);
                $half = floor($length / 2);

                // Ищем ближайшую точку после середины
                $breakpoint = mb_strpos($content, ". ", $half);

                if ($breakpoint !== false) {
                    $first_part = mb_substr($content, 0, $breakpoint + 1);
                    $second_part = mb_substr($content, $breakpoint + 1);

                    echo nl2br(htmlspecialchars($first_part));
                    echo '<img src="/uploads/news/' . htmlspecialchars($inner_image) . '" alt="Inner Photo" class="article-main-img article-inner-img">';
                    echo nl2br(htmlspecialchars($second_part));
                } else {
                    // Если точку не нашли, просто выводим текст и фото под ним
                    echo nl2br(htmlspecialchars($content));
                    echo '<img src="/uploads/news/' . htmlspecialchars($inner_image) . '" alt="Inner Photo" class="article-main-img article-inner-img">';
                }
            } else {
                echo nl2br(htmlspecialchars($content));
            }
            ?>
        </div>
    </article>

    <section class="article-container" id="comments">
        <hr style="border: 0; border-top: 2px solid #f7fafc; margin: 50px 0;">
        <h3 class="comments-title"><i class="far fa-comments"></i> Комментарии</h3>

        <div class="comment-form-container">
            <?php if (isset($_SESSION['user'])): ?>
                <form action="#" method="POST" onsubmit="alert('Комментарии временно отключены: сайт работает без базы данных.'); return false;">
                    <input type="hidden" name="news_id" value="<?= $news_id ?>">
                    <textarea name="comment_text" class="comment-textarea" placeholder="Ваш комментарий..." required></textarea>
                    <button type="submit" class="comment-submit">Отправить</button>
                </form>
            <?php else: ?>
                <p style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                    Чтобы оставить комментарий, <a href="/auth/login.html" style="color:#009688; font-weight:bold;">войдите</a>.
                </p>
            <?php endif; ?>
        </div>

        <div class="comments-list">
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $c): ?>
                    <div class="comment-item">
                        <span class="comment-date"><?= date('d.m.Y H:i', strtotime($c['created_at'])) ?></span>
                        <div class="comment-author"><?= htmlspecialchars($c['name']) ?></div>
                        <p style="color: #4a5568; margin-top: 5px;"><?= nl2br(htmlspecialchars($c['content'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #a0aec0; text-align: center; margin-top: 20px;">Пока нет комментариев.</p>
            <?php endif; ?>
        </div>
    </section>
    <script src="/assets/js/lang.js"></script>
    <script src="/assets/js/main.js"></script>
</body>

</html>