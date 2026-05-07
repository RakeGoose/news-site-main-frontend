<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["success" => false, "message" => "Все поля обязательны"]);
    exit;
}

// Поиск пользователя в БД
$stmt = $conn->prepare("SELECT id, name, email, password, role, verified, force_password_change FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (!$user['verified']) {
        echo json_encode(["success" => false, "message" => "Почта не верифицирована. Проверьте почту!"]);
        exit;
    }
    if (password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
            'force_change' => $user['force_password_change']
        ];
        echo json_encode([
            "success" => true,
            "message" => "Подключение успешно",
            "force_change" => (int)$user['force_password_change']
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Неверный пароль"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Пользователь не найден"]);
}

$stmt->close();
$conn->close();
