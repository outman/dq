<?php
require __DIR__ . '/../vendor/autoload.php';

$redisParams = [
    'scheme' => 'tcp',
    'host'   => '127.0.0.1',
    'port'   => 6379,
];

$config = [
    'params' => $redisParams, // Predis/Client $params
    // 'options' => [] // Predis/Client $options
    // 'bucketCount' => DQ::DEFAULT_BUCKET_THREAD
    // 'queueName' => DQ::DEFAULT_QUEUE,
    // 'bucketPrefix' => DQ::DEFAULT_QUEUE_BUCKET,
    // 'partialCount' => DQ::HASH_PARTIAL,
];


$scanner = new DQ\Server\Scanner($config);
$scanner->run();
