<?php
session_start();

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
    <title>Все регистрации</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container records-container">
        <h1>📋 Все зарегистрированные студенты</h1>
        <p class="subtitle">Данные сохранены в файле data.txt на сервере</p>

        <?php
        if (file_exists("data.txt")) {
            $lines = file("data.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $count = count($lines);
            
            if ($count > 0) {
                echo '<div class="stats">';
                echo '<h3>Всего регистраций: ' . $count . '</h3>';
                echo '</div>';
                
                $recordNumber = 1;
                foreach ($lines as $line) {
                    $parts = explode(";", $line);
                    
                    if (count($parts) >= 6) {
                        $name = htmlspecialchars($parts[0]);
                        $email = htmlspecialchars($parts[1]);
                        $age = htmlspecialchars($parts[2]);
                        $faculty = htmlspecialchars($parts[3]);
                        $studyForm = htmlspecialchars($parts[4]);
                        $agree = htmlspecialchars($parts[5]);
                        
                        $facultyDisplay = $facultyNames[$faculty] ?? $faculty;
                        $studyFormDisplay = $studyFormNames[$studyForm] ?? $studyForm;
                        $agreeDisplay = $agree === 'yes' ? 'Да' : 'Нет';
                        
                        echo '<div class="record-card">';
                        echo '<div class="record-header">';
                        echo '<div class="record-name">👤 ' . $name . '</div>';
                        echo '<div class="record-number">#' . $recordNumber . '</div>';
                        echo '</div>';
                        echo '<div class="record-details">';
                        echo '<div class="detail-item"><strong>Email:</strong> ' . ($email ?: 'не указан') . '</div>';
                        echo '<div class="detail-item"><strong>Возраст:</strong> ' . $age . ' лет</div>';
                        echo '<div class="detail-item"><strong>Факультет:</strong> ' . $facultyDisplay . '</div>';
                        echo '<div class="detail-item"><strong>Форма обучения:</strong> ' . $studyFormDisplay . '</div>';
                        echo '<div class="detail-item"><strong>Согласие:</strong> ' . $agreeDisplay . '</div>';
                        echo '</div>';
                        echo '</div>';
                        
                        $recordNumber++;
                    }
                }
            } else {
                echo '<div class="no-records">📭 Файл пуст. Регистраций пока нет.</div>';
            }
        } else {
            echo '<div class="no-records">📄 Файл data.txt не найден. Зарегистрируйте первого студента!</div>';
        }
        ?>

        <div style="text-align: center;">
            <a href="index.php" class="back-link">← Вернуться на главную</a>
        </div>
    </div>
</body>
</html>