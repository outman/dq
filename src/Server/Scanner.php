<?php
namespace DQ\Server;

use DQ\DQ;
use DQ\Exceptions\PCNTLException;

class Scanner extends DQ
{
    private $pids = [];

    /**
     * [run description]
     * @return [type] [description]
     */
    public function run()
    {
        $threadCount = $this->getBucket()->getBucketCount();
        for ($i = 0; $i < $threadCount; $i ++) {
            $this->forkPcntl($i);
        }

        while (true) {
            $expre = implode('|', array_values($this->pids));
            $command = "ps -e -opid | grep -E '{$expre}'";
            $result = shell_exec($command);
            $result = strtr(trim($result), ["\r\n" => "\n"]);
            $onlinePids = explode("\n", $result);
            $onlinePids = array_filter($onlinePids, function ($v) {
                return !empty($v);
            });

            foreach ($this->pids as $order => $pid) {
                if (!in_array($pid, $onlinePids)) {
                    $this->forkPcntl($order);
                }
            }

            sleep(5);
        }
    }

    /**
     * [forkPcntl description]
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    private function forkPcntl($order)
    {
        $this->pids[$order] = pcntl_fork();
        if ($this->pids[$order] === -1) {
            throw new PCNTLException("pcntl fork failed.");
        } elseif ($this->pids[$order]) {
            // todo moniter pids
        } else {
            $this->scanBucket($order);
        }
    }

    /**
     * [scanBucket description]
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    private function scanBucket($order)
    {
        $bucket = $this->getBucket()->select($order);
        $partial = $this->getBucket()->partialCount();

        while (true) {
            $elements = $this->getConn()
                ->getRedisClient()
                ->zrange($bucket, 0, $partial, ['WITHSCORES' => true]);

            if ($elements) {
                foreach ($elements as $pk => $delayTimestamp) {
                    if ($delayTimestamp > time()) {
                        $this->reenqueue($bucket, $delayTimestamp, $pk);
                    } else {
                        $this->moveToQueue($bucket, $pk);
                    }
                }
            }
            usleep(2000);
        }
    }
}
