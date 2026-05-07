<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: /auth/login.html");
    exit;
}

$categories = $conn->query("SELECT id, name FROM categories");
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Предложить новость - Logotip news</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <header class="main-header">
        <div class="container header-top">
            <div class="header-right">
                <a href="/auth/logout.php" class="auth-btn-black btn-small">Выйти</a>
            </div>
        </div>
        <div class="logo-container">
            <a href="/pages/index.php" style="text-decoration: none; color: inherit;">
                <h1 class="logo">Logotip news</h1>
            </a>
        </div>
    </header>

    <main class="container">
        <div class="author-info">
            <div class="author-avatar"><i class="fas fa-user-circle"></i></div>
            <span class="author-name"><?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
        </div>

        <form id="createNewsForm" action="/actions/news/save_news.php" method="POST" enctype="multipart/form-data" class="create-news-container">
            <div class="input-group-news">
                <select name="category_id" class="news-input" required>
                    <option value="">Выберите категорию *</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="lang-tabs">
                <div class="tab-btn active" onclick="showLang('ru')">Русский (RU)</div>
                <div class="tab-btn" onclick="showLang('kz')">Қазақша (KZ)</div>
                <div class="tab-btn" onclick="showLang('en')">English (EN)</div>
            </div>

            <?php foreach (['ru', 'kz', 'en'] as $lang): ?>
                <div id="lang-<?= $lang ?>" class="lang-section <?= $lang == 'ru' ? 'active' : '' ?>">
                    <div class="input-group-news">
                        <input type="text" name="title_<?= $lang ?>" class="news-input" placeholder="Заголовок (<?= strtoupper($lang) ?>) *" required>
                    </div>
                    <div class="input-area text-area-wrapper" style="height: 200px; margin-bottom: 20px;">
                        <textarea name="content_<?= $lang ?>" placeholder="Текст новости (<?= strtoupper($lang) ?>)... *" required></textarea>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="input-area photo-area-wrapper" style="height: 150px; margin-bottom: 20px;">
                <label for="newsPhoto" class="photo-upload-label">
                    <input type="file" name="newsPhoto" id="newsPhoto" accept="image/*" hidden required>
                    <span id="uploadStatus">Загрузить обязательное фото *</span>
                </label>
            </div>
            <div class="input-area photo-area-wrapper" style="height: 150px; margin-bottom: 20px; ">
                <label for="innerPhoto" class="photo-upload-label">
                    <input type="file" name="innerPhoto" id="innerPhoto" accept="image/*" hidden>
                    <span id="innerUploadStatus">Загрузить дополнительное фото *</span>
                </label>
            </div>

            <div class="form-footer">
                <button type="submit" class="auth-btn-black">Отправить в редакцию</button>
            </div>
        </form>
    </main>

    <script>
        function showLang(lang) {
            document.querySelectorAll('.lang-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

            const targetSection = document.getElementById('lang-' + lang);
            if (targetSection) targetSection.classList.add('active');

            // Находим кнопку по тексту или атрибуту и активируем
            document.querySelectorAll('.tab-btn').forEach(btn => {
                if (btn.getAttribute('onclick').includes(`'${lang}'`)) {
                    btn.classList.add('active');
                }
            });
        }

        $(document).ready(function() {
            $('#createNewsForm').on('submit', function(e) {
                // ПРОВЕРКА ВАЛИДНОСТИ
                if (!this.checkValidity()) {
                    // Если ошибка в скрытой вкладке, переключаемся на неё
                    const invalidField = $(this).find(':invalid').first();
                    const parentSection = invalidField.closest('.lang-section');
                    if (parentSection.length) {
                        const lang = parentSection.attr('id').replace('lang-', '');
                        showLang(lang);
                    }
                    return; // Браузер покажет "Заполните поле"
                }

                e.preventDefault();
                let formData = new FormData(this);
                let submitBtn = $(this).find('button[type="submit"]');

                submitBtn.prop('disabled', true).text('Отправка...');

                $.ajax({
                    type: "POST",
                    url: "/actions/news/save_news.php",
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            window.location.href = '/pages/index.php';
                        } else {
                            alert("Ошибка: " + response.message);
                            submitBtn.prop('disabled', false).text('Отправить в редакцию');
                        }
                    },
                    error: function() {
                        alert("Произошла ошибка при отправке данных.");
                        submitBtn.prop('disabled', false).text('Отправить в редакцию');
                    }
                });
            });

            $('#newsPhoto').change(function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const maxSize = 3 * 1024 * 1024; // 33 MB

                    if (file.size > maxSize) {
                        alert("Файл слишком большой! Максимальный размер — 3 МБ.");
                        this.value = "";
                        $('#uploadStatus').text("Ошибка: файл более 3 МБ").css("color", "red");
                        return;
                    }

                    $('#uploadStatus').text("Файл выбран: " + file.name).css("color", "#009688");
                }
            });
            $('#innerPhoto').change(function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const maxSize = 3 * 1024 * 1024; // 3 MB

                    if (file.size > maxSize) {
                        alert("Файл слишком большой! Максимальный размер — 3 МБ.");
                        this.value = "";
                        $('#innerUploadStatus').text("Ошибка: файл более 3 МБ").css("color", "red");
                        return;
                    }

                    $('#innerUploadStatus').text("Доп. файл выбран: " + file.name).css("color", "#4f46e5");
                }
            });
        });
    </script>
    <script src="/assets/js/lang.js"></script>
    <script src="/assets/js/main.js"></script>
</body>

</html>