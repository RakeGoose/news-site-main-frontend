<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/mock_data.php';

header('Content-Type: application/json; charset=UTF-8');

$lang = $_SESSION['lang'] ?? 'ru';
$query = trim($_GET['q'] ?? '');

if (mb_strlen($query) < 2) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

$results = [];

foreach ($mockNews as $news) {
    if (
        ($news['status'] ?? '') !== 'approved'
        || ($news['language'] ?? 'ru') !== $lang
    ) {
        continue;
    }

    $title = $news['title'] ?? '';
    $content = $news['content'] ?? '';

    if (
        mb_stripos($title, $query) !== false
        || mb_stripos($content, $query) !== false
    ) {
        $results[] = [
            'id' => $news['id'],
            'title' => $title,
            'image' => !empty($news['image'])
                ? '/uploads/news/' . $news['image']
                : null,
        ];
    }

    if (count($results) >= 5) {
        break;
    }
}

echo json_encode($results, JSON_UNESCAPED_UNICODE);