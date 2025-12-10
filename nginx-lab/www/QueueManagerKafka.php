<?php

use RdKafka\Conf;
use RdKafka\Producer;
use RdKafka\KafkaConsumer;

class QueueManagerKafka
{
    private string $broker = 'kafka:9092';
    private string $mainTopic = 'lab7_main_topic';
    private string $errorTopic = 'lab7_error_topic';

    public function publishToMain(array $data): void
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', $this->broker);
        
        $producer = new Producer($conf);
        $topic = $producer->newTopic($this->mainTopic);
        
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($data));
        $producer->poll(0);
        
        for ($flushRetries = 0; $flushRetries < 10; $flushRetries++) {
            $result = $producer->flush(10000);
            if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                break;
            }
        }
    }

    public function publishToError(array $data): void
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', $this->broker);
        
        $producer = new Producer($conf);
        $topic = $producer->newTopic($this->errorTopic);
        
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($data));
        $producer->poll(0);
        $producer->flush(10000);
    }

    public function consumeMain(callable $callback): void
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', $this->broker);
        $conf->set('group.id', 'lab7_main_group');
        $conf->set('auto.offset.reset', 'earliest');
        $conf->set('enable.auto.commit', 'false');
        
        $consumer = new KafkaConsumer($conf);
        $consumer->subscribe([$this->mainTopic]);
        
        echo "Kafka consumer started for main topic...\n";
        
        while (true) {
            $message = $consumer->consume(120 * 1000);
            
            if ($message->err === RD_KAFKA_RESP_ERR_NO_ERROR) {
                $data = json_decode($message->payload, true);
                
                try {
                    $callback($data);
                    $consumer->commit($message);
                } catch (\Exception $e) {
                    $this->publishToError([
                        'original_data' => $data,
                        'error' => $e->getMessage(),
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
    }

    public function consumeErrors(callable $callback): void
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', $this->broker);
        $conf->set('group.id', 'lab7_error_group');
        $conf->set('auto.offset.reset', 'earliest');
        
        $consumer = new KafkaConsumer($conf);
        $consumer->subscribe([$this->errorTopic]);
        
        echo "Kafka consumer started for error topic...\n";
        
        while (true) {
            $message = $consumer->consume(120 * 1000);
            
            if ($message->err === RD_KAFKA_RESP_ERR_NO_ERROR) {
                $data = json_decode($message->payload, true);
                $callback($data);
                $consumer->commit($message);
            }
        }
    }
}