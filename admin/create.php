<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/init_lang.php';

if (!isset($_SESSION['user'])) {
    header("Location: /auth/login.html");
    exit;
}
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ru';
$categories = $conn->query("SELECT id, name FROM categories");
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Предложить новость - Logotip news</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/layout.css">
    <link rel="stylesheet" href="/assets/css/components.css">
    <link rel="stylesheet" href="/assets/css/pages/submit-news.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="page-submit-news">
    <header class="main-header">
        <div class="container header-top-row">
            <div class="header-empty"></div>
            <div class="logo-container">
                <h1 class="logo">Logotip news</h1>
            </div>
            <div class="header-actions">
                <div class="lang-switcher">
                    <div class="lang-dropdown">
                        <button class="lang-btn">
                            <span class="lang-text"><?= $lang ?></span>
                            <span class="lang-arrow">▼</span>
                        </button>
                        <div class="lang-list">
                            <a href="#kz" class="lang-item">KZ</a>
                            <a href="#ru" class="lang-item">RU</a>
                            <a href="#en" class="lang-item">EN</a>
                        </div>
                    </div>
                </div>
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="/auth/logout.php" class="auth-btn-black" id="logout">Выйти</a>
                <?php else: ?>
                    <a href="/auth/login.html" class="auth-btn-black" id="login">Авторизоваться</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="container header-search-row">
            <div class="search-bar">
                <input type="text" class="search-input" placeholder="Поиск по сайту">
            </div>
        </div>

        <div class="container padding_786">
            <nav class="navbar navbar-toggleable-md navbar-light">
                <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="fa-solid fa-bars"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item active">
                            <a class="nav-link" href="/pages/index.php" id="home">Главная</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/politics.php" id="politics">Политика</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/analytics.php" id="analytics">Аналитика</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/world.php" id="world">Мировые новости</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/showbiz.php" id="showbiz">Шоу-бизнес</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/sport.php" id="sports">Спорт</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/feed.php" id="my_lenta">Моя лента</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>

    </header>

    <main class="submit-news-page">
        <div class="container">
            <div class="submit-news-header">
                <h1>Предложить новость</h1>
                <p>
                    Поделитесь важной информацией с нашей редакцией.<br>
                    Мы проверим её и опубликуем на сайте.
                </p>
            </div>
            <div class="submit-news-card">
                <div class="author-info">
                    <div class="author-left">
                        <div class="author-avatar">
                            <i class="far fa-user-circle"></i>
                        </div>
                        <span class="author-name">
                            <?= htmlspecialchars($_SESSION['user']['name']); ?>
                        </span>
                    </div>
                </div>
                <form id="createNewsForm"
                      action="/actions/news/save_news.php"
                      method="POST"
                      enctype="multipart/form-data"
                      class="create-news-container">
                    <div class="input-group-news">
                        <label class="news-label sn1">
                            Выберите категорию*
                        </label>
                        <select name="category_id"
                                class="news-input"
                                required>
                            <option value="" class="category-input">Выберите категорию</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="input-group-news">
                        <label class="news-label sn2">Язык новости*</label>
                        <div class="lang-tabs">
                            <div class="tab-btn tab-btn-ru active" onclick="showLang('ru')">Русский (RU)</div>
                            <div class="tab-btn tab-btn-kz" onclick="showLang('kz')">Қазақша (KZ)</div>
                            <div class="tab-btn tab-btn-en" onclick="showLang('en')">English (EN)</div>
                        </div>
                    </div>

                    <?php foreach (['ru', 'kz', 'en'] as $lang): ?>

                        <div id="lang-<?= $lang ?>"
                             class="lang-section <?= $lang == 'ru' ? 'active' : '' ?>">
                            <div class="input-group-news">
                                <label class="news-label sn3">Заголовок (<?= strtoupper($lang) ?>)*</label>
                                <input type="text" name="title_<?= $lang ?>" class="news-input title-input" placeholder="Введите заголовок" required>
                            </div>
                            <div class="input-group-news">
                                <label class="news-label sn4">Текст новости (<?= strtoupper($lang) ?>)... *</label>
                                <textarea name="content_<?= $lang ?>" class="text-input" placeholder="Введите текст новости" required></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="input-group-news">
                        <label class="news-label sn5">Загрузить обязательное фото*</label>
                        <div class="photo-area-wrapper">
                            <label for="newsPhoto" class="photo-upload-label">
                                <input type="file" name="newsPhoto" id="newsPhoto" accept="image/*" hidden required>
                                <div class="upload-placeholder">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <span id="uploadStatus" class="main-photo-input">Нажмите или перетащите файл сюда</span>
                                    <small>JPG, PNG до 3 MB</small>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="input-group-news">
                        <label class="news-label sn6">Загрузить дополнительное фото</label>
                        <div class="photo-area-wrapper">
                            <label for="innerPhoto" class="photo-upload-label">
                                <input type="file" name="innerPhoto" id="innerPhoto" accept="image/*" hidden>
                                <div class="upload-placeholder">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <span id="innerUploadStatus" class="addition-photo-input">Нажмите или перетащите файл сюда</span>
                                    <small>JPG, PNG до 3 MB</small>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="auth-btn-black submit-btn">Отправить в редакцию</button>
                    </div>
                    <div class="submit-agreement">
                        Нажимая на кнопку, вы соглашаетесь
                        с правилами публикации материалов.
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer class="main-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <div class="footer-logo">LOGOTIP NEWS</div>
                <div class="footer-legal-info">
                    <p id="certificate1">Свидетельство о постановке на учет №KZ05VFY00030397</p>
                    <p id="certificate2">Выдано 22.12.2020</p>
                </div>
                <div class="footer-socials">
                    <a href="#" class="social-link" aria-label="Instagram">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                        <span>Instagram</span>
                    </a>
                    <a href="#" class="social-link" aria-label="TikTok">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>
                        <span>TikTok</span>
                    </a>
                </div>
            </div>

            <div class="footer-nav-section">
                <h4 class="footer-title">Компания</h4>
                <nav class="footer-links">
                    <a href="/pages/about.php" id="about_us">Про нас</a>
                    <a href="#" id="redaction">Редакция</a>
                    <a href="#" id="vacancy">Вакансии</a>
                </nav>
            </div>

            <div class="footer-nav-section">
                <h4 class="footer-title">Информация</h4>
                <nav class="footer-links">
                    <a href="/pages/contacts.php" id="contact">Контакты</a>
                    <a href="#" id="advertisement">Реклама</a>
                    <a href="#" id="support">Поддержка</a>
                </nav>
            </div>

            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                <div class="footer-nav-section">
                    <h4 class="footer-title admin-panel-title">Админ-панель</h4>
                    <nav class="footer-links">
                        <a href="/admin/admin.php" id="admin-site-mgmt" style="color: var(--color-accent); font-weight: bold;">Управление сайтом</a>
                        <a href="/admin/admin_actions.php" id="admin-news-mgmt">Управление новостями</a>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </footer>

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