<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Базовая защита: только для админов
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Content-Type: text/plain; charset=utf-8");
    die("Доступ запрещен: недостаточно прав.");
}

$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    switch ($action) {

        case 'approve':
            // Одобрение новости и установка времени публикации
            $stmt = $conn->prepare("UPDATE news SET status = 'approved', published_at = NOW() WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            break;

        case 'reject':
            // ПОЛНОЕ УДАЛЕНИЕ НОВОСТИ И ВСЕХ СВЯЗЕЙ
            $conn->begin_transaction();
            try {
                // 1. Удаляем комментарии к этой новости
                $stmt1 = $conn->prepare("DELETE FROM comments WHERE news_id = ?");
                $stmt1->bind_param("i", $id);
                $stmt1->execute();

                // 2. Удаляем дополнительные фото из галереи
                $stmt2 = $conn->prepare("DELETE FROM news_gallery WHERE news_id = ?");
                $stmt2->bind_param("i", $id);
                $stmt2->execute();

                // 3. Удаляем переводы новости
                $stmt3 = $conn->prepare("DELETE FROM news_translations WHERE news_id = ?");
                $stmt3->bind_param("i", $id);
                $stmt3->execute();

                // 4. Удаляем саму запись новости
                $stmt4 = $conn->prepare("DELETE FROM news WHERE id = ?");
                $stmt4->bind_param("i", $id);
                $stmt4->execute();

                $conn->commit();
            } catch (Exception $e) {
                $conn->rollback();
                error_log("Ошибка удаления новости ID $id: " . $e->getMessage());
            }
            break;
        case 'set_temp_password':
            $date_part = date('dmY');
            $plain_password = "Astana2026" . $date_part;

            $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET 
                password = ?, 
                force_password_change = 1, 
                verified = 1 
                WHERE id = ?");

            $stmt->bind_param("si", $hashed_password, $id);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Пароль для пользователя (ID $id) сброшен на: <b>дефолтный пароль</b>. Сообщите его пользователю.";
            } else {
                error_log("Ошибка сброса пароля пользователя ID $id: " . $conn->error);
            }
            break;

        case 'delete_user':
            // БЕЗОПАСНОЕ УДАЛЕНИЕ ПОЛЬЗОВАТЕЛЯ
            $conn->begin_transaction();
            try {
                // Чтобы новости не удалились вместе с автором (если нет каскада),
                // переписываем их на главного админа (ID 1)
                $new_author_id = 1;

                $stmt_move = $conn->prepare("UPDATE news SET author_id = ? WHERE author_id = ?");
                $stmt_move->bind_param("ii", $new_author_id, $id);
                $stmt_move->execute();

                // Теперь удаляем пользователя
                $stmt_user = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt_user->bind_param("i", $id);
                $stmt_user->execute();

                $conn->commit();
            } catch (Exception $e) {
                $conn->rollback();
                error_log("Ошибка удаления пользователя ID $id: " . $e->getMessage());
            }
            break;
    }
}

// Редактирование имени пользователя (через POST для безопасности)
if ($action === 'edit_user' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $new_name = trim($_POST['new_name'] ?? '');

    if ($user_id > 0 && !empty($new_name)) {
        $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $new_name, $user_id);
        $stmt->execute();
    }
}

header("Location: /admin/admin.php");
exit;
