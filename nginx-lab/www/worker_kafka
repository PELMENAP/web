<?php

require 'vendor/autoload.php';
require_once 'QueueManagerKafka.php';

$queue = new QueueManagerKafka();

echo "👷 Kafka Worker запущен (основной топик)...\n";

$queue->consumeMain(function($data) {
    echo "📥 Получено: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
    
    if (isset($data['should_fail']) && $data['should_fail']) {
        throw new Exception("Искусственная ошибка для теста");
    }
    
    sleep(2);
    
    file_put_contents(
        'processed_kafka_main.log',
        json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL,
        FILE_APPEND
    );
    
    echo "✅ Обработано успешно\n\n";
});