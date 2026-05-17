<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: /admin/admin.php");
    exit;
}

$id = (int)$_GET['id'];
// Получаем саму новость
$news = $conn->query("SELECT * FROM news WHERE id = $id")->fetch_assoc();
if (!$news) {
    echo "Новость не найдена";
    exit;
}

// Получаем все её переводы
$translations = $conn->query("SELECT * FROM news_translations WHERE news_id = $id");
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Редактирование новости #<?= $id ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: sans-serif;
            background: #f4f7f6;
            padding: 20px;
        }

        .edit-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .lang-group {
            border-bottom: 2px solid #eee;
            margin-bottom: 20px;
            padding-bottom: 20px;
        }

        .lang-group:last-child {
            border: none;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            font-family: inherit;
        }

        textarea {
            height: 150px;
            resize: vertical;
        }

        .btn-save {
            background: #009688;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .badge {
            background: #000;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            font-size: 12px;
        }
    </style>
</head>

<body>

    <div class="edit-container">
        <a href="view_pending.php?id=<?= $id ?>" style="color: #666; text-decoration: none;">← Отмена</a>
        <h2>Редактирование перевода</h2>

        <form action="/actions/news/update_news_process.php" method="POST">
            <input type="hidden" name="news_id" value="<?= $id ?>">

            <?php while ($t = $translations->fetch_assoc()): ?>
                <div class="lang-group">
                    <span class="badge"><?= $t['language'] ?></span>
                    <input type="hidden" name="trans_ids[]" value="<?= $t['id'] ?>">

                    <div style="margin-top: 10px;">
                        <label>Заголовок:</label>
                        <input type="text" name="titles[<?= $t['id'] ?>]" value="<?= htmlspecialchars($t['title']) ?>" required>

                        <label>Текст статьи:</label>
                        <textarea name="contents[<?= $t['id'] ?>]" required><?= htmlspecialchars($t['content']) ?></textarea>
                    </div>
                </div>
            <?php endwhile; ?>

            <button type="submit" class="btn-save">Сохранить изменения</button>
            <a href="/admin/admin_actions.php?action=reject&id=<?= $id ?>"
                onclick="return confirm('ВНИМАНИЕ! Вы уверены, что хотите полностью удалить эту новость и её изображение?')"
                style="color: #d9534f; text-decoration: none; font-size: 14px; font-weight: bold; padding: 10px 15px; border: 1px solid #d9534f; border-radius: 5px;">
                <i class="fas fa-trash"></i> Удалить новость
            </a>
        </form>
    </div>

</body>

</html>