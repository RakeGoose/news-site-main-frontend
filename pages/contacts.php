<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="unit">Контакты | Logotip News</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --accent: #009688;
            --text-main: #2c3e50;
            --text-muted: #7f8c8d;
            --white: #ffffff;
        }

        body {
            background: #fdfdfd;
            font-family: 'Segoe UI', sans-serif;
            opacity: 0;
            /* Для плавного появления при переводе */
            transition: opacity 0.3s;
        }

        .hero-contacts {
            background: #1a1a1a;
            color: white;
            padding: 60px 0 100px 0;
            text-align: center;
            clip-path: ellipse(150% 100% at 50% 0%);
        }

        .contacts-wrapper {
            margin-top: -60px;
            display: flex;
            /* Изменено с grid на flex для центрирования */
            justify-content: center;
            position: relative;
            z-index: 10;
            margin-bottom: 80px;
            padding: 0 20px;
        }

        .contact-info-card {
            background: var(--white);
            padding: 50px;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            /* Контакты в две колонки */
            gap: 40px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
        }

        .info-item i {
            background: rgba(0, 150, 136, 0.1);
            color: var(--accent);
            min-width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-right: 20px;
        }

        .info-text h4 {
            margin: 0 0 5px 0;
            font-size: 1.1rem;
            color: var(--text-main);
        }

        .info-text p {
            margin: 0;
            color: var(--text-muted);
            line-height: 1.5;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .contact-info-card {
                grid-template-columns: 1fr;
                padding: 30px;
            }
        }
    </style>
</head>

<body>

    <header class="main-header">
        <div class="container header-top">
            <div class="header-left">
                <button class="search-btn"><i class="fas fa-search"></i></button>
            </div>
            <div class="header-center">
                <div class="lang-switcher">
                    <a href="#kz" class="lang-item">KAZ</a>
                    <a href="#ru" class="lang-item">RUS</a>
                    <a href="#en" class="lang-item">ENG</a>
                </div>
            </div>
            <div class="header-right">
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="/auth/logout.php" class="auth-btn-black" id="logout">Выйти</a>
                <?php else: ?>
                    <a href="/auth/login.html" class="auth-btn-black" id="login">Авторизоваться</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="logo-container">
            <a href="/pages/index.php" style="text-decoration: none; color: inherit;">
                <h1 class="logo">Logotip news</h1>
            </a>
        </div>
    </header>

    <div class="hero-contacts">
        <div class="container">
            <h1 id="contact-hero-title">Свяжитесь с нами</h1>
            <p id="contact-hero-subtitle">Мы всегда открыты для предложений и сотрудничества.</p>
        </div>
    </div>

    <main class="container">
        <div class="contacts-wrapper">
            <div class="contact-info-card">
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div class="info-text">
                        <h4 id="contact-addr-title">Адрес редакции</h4>
                        <p id="contact-addr-text">Казахстан, г. Астана<br>пр. Мангилик Ел, 55/8</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-phone-alt"></i>
                    <div class="info-text">
                        <h4 id="contact-phone-title">Телефон</h4>
                        <p>+7 (776) 979-02-20<br>+7 (7172) 44-55-66</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <div class="info-text">
                        <h4 id="contact-mail-title">Email</h4>
                        <p>admin.mangilik@gmail.com<br>editor@logotipnews.kz</p>
                    </div>
                </div>

                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <div class="info-text">
                        <h4 id="contact-time-title">Режим работы</h4>
                        <p id="contact-time-text">Пн — Пт: 09:00 - 19:00<br>Сб — Вс: Дежурный редактор</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <div class="container footer-content">
            <div class="footer-legal">
                <p id="certificate1">Свидетельство о постановке на учет №KZ05VFY00030397</p>
                <p id="certificate2">выдано 22.12.2020</p>
            </div>
            <div class="footer-nav-groups">
                <div class="footer-col"><a href="/pages/about.php" id="about_us">Про нас</a><a href="#" id="redaction">Редакция</a></div>
                <div class="footer-col"><a href="#" id="vacancy">Вакансии</a><a href="/pages/contacts.php" id="contact">Контакты</a></div>
                <div class="footer-col"><a href="#" id="advertisement">Реклама</a><a href="#" id="support">Поддержка</a></div>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                    <div class="footer-col">
                        <a href="/admin/admin.php" style="color: #009688; font-weight: bold;">Admin</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="footer-socials">
                <a href="#" class="social-icon">Instagram</a>
                <a href="#" class="social-icon">TikTok</a>
            </div>
        </div>
    </footer>

    <script src="/assets/js/lang.js"></script>
    <script src="/assets/js/main.js"></script>
</body>

</html>