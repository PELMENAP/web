<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class QueueManagerRabbit
{
    private $connection;
    private $channel;
    private string $mainQueue = 'lab7_main_queue';
    private string $errorQueue = 'lab7_error_queue';

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();
        
        $this->channel->queue_declare($this->mainQueue, false, true, false, false);
        $this->channel->queue_declare($this->errorQueue, false, true, false, false);
    }

    public function publishToMain(array $data): void
    {
        $msg = new AMQPMessage(
            json_encode($data),
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );
        $this->channel->basic_publish($msg, '', $this->mainQueue);
    }

    public function publishToError(array $data): void
    {
        $msg = new AMQPMessage(
            json_encode($data),
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );
        $this->channel->basic_publish($msg, '', $this->errorQueue);
    }

    public function consumeMain(callable $callback): void
    {
        $this->channel->basic_qos(null, 1, null);
        
        $this->channel->basic_consume(
            $this->mainQueue,
            '',
            false,
            false,
            false,
            false,
            function ($msg) use ($callback) {
                $data = json_decode($msg->body, true);
                try {
                    $callback($data);
                    $msg->ack();
                } catch (\Exception $e) {
                    $msg->nack(true);
                    $this->publishToError([
                        'original_data' => $data,
                        'error' => $e->getMessage(),
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        );

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function consumeErrors(callable $callback): void
    {
        $this->channel->basic_consume(
            $this->errorQueue,
            '',
            false,
            false,
            false,
            false,
            function ($msg) use ($callback) {
                $data = json_decode($msg->body, true);
                $callback($data);
                $msg->ack();
            }
        );

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function getQueueStats(): array
    {
        $mainStats = $this->channel->queue_declare($this->mainQueue, true, true, false, false);
        $errorStats = $this->channel->queue_declare($this->errorQueue, true, true, false, false);
        
        return [
            'main_queue' => $mainStats[1] ?? 0,
            'error_queue' => $errorStats[1] ?? 0
        ];
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}