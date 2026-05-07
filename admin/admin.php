<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: /pages/index.php");
    exit;
}

$conn->query("SET SESSION group_concat_max_len = 1000000");

// 1. ПАРАМЕТРЫ ПОИСКА
$search_news = isset($_GET['q_news']) ? trim($conn->real_escape_string($_GET['q_news'])) : '';
$search_users = isset($_GET['q_users']) ? trim($conn->real_escape_string($_GET['q_users'])) : '';

// 2. ЗАПРОС: Новости на проверке (обычно их мало, оставляем без лимита)
$pending_news = $conn->query("
    SELECT n.id, n.created_at, u.name as author,
    GROUP_CONCAT(nt.title SEPARATOR '|||') as titles
    FROM news n
    JOIN news_translations nt ON n.id = nt.news_id AND nt.language = 'ru'
    JOIN users u ON n.author_id = u.id
    WHERE n.status = 'pending'
    GROUP BY n.id
    ORDER BY n.created_at DESC
");

// 3. ЗАПРОС: Архив статей (Логика 5 штук ИЛИ поиск)
$news_where = "WHERE n.status = 'approved'";
$news_limit = "LIMIT 5"; // По умолчанию только 5

if ($search_news !== '') {
    $news_where .= " AND (nt.title LIKE '%$search_news%' OR u.name LIKE '%$search_news%')";
    $news_limit = ""; // Если ищем, убираем лимит
}

$published_news = $conn->query("
    SELECT n.id, n.created_at, u.name as author,
    GROUP_CONCAT(nt.title SEPARATOR '|||') as titles
    FROM news n
    JOIN news_translations nt ON n.id = nt.news_id AND nt.language = 'ru'
    JOIN users u ON n.author_id = u.id
    $news_where
    GROUP BY n.id
    ORDER BY n.created_at DESC
    $news_limit
");

// 4. ЗАПРОС: Пользователи (Логика 5 штук ИЛИ поиск)
$user_where = "WHERE id != " . $_SESSION['user']['id'];
$user_limit = "LIMIT 5";

if ($search_users !== '') {
    $user_where .= " AND (name LIKE '%$search_users%' OR email LIKE '%$search_users%')";
    $user_limit = "";
}
$users = $conn->query("SELECT id, name, email FROM users $user_where ORDER BY name ASC $user_limit");
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Панель управления</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #4f46e5;
            --bg: #f1f5f9;
            --card-bg: #ffffff;
            --text-dark: #0f172a;
            --text-light: #64748b;
            --border: #e2e8f0;
            --radius: 16px;
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text-dark);
            margin: 0;
        }

        nav {
            background: #ffffff;
            padding: 0.75rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--border);
        }

        nav h2 {
            font-size: 1.1rem;
            margin: 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        nav h2 i {
            color: var(--accent);
        }

        nav a {
            color: var(--text-light);
            text-decoration: none;
            font-size: 0.85rem;
        }

        .wrapper {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        /* ИСПРАВЛЕНИЕ ВЫПИРАЮЩЕЙ ФОРМЫ */
        form.search-form {
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
        }

        .search-wrapper {
            position: relative;
            width: 300px;
        }

        .search-wrapper i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            z-index: 2;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 0.75rem 0.5rem 2.2rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 0.8rem;
            background: #f8fafc;
            transition: 0.2s;
            box-sizing: border-box;
            /* Важно: чтобы padding не раздувал ширину */
        }

        .search-input:focus {
            border-color: var(--accent);
            background: white;
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            text-align: left;
            padding: 0.8rem 1.5rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--text-light);
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
        }

        .btn {
            border: none;
            padding: 0.5rem;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: 0.2s;
        }

        .btn-edit {
            background: #eef2ff;
            color: #4338ca;
        }

        .btn-del {
            background: #fff1f2;
            color: #be123c;
            margin-left: 5px;
        }

        .author-badge {
            background: #f1f5f9;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .empty-msg {
            padding: 2rem;
            text-align: center;
            color: var(--text-light);
            font-style: italic;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            z-index: 1000;
        }

        .modal-content {
            background: white;
            max-width: 400px;
            margin: 10% auto;
            padding: 2rem;
            border-radius: var(--radius);
        }

        .btn-temp-pass {
            background: #fef3c7;
            color: #92400e;
            margin-left: 5px;
        }

        .btn-temp-pass:hover {
            background: #fde68a;
        }
    </style>
</head>

