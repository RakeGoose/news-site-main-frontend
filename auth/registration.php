<?php
header('Content-Type: application/json');
ob_start(); 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php'; 

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$bio = trim($_POST['bio'] ?? '');       

$role = 'author'; 
$rating = 0;      // Начальный рейтинг
$verified = 0;

if (!$name || !$email || !$password) {
    echo json_encode(["success" => false, "message" => "Заполните основные поля"]);
    exit;
}

// Проверка Email
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Email занят"]);
    exit;
}

$passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

if (!preg_match($passwordRegex, $password)) {
    echo json_encode([
        "success" => false, 
        "message" => "Пароль слишком слабый! Требования: минимум 8 символов, заглавная буква, цифра и спецсимвол (@$!%*?&)."
    ]);
    exit;
}

$verifCode = (string)random_int(100000, 999999);

// Отправка почты (PHPMailer)
$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug = 0; 
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USER'];
    $mail->Password = $_ENV['SMTP_PASS'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $_ENV['SMTP_PORT'];
    $mail->setFrom($_ENV['SMTP_USER'], $_ENV['SMTP_FROM_NAME']);
    $mail->addAddress($email, $name);

    $mail->CharSet = 'UTF-8';
    $mail->Subject = "Код подтверждения регистрации";
    
    // Чистый текстовый формат сообщения
    $mail->Body = "Здравствуйте, $name!\n\n"
        . "Ваш код подтверждения для регистрации на портале Logotip News: $verifCode\n\n"
        . "Пожалуйста, введите этот код на странице подтверждения, чтобы активировать ваш аккаунт.\n\n"
        . "⚠️ БЕЗОПАСНОСТЬ:\n"
        . "- Никогда не передавайте этот код третьим лицам.\n"
        . "- Сотрудники редакции никогда не запрашивают ваш код.\n\n"
        . "Если вы не регистрировались на нашем сайте, просто проигнорируйте это письмо. "
        . "Ваш e-mail был указан ошибочно, и без ввода кода никакие данные не будут подтверждены.\n\n"
        . "С уважением,\nКоманда Logotip News";

    $mail->send();
}catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Ошибка почты"]);
    exit;
}

// Запись в базу (Добавляем bio, avatar, rating)
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Поля: name, email, password, role, verified, verification_code, bio, avatar, rating
$sql = "INSERT INTO users (name, email, password, role, verified, verification_code, bio, avatar, rating) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
// ssssisssi означает: string, string, string, string, int, string, string, string, int
$stmt->bind_param("ssssisssi", $name, $email, $hashedPassword, $role, $verified, $verifCode, $bio, $avatar, $rating);

if ($stmt->execute()) {
    ob_end_clean();
    echo json_encode(["success" => true, "message" => "Код отправлен!"]);
} else {
    ob_end_clean();
    echo json_encode(["success" => false, "message" => "Ошибка БД: " . $conn->error]);
}

$stmt->close();
$conn->close();