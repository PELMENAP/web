<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная - Регистрация студентов</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>🎓 Система регистрации студентов</h1>
        <p class="subtitle">Управление данными через PHP Sessions и Cookies</p>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message">
                ✅ <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['errors'])): ?>
            <div class="error-list">
                <strong>❌ Ошибки при регистрации:</strong>
                <ul style="margin: 10px 0 0 20px;">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>

        <div class="data-section">
            <h2>📊 Данные из сессии (Session)</h2>
            <?php if (isset($_SESSION['name'])): ?>
                <div class="data-item"><strong>Имя:</strong> <?= htmlspecialchars($_SESSION['name']) ?></div>
                <div class="data-item"><strong>Email:</strong> <?= htmlspecialchars($_SESSION['email'] ?: 'не указан') ?></div>
                <div class="data-item"><strong>Возраст:</strong> <?= htmlspecialchars($_SESSION['age']) ?> лет</div>
                <div class="data-item"><strong>Факультет:</strong> <?= htmlspecialchars($_SESSION['faculty']) ?></div>
                <div class="data-item"><strong>Форма обучения:</strong> <?= htmlspecialchars($_SESSION['studyForm']) ?></div>
                <div class="data-item"><strong>Согласие с правилами:</strong> <?= $_SESSION['agree'] === 'yes' ? 'Да' : 'Нет' ?></div>
                <p style="margin-top: 15px; color: #666; font-size: 13px;">
                    ℹ️ Данные хранятся на сервере и привязаны к вашей сессии (PHPSESSID в cookie)
                </p>
            <?php else: ?>
                <p style="color: #999;">Данных в сессии пока нет. Заполните форму для регистрации.</p>
            <?php endif; ?>
        </div>

        <div class="cookie-section">
            <h2>🍪 Данные из Cookies</h2>
            <?php if (isset($_COOKIE['last_name'])): ?>
                <div class="data-item"><strong>Последнее имя:</strong> <?= htmlspecialchars($_COOKIE['last_name']) ?></div>
                <div class="data-item"><strong>Последний email:</strong> <?= htmlspecialchars($_COOKIE['last_email'] ?: 'не указан') ?></div>
                <div class="data-item"><strong>Последний факультет:</strong> <?= htmlspecialchars($_COOKIE['last_faculty']) ?></div>
                <p style="margin-top: 15px; color: #666; font-size: 13px;">
                    ℹ️ Данные хранятся в браузере и живут 1 час с момента регистрации
                </p>
            <?php else: ?>
                <p style="color: #999;">Cookies пока не установлены. После регистрации данные сохранятся в браузере.</p>
            <?php endif; ?>
        </div>

        <div class="nav-links">
            <a href="form.html">📝 Заполнить форму</a>
            <a href="view.php">📋 Все регистрации</a>
        </div>
    </div>
</body>
</html>