<?php
// 1. Используем строгую типизацию (хорошая практика)
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

try {
    // 2. Проверяем наличие файла .env перед загрузкой, чтобы избежать лишних Fatal Errors
    if (!file_exists(dirname(__DIR__) . '/.env')) {
        throw new Exception("Конфигурационный файл .env не найден.");
    }

    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();

    // 3. Используем проверку существования переменных (защита от "Notice: Undefined index")
    $host     = $_ENV['DB_HOST'] ?? 'localhost';
    $user     = $_ENV['DB_USER'] ?? '';
    $password = $_ENV['DB_PASS'] ?? '';
    $database = $_ENV['DB_NAME'] ?? '';

    // 4. Включаем режим исключений для MySQLi (теперь ошибки БД будут попадать в catch)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conn = new mysqli($host, $user, $password, $database);

    // Установка кодировки
    $conn->set_charset("utf8mb4");
} catch (Throwable $e) {
    // 5. Логируем ошибку в файл (если нужно), а пользователю отдаем только JSON или общее сообщение
    error_log($e->getMessage());

    // Если это AJAX запрос (как в твоем случае), лучше возвращать JSON
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code(500);
    die(json_encode([
        "success" => false,
        "message" => "Внутренняя ошибка сервера. Пожалуйста, попробуйте позже."
    ]));
}