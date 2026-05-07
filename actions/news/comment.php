<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Проверяем авторизацию и данные
if (!isset($_SESSION['user']) || !isset($_POST['news_id']) || empty(trim($_POST['comment_text']))) {
    header("Location: /pages/index.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$news_id = intval($_POST['news_id']);
$content = trim($_POST['comment_text']);

// Вставляем комментарий в БД
$stmt = $conn->prepare("INSERT INTO comments (news_id, user_id, content) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $news_id, $user_id, $content);

if ($stmt->execute()) {
    // Возвращаемся обратно к статье, к блоку комментариев
    header("Location: /pages/article.php?id=" . $news_id . "#comments");
} else {
    echo "Ошибка при добавлении комментария.";
}
exit;