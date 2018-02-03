<?php
namespace DQ;

class Bucket
{
    /**
     * [$config description]
     * @var array
     */
    private $config = [];

    /**
     * [__construct description]
     * @param array $config [description]
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * [getBucketCount description]
     * @return [type] [description]
     */
    public function getBucketCount()
    {
        if (!isset($this->config['bucketCount'])
            || $this->config['bucketCount'] <= 0) {
            return Consts::DEFAULT_BUCKET_THREAD;
        } else {
            return (int) $this->config['bucketCount'];
        }
    }

    /**
     * [getQueueName description]
     * @return [type] [description]
     */
    public function getQueueName()
    {
        if (empty($this->config['queueName'])) {
            return Consts::DEFAULT_QUEUE;
        } else {
            return $this->config['queueName'];
        }
    }

    /**
     * [getBucketPrefix description]
     * @return [type] [description]
     */
    private function getBucketPrefix()
    {
        if (empty($this->config['bucketPrefix'])) {
            return Consts::DEFAULT_QUEUE_BUCKET;
        } else {
            return $this->config['bucketPrefix'];
        }
    }

    /**
     * [selectHashPartial description]
     * @param  [type] $pk [description]
     * @return [type]     [description]
     */
    public function selectHashPartial($pk)
    {
        return $this->getBucketPrefix() . Consts::DEFAULT_SUFFIX_HASH . substr($pk, -2);
    }

    /**
     * [select description]
     * @param  [type] $bucketNumber [description]
     * @return [type]               [description]
     */
    public function select($bucketNumber)
    {
        return $this->getBucketPrefix() . $bucketNumber;
    }

    /**
     * [partialCount description]
     * @return [type] [description]
     */
    public function partialCount()
    {
        if (empty($this->config['partialCount'])
            || $this->config['partialCount'] <= 0) {
            return Consts::HASH_PARTIAL;
        } else {
            return (int) $this->config['partialCount'];
        }
    }
}
