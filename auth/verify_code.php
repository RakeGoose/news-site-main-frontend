<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Недопустимый метод запроса']);
    exit;
}

// Получаем данные из AJAX
$email = trim($_POST['email'] ?? '');
$code = trim($_POST['code'] ?? '');

if (!$email || !$code) {
    echo json_encode(["success" => false, "message" => "Пожалуйста, заполните все поля"]);
    exit;
}

// 1. Ищем пользователя с таким email в базе данных
$stmt = $conn->prepare("SELECT verification_code, verified FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    // 2. Проверяем, не подтвержден ли он уже
    if ($user['verified'] == 1) {
        echo json_encode(['success' => true, 'message' => 'Ваш Email уже подтвержден!']);
        exit;
    }

    // 3. Сверяем код (в БД это колонка verification_code)
    if ($user['verification_code'] === $code) {
        // 4. Обновляем статус в базе: ставим verified = 1 и очищаем код
        $updateStmt = $conn->prepare("UPDATE users SET verified = 1, verification_code = NULL WHERE email = ?");
        $updateStmt->bind_param("s", $email);

        if ($updateStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Email успешно подтвержден!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Произошла системная ошибка при обновлении']);
        }
        $updateStmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Неверный код подтверждения']);
    }
} else {
    echo json_encode(['success' => false, 'message' => "Пользователь с таким Email не найден"]);
}

$stmt->close();
$conn->close();
