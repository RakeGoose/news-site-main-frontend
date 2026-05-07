<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$allowed_langs = ['ru', 'en', 'kz'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed_langs)) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
} else {
    $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ru';
}

// Если вдруг в сессии оказался мусор
if (!in_array($lang, $allowed_langs)) $lang = 'ru';
