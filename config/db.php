<?php
declare(strict_types=1);

/**
 * MOCK MODE
 * Сейчас база данных отсутствует, поэтому реальное подключение отключено.
 * Страницы должны брать данные из config/mock_data.php.
 */

$conn = null;
$isMockMode = true;