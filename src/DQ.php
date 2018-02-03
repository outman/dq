<?php
namespace DQ;

use DQ\Bucket;
use DQ\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class DQ
{
    /**
     * [$conn description]
     * @var [type]
     */
    protected $conn;

    /**
     * [$bucket description]
     * @var [type]
     */
    protected $bucket;

    /**
     * [$config description]
     * @var array
     */
    protected $config = [];

    /**
     * [__construct]
     * @param array $config [
     *     'params' => [] // Predis/Client $params
     *     'options' => [] // Predis/Client $options
     *     'bucketCount' => DQ::DEFAULT_BUCKET_THREAD
     *     'queueName' => DQ::DEFAULT_QUEUE,
     *     'bucketPrefix' => DQ::DEFAULT_QUEUE_BUCKET,
     *     'partialCount' => DQ::HASH_PARTIAL,
     * ]
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * [getConn description]
     * @return [type] [description]
     */
    protected function getConn()
    {
        if (!empty($this->conn)) {
            return $this->conn;
        }

        $params = [];
        if (isset($this->config['params'])) {
            $params = $this->config['params'];
        }

        $options = [];
        if (isset($this->config['options'])) {
            $options = $this->config['options'];
        }

        $this->conn = new Connection($params, $options);
        return $this->conn;
    }

    /**
     * [enqueueNormal description]
     * @param  [type]  $data          [description]
     * @param  boolean $needSerialize [description]
     * @return [type]                 [description]
     */
    protected function enqueueNormal($data, $needSerialize = true)
    {
        if ($needSerialize) {
            $data = serialize($data);
        }

        return $this->getConn()
            ->getRedisClient()
            ->lpush($this->getBucket()->getQueueName(), $data);
    }

    /**
     * [enqueueDelay description]
     * @param  [type] $data      [description]
     * @param  [type] $delayTime [description]
     * @return [type]            [description]
     */
    protected function enqueueDelay($data, $delayTime)
    {
        $sesstr = serialize($data);
        $number = crc32($sesstr) % $this->getBucket()->getBucketCount();
        $bucket = $this->getBucket()->select($number);

        $pk = (Uuid::uuid4())->toString();
        return $this->reenqueue($bucket, time() + $delayTime, $pk)
               && $this->getConn()
                       ->getRedisClient()
                       ->hset($this->getBucket()->selectHashPartial($pk), $pk, $sesstr);
    }

    /**
     * [reenqueue description]
     * @param  [type] $bucket    [description]
     * @param  [type] $delayTime [description]
     * @param  [type] $value     [description]
     * @return [type]            [description]
     */
    protected function reenqueue($bucket, $delayTime, $value)
    {
        return $this->getConn()->getRedisClient()->zadd($bucket, $delayTime, $value);
    }

    /**
     * [moveToQueue description]
     * @param  [type] $bucket [description]
     * @param  [type] $pk     [description]
     * @return [type]         [description]
     */
    protected function moveToQueue($bucket, $pk)
    {
        $hashPartial = $this->getBucket()->selectHashPartial($pk);
        $sesstrValue = $this->getConn()
                ->getRedisClient()
                ->hget($hashPartial, $pk);

        if ($sesstrValue) {
            return $this->enqueueNormal($sesstrValue, false)
            && $this->getConn()
                    ->getRedisClient()
                    ->zrem($bucket, $pk)
            && $this->getConn()
                    ->getRedisClient()
                    ->hdel($hashPartial, $pk);
        }
        return false;
    }

    /**
     * [getBucket description]
     * @return [type] [description]
     */
    protected function getBucket()
    {
        if (empty($this->bucket)) {
            $this->bucket = new Bucket($this->config);
        }
        return $this->bucket;
    }
}
