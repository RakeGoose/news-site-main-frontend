<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>О проекте | Logotip News — Голос современности</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #009688;
            --accent-soft: rgba(0, 150, 136, 0.1);
            --text-main: #1a1a1a;
            --text-muted: #5d6768;
        }

        body {
            background: #fdfdfd;
            font-family: "Inter", sans-serif;
            color: var(--text-main);
            line-height: 1.6;
        }

        .hero-section {
            background: #1a1a1a;
            color: white;
            padding: 80px 0;
            text-align: center;
            margin-bottom: -50px;
            clip-path: ellipse(150% 100% at 50% 0%);
        }

        .hero-section h1 {
            font-size: 3rem;
            margin-bottom: 15px;
            letter-spacing: -1px;
        }

        .hero-section p {
            font-size: 1.2rem;
            opacity: 0.8;
            max-width: 700px;
            margin: 0 auto;
        }

        .about-wrapper {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            padding: 60px;
            margin-top: 20px;
            position: relative;
            z-index: 2;
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 50px;
            align-items: start;
        }

        .about-text h2 {
            font-size: 2rem;
            margin-bottom: 25px;
            color: #000;
        }

        .about-text p {
            font-size: 1.05rem;
            margin-bottom: 20px;
            color: var(--text-muted);
            text-align: justify;
        }

        .eco-list {
            list-style: none;
            padding: 0;
            margin: 25px 0;
        }

        .eco-list li {
            padding-left: 35px;
            position: relative;
            margin-bottom: 15px;
            font-size: 1.05rem;
            color: var(--text-main);
        }

        .eco-list li i {
            position: absolute;
            left: 0;
            top: 5px;
            color: var(--accent);
        }

        .mission-quote {
            background: var(--accent-soft);
            border-left: 4px solid var(--accent);
            padding: 30px;
            border-radius: 0 16px 16px 0;
            font-size: 1.15rem;
            color: #00796b;
            font-weight: 500;
            margin: 40px 0;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-top: 50px;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #f0f0f0;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            border-color: var(--accent);
        }

        .stat-card i {
            font-size: 2rem;
            color: var(--accent);
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .about-grid {
                grid-template-columns: 1fr;
            }

            .about-wrapper {
                padding: 30px;
            }

            .stats-container {
                grid-template-columns: 1fr;
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
                    <a href="#kz" class="lang-item">KZ</a>
                    <a href="#ru" class="lang-item">RU</a>
                    <a href="#en" class="lang-item">EN</a>
                </div>
            </div>
            <div class="header-right">
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="/auth/logout.php" class="auth-btn-black">Выйти</a>
                <?php else: ?>
                    <a href="/auth/login.html" class="auth-btn-black">Авторизоваться</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="logo-container">
            <a href="/pages/index.php" style="text-decoration: none; color: inherit;">
                <h1 class="logo">Logotip news</h1>
            </a>
        </div>
    </header>

    <div class="hero-section">
        <div class="container">
            <h1 id="about_hero_title">О проекте</h1>
            <p id="about_hero_subtitle">Logotip News — это многоязычный новостной портал, создающий устойчивое медиасообщество.</p>
        </div>
    </div>

    <main class="container">
        <div class="about-wrapper">
            <div class="about-grid">
                <div class="about-text">
                    <h2 id="about_mission_title">Наша миссия</h2>
                    <p id="about_main_text">
                        Наш проект — это многоязычный новостной портал, на котором публикуются материалы по ключевым направлениям:
                        политика, спорт, общество, мировые новости и аналитика. Сайт объединяет работу редакции и пользовательский контент,
                        создавая открытую площадку, где важны интерес к теме, активность и вклад каждого автора.
                    </p>

                    <div class="mission-quote" id="about_feature_quote">
                        Ключевая особенность проекта — возможность для каждого читателя стать автором. Независимо от профессионального бэкграунда, любой пользователь может предложить новость.
                    </div>

                    <p id="about_moderation_text">
                        Все материалы проходят редакционную модерацию, что гарантирует качество, достоверность и соответствие стандартам платформы.
                        Мы формируем экосистему, где:
                    </p>

                    <ul class="eco-list">
                        <li><i class="fas fa-check-circle"></i> <span id="eco_1">каждый голос имеет значение;</span></li>
                        <li><i class="fas fa-check-circle"></i> <span id="eco_2">качественный контент получает признание через систему рейтинга;</span></li>
                        <li><i class="fas fa-check-circle"></i> <span id="eco_3">авторы выстраивают собственную аудиторию через подписки;</span></li>
                        <li><i class="fas fa-check-circle"></i> <span id="eco_4">пользователи получают персонализированную новостную ленту.</span></li>
                    </ul>

                    <p id="about_footer_text">
                        Проект ориентирован на долгосрочное развитие цифровой журналистики и создание устойчивого медиасообщества,
                        в котором каждый участник влияет на информационную повестку.
                    </p>
                </div>

                <div class="about-image">
                    <div style="margin-top: 30px; padding: 20px; border: 1px dashed #ccc; border-radius: 15px;">
                        <small id="legal_status_label" style="color: var(--text-muted); display: block; margin-bottom: 10px;">Официальный статус:</small>
                        <p style="font-size: 0.9rem; margin: 0;">Свидетельство №KZ05VFY00030397<br>Выдано 22.12.2020</p>
                    </div>
                </div>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <span class="stat-number" id="stat_author_title">Каждый</span>
                    <span class="stat-label" id="stat_author_desc">Может быть автором</span>
                </div>
                <div class="stat-card">
                    <i class="fas fa-shield-alt"></i>
                    <span class="stat-number">100%</span>
                    <span class="stat-label" id="stat_mod_desc">Модерация контента</span>
                </div>
                <div class="stat-card">
                    <i class="fas fa-language"></i>
                    <span class="stat-number">Multi</span>
                    <span class="stat-label" id="stat_lang_desc">Язычная среда</span>
                </div>
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
                <h4 class="footer-title company">Компания</h4>
                <nav class="footer-links">
                    <a href="/pages/about.php" id="about_us">Про нас</a>
                    <a href="#" id="redaction">Редакция</a>
                    <a href="#" id="vacancy">Вакансии</a>
                </nav>
            </div>

            <div class="footer-nav-section">
                <h4 class="footer-title information">Информация</h4>
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
    <script src="/assets/js/lang.js"></script>
    <script src="/assets/js/main.js"></script>
</body>

</html>