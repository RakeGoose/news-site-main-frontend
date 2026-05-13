<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ru';

if ($news_id <= 0) {
    header("Location: /pages/index.php");
    exit;
}

// Увеличиваем счетчик просмотров
$conn->query("UPDATE news SET views = views + 1 WHERE id = $news_id");

// Получаем данные статьи, переводы и автора
$sql = "SELECT n.*, t.title, t.content, u.name as author_name, c.name as cat_name
        FROM news n
        JOIN news_translations t ON n.id = t.news_id
        JOIN users u ON n.author_id = u.id
        JOIN categories c ON n.category_id = c.id
        WHERE n.id = ? AND t.language = ? AND n.status = 'approved'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $news_id, $lang);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    die("Статья не найдена или еще не одобрена.");
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title']) ?> | Logotip news</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        .article-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
            word-wrap: break-word;
        }

        .article-header {
            margin-bottom: 30px;
        }

        .article-category {
            color: #009688;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .article-title {
            font-size: 2.5rem;
            line-height: 1.2;
            margin: 10px 0;
            color: #1a1a1a;
        }

        .article-meta {
            color: #7f8c8d;
            font-size: 0.9rem;
            display: flex;
            gap: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }

        .article-main-img {
            width: 100%;
            border-radius: 15px;
            margin: 20px 0;
            object-fit: cover;
            max-height: 500px;
            display: block;
        }

        /* Стиль для фото внутри текста */
        .article-inner-img {
            margin: 35px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .article-content {
            font-size: 1.2rem;
            line-height: 1.8;
            color: #2c3e50;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #009688;
            text-decoration: none;
            font-weight: 500;
        }

        /* Комментарии */
        #comments {
            margin-top: 50px;
        }

        .comments-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .comment-textarea {
            width: 100%;
            min-height: 110px;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            margin-bottom: 10px;
            font-family: inherit;
        }

        .comment-submit {
            background: #009688;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .comment-item {
            border-bottom: 1px solid #f0f0f0;
            padding: 20px 0;
        }

        .comment-author {
            font-weight: 700;
            color: #2d3748;
        }

        .comment-date {
            color: #a0aec0;
            font-size: 0.8rem;
            float: right;
        }

        @media (max-width: 600px) {
            .article-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>

<body>

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
            <img src="<?= htmlspecialchars($article['image']) ?>" alt="Main Photo" class="article-main-img">
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
                    echo '<img src="' . htmlspecialchars($inner_image) . '" alt="Inner Photo" class="article-main-img article-inner-img">';
                    echo nl2br(htmlspecialchars($second_part));
                } else {
                    // Если точку не нашли, просто выводим текст и фото под ним
                    echo nl2br(htmlspecialchars($content));
                    echo '<img src="' . htmlspecialchars($inner_image) . '" alt="Inner Photo" class="article-main-img article-inner-img">';
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
                <form action="/actions/news/comment.php" method="POST">
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
            <?php
            $comm_stmt = $conn->prepare("SELECT c.content, c.created_at, u.name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.news_id = ? ORDER BY c.created_at DESC");
            $comm_stmt->bind_param("i", $news_id);
            $comm_stmt->execute();
            $comments = $comm_stmt->get_result();

            if ($comments->num_rows > 0):
                while ($c = $comments->fetch_assoc()): ?>
                    <div class="comment-item">
                        <span class="comment-date"><?= date('d.m.Y H:i', strtotime($c['created_at'])) ?></span>
                        <div class="comment-author"><?= htmlspecialchars($c['name']) ?></div>
                        <p style="color: #4a5568; margin-top: 5px;"><?= nl2br(htmlspecialchars($c['content'])) ?></p>
                    </div>
                <?php endwhile;
            else: ?>
                <p style="color: #a0aec0; text-align: center; margin-top: 20px;">Пока нет комментариев.</p>
            <?php endif; ?>
        </div>
    </section>
    <script src="/assets/js/lang.js"></script>
    <script src="/assets/js/main.js"></script>
</body>

</html>