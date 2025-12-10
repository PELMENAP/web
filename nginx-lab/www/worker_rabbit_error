<?php

require 'vendor/autoload.php';
require_once 'QueueManagerRabbit.php';

$queue = new QueueManagerRabbit();

echo "🚨 RabbitMQ Worker запущен (очередь ошибок)...\n";

$queue->consumeErrors(function($data) {
    echo "⚠️ Ошибка: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
    
    file_put_contents(
        'processed_rabbit_errors.log',
        json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL,
        FILE_APPEND
    );
    
    echo "📝 Ошибка залогирована\n\n";
});