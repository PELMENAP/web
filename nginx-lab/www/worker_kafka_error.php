<?php

require 'vendor/autoload.php';
require_once 'QueueManagerKafka.php';

$queue = new QueueManagerKafka();

echo "🚨 Kafka Worker запущен (топик ошибок)...\n";

$queue->consumeErrors(function($data) {
    echo "⚠️ Ошибка: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
    
    file_put_contents(
        'processed_kafka_errors.log',
        json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL,
        FILE_APPEND
    );
    
    echo "📝 Ошибка залогирована\n\n";
});