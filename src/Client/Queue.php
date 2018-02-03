<?php
namespace DQ\Client;

use DQ\DQ;

class Queue extends DQ
{
    /**
     * [dequeue]
     * @return [string]
     */
    public function dequeue()
    {
        $data = $this->getConn()
            ->getRedisClient()
            ->rPop($this->getBucket()->getQueueName());
        return unserialize($data);
    }

    /**
     * [enqueue description]
     * @param  integer $delayTime [description]
     * @return [type]             [description]
     */
    public function enqueue($data, $delayTime = 0)
    {
        if ($data === null) {
            return false;
        }

        if ($delayTime > 0) {
            return $this->enqueueDelay($data, $delayTime);
        } else {
            return $this->enqueueNormal($data);
        }
    }
}
