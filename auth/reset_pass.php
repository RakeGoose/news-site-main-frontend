<?php
// Выключаем вывод ошибок в текст, чтобы не ломать JSON
ini_set('display_errors', 1); 
error_reporting(E_ALL);
header('Content-Type: application/json; charset=UTF-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php'; 

$email = trim($_POST['email'] ?? '');

if (!$email) {
    echo json_encode(["success" => false, "message" => "Введите Email!"]);
    exit;
}

$stmt = $conn->prepare("SELECT id, name, last_reset_request FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Сначала проверяем, есть ли пользователь
if (!$user) {
    echo json_encode(["success" => false, "message" => "Пользователь с таким Email не найден"]);
    exit;
}

// Только теперь, когда мы уверены, что $user существует, берем имя
$name = $user['name'];

// 5 min cooldown check
if ($user['last_reset_request']) {
    $lastRequest = strtotime($user['last_reset_request']);
    $diff = time() - $lastRequest;
    if ($diff < 300) {
        $minutesLeft = ceil((300 - $diff) / 60);
        echo json_encode([
            "success" => false,
            "message" => "Слишком часто! Попробуйте снова через $minutesLeft мин."
        ]);
        exit;
    }
}

// Функция генерации (без изменений)
function gen_password($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    return substr(str_shuffle($chars), 0, $length);
}

$newPass = gen_password(12);
$hashedPass = password_hash($newPass, PASSWORD_DEFAULT);

// Обновление БД
$updateStmt = $conn->prepare("UPDATE users SET password = ?, last_reset_request = NOW(), force_password_change = 1 WHERE id = ?");
$updateStmt->bind_param("si", $hashedPass, $user['id']);

if (!$updateStmt->execute()) {
    echo json_encode(["success" => false, "message" => "Ошибка базы данных"]);
    exit;
}

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USER'];
    $mail->Password = $_ENV['SMTP_PASS'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $_ENV['SMTP_PORT'];
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($_ENV['SMTP_USER'], $_ENV['SMTP_FROM_NAME']);
    $mail->addAddress($email, $name);

    $mail->Subject = "Восстановление доступа — Logotip News";
    $mail->Body = "Здравствуйте, $name!\n\n"
        . "Ваш новый временный пароль: $newPass\n\n"
        . "При входе система попросит вас сменить его на постоянный.\n\n"
        . "С уважением, Команда Logotip News";

    $mail->send();
    echo json_encode(["success" => true, "message" => "Новый пароль отправлен на ваш email!"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Ошибка почты: {$mail->ErrorInfo}"]);
}