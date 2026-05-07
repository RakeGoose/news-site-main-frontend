<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
    $news_id = (int)$_POST['news_id'];
    
    // 1. Сохраняем изменения переводов
    if (isset($_POST['trans_ids'])) {
        foreach ($_POST['trans_ids'] as $t_id) {
            $t_id = (int)$t_id;
            $new_title = $conn->real_escape_string($_POST['titles'][$t_id]);
            $new_content = $conn->real_escape_string($_POST['contents'][$t_id]);

            $conn->query("UPDATE news_translations SET title = '$new_title', content = '$new_content' WHERE id = $t_id");
        }
    }

    // 2. Узнаем текущий статус новости
    $res = $conn->query("SELECT status FROM news WHERE id = $news_id");
    $news = $res->fetch_assoc();

    // 3. Логика редиректа
    if ($news && $news['status'] === 'approved') {
        // Если уже одобрена — сразу в админку
        header("Location: /admin/admin.php?msg=success_edit");
    } else {
        // Если еще висит на проверке — возвращаемся к кнопкам одобрения
        header("Location: /admin/view_pending.php?id=" . $news_id);
    }
    exit;
} else {
    header("Location: /admin/admin.php");
    exit;
}