<?php
require_once __DIR__ . '/../config/db.php';
session_start();

$lang = $_SESSION['lang'] ?? 'ru';
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (mb_strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$searchTerm = "%$query%";
$sql = "SELECT n.id, t.title, n.image 
        FROM news n 
        JOIN news_translations t ON n.id = t.news_id 
        WHERE n.status = 'approved' AND t.language = ? AND t.title LIKE ? 
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $lang, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$news = [];
while ($row = $result->fetch_assoc()) {
    $news[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'image' => $row['image'] ? $row['image'] . "?tr=w-50,h-50,cm-pad_resize" : null
    ];
}

header('Content-Type: application/json');
echo json_encode($news);