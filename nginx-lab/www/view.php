<?php
session_start();
require_once 'db.php';
require_once 'Student.php';

$student = new Student($pdo);
$students = $student->getAll();

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
        <h1>📋 Студенты из базы данных MySQL</h1>
        <p class="subtitle">Данные хранятся в Docker-контейнере с MySQL</p>

        <?php if (count($students) > 0): ?>
            <div class="stats">
                <h3>Всего студентов: <?= count($students) ?></h3>
            </div>

            <?php foreach ($students as $index => $s): ?>
                <div class="record-card">
                    <div class="record-header">
                        <div class="record-name">👤 <?= htmlspecialchars($s['name']) ?></div>
                        <div class="record-number">#<?= $index + 1 ?></div>
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
                        <div class="detail-item">
                            <strong>Дата:</strong> <?= date('d.m.Y H:i', strtotime($s['created_at'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-records">
                📭 В базе данных пока нет студентов
            </div>
        <?php endif; ?>

        <div style="text-align: center;">
            <a href="index.php" class="back-link">← На главную</a>
        </div>
    </div>
</body>
</html>