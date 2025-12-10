<?php

require 'vendor/autoload.php';
require_once 'QueueManagerRabbit.php';

$rabbitStats = ['main_queue' => 0, 'error_queue' => 0];

try {
    $rabbit = new QueueManagerRabbit();
    $rabbitStats = $rabbit->getQueueStats();
} catch (Exception $e) {
    $rabbitError = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 7 - Message Queues</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .subtitle {
            color: #666;
            font-size: 16px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .stat-card h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .stat-value {
            font-size: 48px;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin: 15px 0;
        }
        .stat-label {
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        input[type="text"], select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }
        input[type="text"]:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #667eea;
        }
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
        }
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            display: none;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .message.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📨 Message Queue Lab</h1>
            <p class="subtitle">RabbitMQ & Kafka | Лабораторная работа #7</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>🐰 RabbitMQ - Основная очередь</h3>
                <div class="stat-value"><?= $rabbitStats['main_queue'] ?></div>
                <div class="stat-label">сообщений</div>
            </div>
            <div class="stat-card">
                <h3>🐰 RabbitMQ - Очередь ошибок</h3>
                <div class="stat-value"><?= $rabbitStats['error_queue'] ?></div>
                <div class="stat-label">сообщений</div>
            </div>
            <div class="stat-card">
                <h3>📊 Kafka - Основной топик</h3>
                <div class="stat-value">N/A</div>
                <div class="stat-label">требуется JMX мониторинг</div>
            </div>
            <div class="stat-card">
                <h3>📊 Kafka - Топик ошибок</h3>
                <div class="stat-value">N/A</div>
                <div class="stat-label">требуется JMX мониторинг</div>
            </div>
        </div>

        <div class="form-card">
            <h2>➕ Отправить сообщение</h2>
            <form id="messageForm">
                <div class="form-group">
                    <label>Выберите брокер</label>
                    <select name="broker" id="broker">
                        <option value="rabbitmq">RabbitMQ</option>
                        <option value="kafka">Kafka</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Имя студента</label>
                    <input type="text" name="name" id="name" required placeholder="Введите имя">
                </div>
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="should_fail" id="should_fail">
                        <label for="should_fail" style="margin: 0;">Вызвать ошибку (отправить в error queue)</label>
                    </div>
                </div>
                <button type="submit" id="submitBtn">Отправить в очередь</button>
                <div id="message" class="message"></div>
            </form>
        </div>
    </div>

    <script>
        const form = document.getElementById('messageForm');
        const messageDiv = document.getElementById('message');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            submitBtn.disabled = true;
            messageDiv.className = 'message';
            messageDiv.textContent = 'Отправка...';
            messageDiv.classList.add('show');

            const formData = new FormData(form);

            try {
                const response = await fetch('send.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    messageDiv.className = 'message success show';
                    messageDiv.textContent = '✅ ' + result.message;
                    form.reset();
                    
                    setTimeout(() => location.reload(), 2000);
                } else {
                    messageDiv.className = 'message error show';
                    messageDiv.textContent = '❌ ' + result.error;
                }
            } catch (error) {
                messageDiv.className = 'message error show';
                messageDiv.textContent = '❌ Ошибка: ' + error.message;
            } finally {
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>