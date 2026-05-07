<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: /admin/admin.php");
    exit;
}

$id = (int)$_GET['id'];
$news = $conn->query("
    SELECT n.*, u.name as author 
    FROM news n 
    JOIN users u ON n.author_id = u.id 
    WHERE n.id = $id
")->fetch_assoc();

if (!$news) {
    echo "Новость не найдена или уже обработана.";
    exit;
}

$translations = $conn->query("SELECT * FROM news_translations WHERE news_id = $id");
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Просмотр новости #<?= $id ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; line-height: 1.6; }
        .card { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); }
        .btn-group { display: flex; gap: 10px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        .btn { padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; color: #fff; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .btn-app { background: #009688; }
        .btn-edit { background: #ff9800; }
        .btn-rej { background: #d9534f; }
        .lang-block { margin-bottom: 25px; padding: 15px; border: 1px solid #eee; border-radius: 8px; }
        .img-label { font-size: 0.8rem; color: #666; margin-bottom: 5px; display: block; text-transform: uppercase; font-weight: bold; }
        .img-preview { max-width: 100%; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ddd; }
        .inner-img { max-width: 300px; } /* Дополнительное фото чуть меньше в превью */
    </style>
</head>

<body>
    <div class="card">
        <a href="/admin/admin.php" style="text-decoration:none; color:#666;">← Назад в админку</a>
        <h1>Проверка новости #<?= $id ?></h1>
        <p>Автор: <b><?= htmlspecialchars($news['author']) ?></b> | Дата: <?= $news['created_at'] ?></p>

        <span class="img-label">Главная обложка:</span>
        <?php if ($news['image']): ?>
            <img src="<?= htmlspecialchars($news['image']) ?>" class="img-preview">
        <?php else: ?>
            <p style="color: red;">Ошибка: Главное фото отсутствует!</p>
        <?php endif; ?>

        <?php while ($t = $translations->fetch_assoc()): ?>
            <div class="lang-block">
                <span style="background:#eee; padding:2px 6px; border-radius:4px;"><?= strtoupper($t['language']) ?></span>
                <h2><?= htmlspecialchars($t['title']) ?></h2>
                <div style="white-space: pre-wrap;"><?= htmlspecialchars($t['content']) ?></div>
            </div>
        <?php endwhile; ?>

        <div style="margin-top: 20px;">
            <span class="img-label">Фото внутри текста (дополнительное):</span>
            <?php if (!empty($news['inner_image'])): ?>
                <img src="<?= htmlspecialchars($news['inner_image']) ?>" class="img-preview inner-img">
            <?php else: ?>
                <p style="color: #999; font-style: italic;">Дополнительное фото не приложено.</p>
            <?php endif; ?>
        </div>

        <div class="btn-group">
            <?php if ($news['status'] === 'pending'): ?>
                <a href="/admin/admin_actions.php?action=approve&id=<?= $id ?>" class="btn btn-app">
                    <i class="fas fa-check"></i> Одобрить выпуск
                </a>
            <?php endif; ?>

            <a href="/admin/edit_news.php?id=<?= $id ?>" class="btn btn-edit">
                <i class="fas fa-pen"></i> Редактировать
            </a>

            <a href="admin_actions.php?action=reject&id=<?= $id ?>" class="btn btn-rej" onclick="return confirm('Вы уверены? Это полностью удалит новость.')">
                <i class="fas fa-trash"></i> <?= ($news['status'] === 'approved') ? 'Удалить новость' : 'Отклонить' ?>
            </a>
        </div>
    </div>
</body>
</html>