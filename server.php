<?php
require __DIR__ . '/vendor/autoload.php';

$scanner = new DQ\Server\Scanner([
    'scheme' => 'tcp',
    'host'   => '127.0.0.1',
    'port'   => 6379,
]);

$scanner->run();
