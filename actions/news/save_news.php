<?php
session_start();
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '60');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/db.php';

use ImageKit\ImageKit;

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Ошибка авторизации']);
    exit;
}

// Вспомогательная функция для обработки и загрузки в ImageKit
function processAndUpload($fileField, $imageKit)
{
    if (!isset($_FILES[$fileField]) || $_FILES[$fileField]['error'] !== 0) return null;

    $tmpPath = $_FILES[$fileField]['tmp_name'];
    $info = @getimagesize($tmpPath);
    if (!$info) throw new Exception("Файл $fileField не является изображением");

    // Создаем ресурс
    switch ($info['mime']) {
        case 'image/jpeg':
            $src = @imagecreatefromjpeg($tmpPath);
            break;
        case 'image/png':
            $src = @imagecreatefrompng($tmpPath);
            break;
        case 'image/webp':
            $src = @imagecreatefromwebp($tmpPath);
            break;
        default:
            throw new Exception("Формат " . $info['mime'] . " не поддерживается");
    }

    if (!$src) throw new Exception("Ошибка чтения файла $fileField");

    $width = imagesx($src);
    $height = imagesy($src);
    $max_width = 1200;

    if ($width > $max_width) {
        $new_width = $max_width;
        $new_height = floor($height * ($max_width / $width));
        $final_img = imagecreatetruecolor($new_width, $new_height);
        imagealphablending($final_img, false);
        imagesavealpha($final_img, true);
        imagecopyresampled($final_img, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    } else {
        $final_img = $src;
    }

    ob_start();
    imagejpeg($final_img, null, 85);
    $optimized_data = ob_get_clean();

    if ($final_img !== $src) imagedestroy($final_img);
    imagedestroy($src);

    $upload = $imageKit->uploadFile([
        'file' => base64_encode($optimized_data),
        'fileName' => $fileField . '_' . uniqid() . '.jpg',
        'folder' => '/news/'
    ]);

    if ($upload->error) throw new Exception("ImageKit Error: " . $upload->error->message);

    return $upload->result->url;
}

try {
    $imageKit = new ImageKit(
        $_ENV['IMAGEKIT_PUBLIC_KEY'],
        $_ENV['IMAGEKIT_PRIVATE_KEY'],
        $_ENV['IMAGEKIT_URL_ENDPOINT']
    );

    $author_id = $_SESSION['user']['id'];
    $category_id = $_POST['category_id'] ?? null;

    if (!$category_id) throw new Exception("Категория не выбрана");

    // 1. Обработка ГЛАВНОГО фото (обязательно)
    if (!isset($_FILES['newsPhoto']) || $_FILES['newsPhoto']['error'] !== 0) {
        throw new Exception("Главное фото обязательно для загрузки");
    }
    $image_url = processAndUpload('newsPhoto', $imageKit);

    // 2. Обработка ВНУТРЕННЕГО фото (необязательно)
    $inner_image_url = null;
    if (isset($_FILES['innerPhoto']) && $_FILES['innerPhoto']['error'] === 0) {
        $inner_image_url = processAndUpload('innerPhoto', $imageKit);
    }

    // 3. Сохранение в БД
    $conn->begin_transaction();

    // Обновленный запрос с учетом колонки inner_image
    $stmt = $conn->prepare("INSERT INTO news (author_id, category_id, image, inner_image, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iiss", $author_id, $category_id, $image_url, $inner_image_url);
    $stmt->execute();

    $news_id = $conn->insert_id;

    $languages = ['ru', 'kz', 'en'];
    $stmt_trans = $conn->prepare("INSERT INTO news_translations (news_id, language, title, content) VALUES (?, ?, ?, ?)");

    foreach ($languages as $lang) {
        $title = trim($_POST["title_$lang"] ?? '');
        $content = trim($_POST["content_$lang"] ?? '');
        if (!empty($title) && !empty($content)) {
            $stmt_trans->bind_param("isss", $news_id, $lang, $title, $content);
            $stmt_trans->execute();
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Ваша новость отправлена модератору!']);
} catch (Exception $e) {
    if (isset($conn) && $conn->connect_errno == 0) $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