<body>

    <nav>
        <h2><i class="fas fa-shield-halved"></i> Admin Panel</h2>
        <a href="/pages/index.php">На сайт <i class="fas fa-arrow-up-right-from-square"></i></a>
    </nav>

    <div class="wrapper">
        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 12px; margin-bottom: 2rem; border: 1px solid #bbf7d0; font-size: 0.9rem;">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <div class="dashboard-grid">

            <div class="card">
                <div class="card-header">
                    <h3>Новые заявки <span style="background:var(--accent); color:white; padding:2px 8px; border-radius:6px; font-size:0.7rem;"><?= $pending_news->num_rows ?></span></h3>
                </div>
                <?php if ($pending_news->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Автор</th>
                                <th>Дата</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $pending_news->fetch_assoc()):
                                $titles = explode('|||', $row['titles']); ?>
                                <tr>
                                    <td><a href="/admin/view_pending.php?id=<?= $row['id'] ?>" style="text-decoration:none; color:var(--accent); font-weight:600;"><?= htmlspecialchars($titles[0]) ?></a></td>
                                    <td><span class="author-badge"><?= htmlspecialchars($row['author']) ?></span></td>
                                    <td style="color:var(--text-light); font-size:0.8rem;"><?= date('d.m.Y H:i', strtotime($row['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-msg">Новых заявок пока нет</div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Архив статей <?= $search_news === '' ? '<small style="color:var(--text-light); font-weight:400;">(последние 5)</small>' : '' ?></h3>
                    <form method="GET" class="search-form">
                        <div class="search-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" name="q_news" class="search-input" placeholder="Найти статью..." value="<?= htmlspecialchars($search_news) ?>">
                        </div>
                    </form>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Статья</th>
                            <th>Автор</th>
                            <th style="text-align:right;">Действие</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($published_news->num_rows > 0): ?>
                            <?php while ($row = $published_news->fetch_assoc()):
                                $titles = explode('|||', $row['titles']); ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:600;"><?= htmlspecialchars($titles[0]) ?></div>
                                        <small style="color:var(--text-light);"><?= date('d.m.Y', strtotime($row['created_at'])) ?></small>
                                    </td>
                                    <td><span class="author-badge"><?= htmlspecialchars($row['author']) ?></span></td>
                                    <td style="text-align:right;">
                                        <a href="edit_news.php?id=<?= $row['id'] ?>" class="btn btn-edit"><i class="fas fa-pen"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="empty-msg">По вашему запросу ничего не найдено</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Пользователи <?= $search_users === '' ? '<small style="color:var(--text-light); font-weight:400;">(топ 5)</small>' : '' ?></h3>
                    <form method="GET" class="search-form">
                        <div class="search-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" name="q_users" class="search-input" placeholder="Поиск пользователей..." value="<?= htmlspecialchars($search_users) ?>">
                        </div>
                    </form>
                </div>
                <table>
                    <tbody>
                        <?php if ($users->num_rows > 0): ?>
                            <?php while ($u = $users->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:700;"><?= htmlspecialchars($u['name']) ?></div>
                                        <div style="color:var(--text-light); font-size: 0.8rem;"><?= htmlspecialchars($u['email']) ?></div>
                                    </td>
                                    <td style="text-align:right;">
                                        <button onclick="editUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name']) ?>')" class="btn btn-edit" title="Редактировать"><i class="fas fa-user-gear"></i></button>

                                        <a href="/admin/admin_actions.php?action=set_temp_password&id=<?= $u['id'] ?>"
                                            class="btn btn-temp-pass"
                                            title="Дать временный пароль"
                                            onclick="return confirm('Установить временный пароль для <?= htmlspecialchars($u['name']) ?>? (Astana2026 + дата)')">
                                            <i class="fas fa-key"></i>
                                        </a>

                                        <a href="/admin/admin_actions.php?action=delete_user&id=<?= $u['id'] ?>" class="btn btn-del" onclick="return confirm('Удалить?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="empty-msg">Пользователи не найдены</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <h3 style="margin-top:0;">Изменить профиль</h3>
            <form action="admin_actions.php?action=edit_user" method="POST">
                <input type="hidden" name="user_id" id="editUserId">
                <input type="text" name="new_name" id="editUserName" class="search-input" style="padding-left:1rem; margin-bottom:1.5rem;" required>
                <div style="display:flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal()" class="btn" style="background:#f1f5f9;">Отмена</button>
                    <button type="submit" class="btn" style="background:var(--accent); color:white;">Сохранить</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editUser(id, name) {
            document.getElementById('editUserId').value = id;
            document.getElementById('editUserName').value = name;
            document.getElementById('editUserModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editUserModal').style.display = 'none';
        }
        window.onclick = e => {
            if (e.target.classList.contains('modal')) closeModal();
        }
    </script>
</body>

</html>