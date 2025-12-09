<?php
session_start();
if (file_exists(__DIR__ . '/UserInfo.php')) {
    require_once __DIR__ . '/UserInfo.php';
}

function displaySessionData($key, $label, $default = 'не указан') {
    if (isset($_SESSION[$key])) {
        $value = $_SESSION[$key] ?: $default;
        if ($key === 'agree') $value = $value === 'yes' ? 'Да' : 'Нет';
        return "<div class='data-item'><strong>$label:</strong> " . htmlspecialchars($value) . "</div>";
    }
    return '';
}

function displayCookieData($key, $label) {
    if (isset($_COOKIE[$key])) {
        return "<div class='data-item'><strong>$label:</strong> " . htmlspecialchars($_COOKIE[$key]) . "</div>";
    }
    return '';
}

function displayMessage($type, $message) {
    if ($type === 'success' && isset($_SESSION['success'])) {
        $html = "<div class='success-message'>✅ " . htmlspecialchars($_SESSION['success']) . "</div>";
        unset($_SESSION['success']);
        return $html;
    }
    
    if ($type === 'errors' && isset($_SESSION['errors'])) {
        $html = "<div class='error-list'><strong>❌ Ошибки при регистрации:</strong><ul style='margin:10px 0 0 20px'>";
        foreach ($_SESSION['errors'] as $error) {
            $html .= "<li>" . htmlspecialchars($error) . "</li>";
        }
        $html .= "</ul></div>";
        unset($_SESSION['errors']);
        return $html;
    }
    return '';
}
?>

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
        <p class="subtitle">PHP Sessions, Cookies, API Integration, User Detection</p>

        <?= displayMessage('success', '') ?>
        <?= displayMessage('errors', '') ?>

        <div class="data-section">
            <h2>📊 Данные из сессии (Session)</h2>
            <?php if (isset($_SESSION['name'])): ?>
                <?= displaySessionData('name', 'Имя') ?>
                <?= displaySessionData('email', 'Email') ?>
                <?= displaySessionData('age', 'Возраст') . ' лет' ?>
                <?= displaySessionData('faculty', 'Факультет') ?>
                <?= displaySessionData('studyForm', 'Форма обучения') ?>
                <?= displaySessionData('agree', 'Согласие с правилами') ?>
                <p style="margin-top:15px;color:#666;font-size:13px">
                    ℹ️ Данные хранятся на сервере и привязаны к вашей сессии
                </p>
            <?php else: ?>
                <p style="color:#999">Данных в сессии пока нет. Заполните форму для регистрации.</p>
            <?php endif; ?>
        </div>

        <div class="cookie-section">
            <h2>🍪 Данные из Cookies</h2>
            <?php if (isset($_COOKIE['last_name'])): ?>
                <?= displayCookieData('last_name', 'Последнее имя') ?>
                <?= displayCookieData('last_email', 'Последний email') ?>
                <?= displayCookieData('last_faculty', 'Последний факультет') ?>
                <?= displayCookieData('last_submission', 'Последняя отправка') ?>
                <?php if ($lastVisit = UserInfo::getLastVisit()): ?>
                    <div class="data-item"><strong>Последний визит:</strong> <?= htmlspecialchars($lastVisit) ?></div>
                <?php endif; ?>
                <div class="data-item"><strong>Количество визитов:</strong> <?= UserInfo::getVisitCount() ?></div>
                <p style="margin-top:15px;color:#666;font-size:13px">
                    ℹ️ Данные хранятся в браузере (срок действия: 1 час / 24 часа)
                </p>
            <?php else: ?>
                <p style="color:#999">Cookies пока не установлены.</p>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['user_info'])): ?>
        <div class="user-info-section">
            <h2>👤 Информация о пользователе</h2>
            <?php foreach ($_SESSION['user_info'] as $key => $value): ?>
                <div class="data-item">
                    <strong><?= ucfirst(str_replace('_', ' ', htmlspecialchars($key))) ?>:</strong> 
                    <?= htmlspecialchars($value) ?>
                </div>
            <?php endforeach; ?>
            <p style="margin-top:15px;color:#666;font-size:13px">
                ℹ️ Определяется автоматически из HTTP-заголовков
            </p>
        </div>
        <?php endif; ?>

        <div class="api-section" id="apiSection">
            <h2>
                🌐 Данные из API (HeadHunter - Регионы РФ)
                <?php if (isset($_SESSION['api_data'])): ?>
                    <span class="cache-badge <?= $_SESSION['api_data']['cached'] ? 'cache-hit' : 'cache-miss' ?>">
                        <?= $_SESSION['api_data']['cached'] ? 'CACHED' : 'FRESH' ?>
                    </span>
                <?php endif; ?>
            </h2>
            
            <div id="apiContent">
                <?php if (isset($_SESSION['api_data'])): ?>
                    <?php if ($_SESSION['api_data']['success']): ?>
                        <?php $regions = $_SESSION['api_data']['data'] ?>
                        <p><strong>Загружено:</strong> <?= date('H:i:s', $_SESSION['api_data']['timestamp']) ?></p>
                        <?php if ($_SESSION['api_data']['cached']): ?>
                            <p><strong>Возраст кеша:</strong> <?= $_SESSION['api_data']['cache_age'] ?> сек</p>
                        <?php endif; ?>
                        <p><strong>Всего регионов:</strong> <?= count($regions) ?></p>
                        <div style="margin-top:15px">
                            <?php foreach (array_slice($regions, 0, 10) as $region): ?>
                                <div class="region-item">
                                    <strong><?= htmlspecialchars($region['name']) ?></strong>
                                    (ID: <?= htmlspecialchars($region['id']) ?>)
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($regions) > 10): ?>
                                <p style="margin-top:10px;color:#666">
                                    ... и ещё <?= count($regions) - 10 ?> регионов
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="api-error">⚠️ Ошибка API: <?= htmlspecialchars($_SESSION['api_data']['error']) ?></div>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="color:#999">Данные API загрузятся после регистрации</p>
                <?php endif; ?>
            </div>
            <div class="spinner" id="spinner"></div>
        </div>

        <div class="nav-links">
            <a href="form.html">📝 Заполнить форму</a>
            <a href="view.php">📋 Все регистрации</a>
            <?php if (isset($_SESSION['api_data'])): ?>
                <button onclick="refreshApi()">🔄 Обновить API</button>
            <?php endif; ?>
        </div>
    </div>

    <script>
        async function refreshApi() {
            const spinner = document.getElementById('spinner');
            const content = document.getElementById('apiContent');
            const section = document.getElementById('apiSection');
            
            content.style.display = 'none';
            spinner.style.display = 'block';
            
            try {
                const response = await fetch('api_refresh.php');
                const data = await response.json();
                
                if (data.success) {
                    section.innerHTML = `
                        <h2>🌐 Данные из API (HeadHunter - Регионы РФ) 
                            <span class="cache-badge cache-miss">FRESH</span>
                        </h2>
                        <p><strong>Обновлено:</strong> ${new Date().toLocaleTimeString()}</p>
                        <p><strong>Всего регионов:</strong> ${data.regions.length}</p>
                        <div style="margin-top:15px">
                            ${data.regions.slice(0,10).map(r=>`
                                <div class="region-item">
                                    <strong>${escapeHtml(r.name)}</strong> (ID: ${escapeHtml(r.id)})
                                </div>
                            `).join('')}
                            ${data.regions.length>10?`
                                <p style="margin-top:10px;color:#666">
                                    ... и ещё ${data.regions.length-10} регионов
                                </p>
                            `:''}
                        </div>
                    `;
                } else {
                    section.innerHTML = `<h2>🌐 Данные из API</h2>
                        <div class="api-error">⚠️ Ошибка: ${escapeHtml(data.error)}</div>`;
                }
            } catch (error) {
                section.innerHTML = `<h2>🌐 Данные из API</h2>
                    <div class="api-error">⚠️ Ошибка сети: ${escapeHtml(error.message)}</div>`;
            }
            
            spinner.style.display = 'none';
            content.style.display = 'block';
        }
        
        function escapeHtml(text) {
            const map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'};
            return String(text).replace(/[&<>"']/g, m=>map[m]);
        }
    </script>
</body>
</html>