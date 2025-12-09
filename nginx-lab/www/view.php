<?php
session_start();
require_once 'db.php';
require_once 'Student.php';

$student = new Student($pdo);

$filter = $_GET['filter'] ?? 'all';
$minAge = isset($_GET['min_age']) ? intval($_GET['min_age']) : 18;
$selectedFaculty = $_GET['faculty'] ?? '';

if ($filter === 'age' && $minAge > 0) {
    $students = $student->getByMinAge($minAge);
    $filterTitle = "Студенты старше {$minAge} лет";
} elseif ($filter === 'faculty' && !empty($selectedFaculty)) {
    $students = $student->getByFaculty($selectedFaculty);
    $filterTitle = "Факультет: " . ($facultyNames[$selectedFaculty] ?? $selectedFaculty);
} else {
    $students = $student->getAll();
    $filterTitle = "Все студенты";
}

$totalCount = $student->getTotalCount();
$avgAge = $student->getAverageAge();
$statsByFaculty = $student->getStatsByFaculty();
$statsByForm = $student->getStatsByStudyForm();

$facultyNames = [
    'cs' => 'Компьютерные науки',
    'math' => 'Математика',
    'physics' => 'Физика',
    'economics' => 'Экономика',
    'law' => 'Юриспруденция'
];

$studyFormNames = [
    'fulltime' => 'Очная',
    'parttime' => 'Заочная',
    'evening' => 'Вечерняя'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Все студенты</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container records-container">
        <h1>📊 База данных студентов</h1>
        <p class="subtitle">Фильтрация, сортировка и статистика из MySQL</p>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>👥 Всего студентов</h3>
                <div class="stat-value"><?= $totalCount ?></div>
            </div>
            <div class="stat-card">
                <h3>📅 Средний возраст</h3>
                <div class="stat-value">
                    <?php if ($avgAge > 0): ?>
                        <?= $avgAge ?> <span style="font-size:20px">лет</span>
                    <?php else: ?>
                        <span style="font-size:20px; color:#999">нет данных</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>🎓 По факультетам</h3>
                <div class="stat-list">
                    <?php foreach ($statsByFaculty as $stat): ?>
                        <div class="stat-list-item">
                            <span><?= $facultyNames[$stat['faculty']] ?? $stat['faculty'] ?></span>
                            <strong><?= $stat['count'] ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>📚 По формам обучения</h3>
                <div class="stat-list">
                    <?php foreach ($statsByForm as $stat): ?>
                        <div class="stat-list-item">
                            <span><?= $studyFormNames[$stat['study_form']] ?? $stat['study_form'] ?></span>
                            <strong><?= $stat['count'] ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="filter-panel">
            <h2 style="margin-bottom: 15px;">🔍 Фильтры</h2>
            
            <form method="GET" action="view.php">
                <div class="filter-group">
                    <div class="filter-item">
                        <label>Фильтр по возрасту</label>
                        <input type="number" 
                               name="min_age" 
                               placeholder="Минимальный возраст" 
                               value="<?= $filter === 'age' ? $minAge : '' ?>"
                               min="1" max="100">
                    </div>
                    <div class="filter-item">
                        <label>Фильтр по факультету</label>
                        <select name="faculty">
                            <option value="">Все факультеты</option>
                            <?php foreach ($facultyNames as $code => $name): ?>
                                <option value="<?= $code ?>" <?= $selectedFaculty === $code ? 'selected' : '' ?>>
                                    <?= $name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-item">
                        <button type="submit" name="filter" value="age" class="filter-btn">
                            Применить фильтр по возрасту
                        </button>
                    </div>
                    <div class="filter-item">
                        <button type="submit" name="filter" value="faculty" class="filter-btn">
                            Применить фильтр по факультету
                        </button>
                    </div>
                    <div class="filter-item">
                        <a href="view.php" class="filter-btn reset-btn" style="display:inline-block;text-decoration:none;text-align:center;">
                            ✕ Сбросить
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($filter !== 'all'): ?>
            <div class="active-filter">
                ✓ Активный фильтр: <strong><?= $filterTitle ?></strong> (найдено записей: <?= count($students) ?>)
            </div>
        <?php endif; ?>

        <?php if (count($students) > 0): ?>
            <?php foreach ($students as $index => $s): ?>
                <div class="record-card">
                    <div class="record-header">
                        <div class="record-name">👤 <?= htmlspecialchars($s['name']) ?></div>
                        <div class="record-number">#<?= $s['id'] ?></div>
                    </div>
                    <div class="record-details">
                        <div class="detail-item">
                            <strong>Email:</strong> <?= htmlspecialchars($s['email'] ?: 'не указан') ?>
                        </div>
                        <div class="detail-item">
                            <strong>Возраст:</strong> <?= $s['age'] ?> лет
                        </div>
                        <div class="detail-item">
                            <strong>Факультет:</strong> <?= $facultyNames[$s['faculty']] ?? $s['faculty'] ?>
                        </div>
                        <div class="detail-item">
                            <strong>Форма:</strong> <?= $studyFormNames[$s['study_form']] ?? $s['study_form'] ?>
                        </div>
                        <div class="detail-item">
                            <strong>Согласие:</strong> <?= $s['agree_rules'] ? 'Да ✓' : 'Нет ✗' ?>
                        </div>
                        <div class="detail-item" style="grid-column: 1 / -1;">
                            <strong>📅 Дата регистрации:</strong> 
                            <?= date('d.m.Y в H:i:s', strtotime($s['created_at'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-records">
                <?php if ($filter !== 'all'): ?>
                    🔍 По выбранному фильтру студентов не найдено
                <?php else: ?>
                    📭 В базе данных пока нет студентов
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="back-link">← На главную</a>
        </div>
    </div>
</body>
</html>