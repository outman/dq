<?php
namespace DQ;

use Predis\Client;

class Connection
{
    /**
     * [$redisParams redis client params]
     * @var [type]
     */
    private $redisParams;

    /**
     * [$redisOptions redis client options]
     * @var [type]
     */
    private $redisOptions;

    /**
     * [$redisClient]
     * @var [Predis/Client]
     */
    protected $redisClient;

    /**
     * [__construct]
     * @param array $params ]
     * @param array $options]
     */
    public function __construct($params = [], $options = [])
    {
        $this->redisParams = $params;
        $this->redisOptions = $options;
    }

    /**
     * [getRedisClient init redis client]
     * @return [Predis/Client]
     */
    public function getRedisClient()
    {
        if (empty($this->redisClient)) {
            $this->redisClient = new Client($this->redisParams, $this->redisOptions);
        }
        return $this->redisClient;
    }
}
