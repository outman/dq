# dq
PHP delay queue based on Redis. Just For FUN.

## Install

```php
composer require outman/dq
```

## Usage

### Client

```php
require __DIR__ . '/vendor/autoload.php';

$queue = new DQ\Client\Queue([
    'scheme' => 'tcp',
    'host'   => '127.0.0.1',
    'port'   => 6379,
]);

$queue->enqueue(mixed $value [, int $delaySeconds = 0]); // $value will be serialize.

//// enqueue delay
for ($i = 0; $i <= 10000; $i ++) {
    mt_srand($i);
    $dt = mt_rand(0, 100);// delay time seconds.
    $ret = $queue->enqueue([
        'dq' => sprintf('delay value - %07d - %d', $i, $dt),], $dt);

    // if ($i % 10000) {
    //     echo "TEST-", $i, PHP_EOL;
    // }
}

//// dequeue
while (true) {
    $v = $queue->dequeue();
    if ($v) {
        $v = json_encode($v) . PHP_EOL;
        file_put_contents('deq_1.txt', $v, FILE_APPEND);
        echo $v;
    }
}
```

### Server

```php
<?php
require __DIR__ . '/vendor/autoload.php';

$scanner = new DQ\Server\Scanner([
    'scheme' => 'tcp',
    'host'   => '127.0.0.1',
    'port'   => 6379,
]);

$scanner->run();
```

