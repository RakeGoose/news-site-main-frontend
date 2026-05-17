<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Защита: если пользователь не залогинен, отправляем на вход
if (!isset($_SESSION['user'])) {
    header("Location: /auth/login.html");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';
    
    // Регулярное выражение для надежного пароля
    $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    if (!preg_match($passwordRegex, $new_pass)) {
        $error = "Пароль слишком слабый! Нужно: 8+ символов, заглавная буква, цифра и спецсимвол.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "Пароли не совпадают.";
    } else {
        $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
        $user_id = $_SESSION['user']['id'];

        $stmt = $conn->prepare("UPDATE users SET password = ?, force_password_change = 0 WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);

        if ($stmt->execute()) {
            // Обновляем сессию, чтобы пускало на сайт
            $_SESSION['user']['force_change'] = 0; 
            
            $success = "Пароль успешно обновлен! Сейчас вы будете перенаправлены...";
            
            // Редирект через 2 секунды
            header("Refresh: 2; url=/pages/index.php");
        } else {
            $error = "Ошибка при сохранении в базу данных.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Смена пароля - Logotip news</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-logo">
                <a href="/pages/index.php">Logotip news</a>
            </div>

            <h1 class="auth-title">Новый пароль</h1>
            <p class="auth-subtitle">Пожалуйста, установите постоянный пароль.</p>

            <?php if ($error): ?>
                <p class="auth-message" style="color: #d9534f;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <?php if ($success): ?>
                <p class="auth-message" style="color: #009688;"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>

            <form action="" method="POST" class="auth-form">
                <div class="input-group">
                    <input type="password" name="new_password" placeholder="Новый пароль" required>
                </div>
                <div class="input-group">
                    <input type="password" name="confirm_password" placeholder="Повторите пароль" required>
                </div>

                <button type="submit" class="auth-btn-black">Сохранить пароль</button>
            </form>
        </div>
    </div>

    <script>
        // Скрыть уведомление об ошибке через 5 секунд
        setTimeout(() => {
            const msg = document.querySelector('.auth-message');
            if (msg && !msg.innerText.includes('успешно')) {
                msg.style.display = 'none';
            }
        }, 5000);
    </script>
    <script src="/assets/js/lang.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>