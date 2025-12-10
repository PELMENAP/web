<?php

require 'vendor/autoload.php';
require_once 'QueueManagerRabbit.php';
require_once 'QueueManagerKafka.php';

$broker = $_POST['broker'] ?? 'rabbitmq';
$name = $_POST['name'] ?? 'Без имени';
$shouldFail = isset($_POST['should_fail']);

$data = [
    'name' => $name,
    'timestamp' => date('Y-m-d H:i:s'),
    'should_fail' => $shouldFail
];

try {
    if ($broker === 'rabbitmq') {
        $queue = new QueueManagerRabbit();
        $queue->publishToMain($data);
        $message = "Сообщение отправлено в RabbitMQ (основная очередь)";
    } else {
        $queue = new QueueManagerKafka();
        $queue->publishToMain($data);
        $message = "Сообщение отправлено в Kafka (основной топик)";
    }
    
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}