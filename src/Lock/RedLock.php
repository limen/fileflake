<?php
/*
 * This file is part of the Fileflake package.
 *
 * (c) LI Mengxiang <limengxiang876@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Limen\Fileflake\Lock;

use Limen\Fileflake\Contracts\LockContract;
use Limen\RedModel\Model as RedModel;

/**
 * Class RedLock
 *
 * Lock for avoiding concurrency conflict
 *
 * @package Limen\RedEnvelope\Lock
 *
 * @author LI Mengxiang <limengxiang876@gmail.com>
 */
class RedLock extends RedModel implements LockContract
{
    /**
     * Red envelope id to lock
     * @var mixed
     */
    protected $id;

    protected $type = RedModel::TYPE_STRING;

    protected $key = 'fileflake:{id}:lock';

    /**
     * Max retry times to lock
     * @var int
     */
    protected $maxRetryTimes = 100;

    /**
     * lock ttl in millisecond
     * @var int
     */
    protected $ttl = 1000;

    /**
     * @param array $parameters
     * @param array $options
     */
    public function initRedisClient($parameters, $options = null)
    {
        if (!isset($parameters['host']) || empty($parameters['host'])) {
            $parameters['host'] = 'localhost';
        }
        if (!isset($parameters['port']) || empty($parameters['port'])) {
            $parameters['port'] = 6379;
        }

        parent::initRedisClient($parameters, $options);
    }

    /**
     * @param int $times
     * @return $this
     */
    public function setMaxRetryTimes($times)
    {
        $this->maxRetryTimes = $times;

        return $this;
    }

    /**
     * @param int $ttl
     * @return $this
     */
    public function setTTL($ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getMaxRetryTimes()
    {
        return $this->maxRetryTimes;
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Lock item
     * @param string|int $id Resource id
     * @return bool return true when success
     */
    public function lock($id)
    {
        $success = false;

        for ($i = 0; $i < $this->maxRetryTimes; $i++) {
            if ($this->newQuery()->where('id', $id)->setnx(1)) {
                $this->newQuery()->where('id', $id)->pexpire($this->getTtl());
                $success = true;
                break;
            }
            // sleep 1 millisecond
            usleep(1000);
        }

        return $success;
    }

    /**
     * Unlock item
     * @param $id
     * @return bool
     */
    public function unlock($id)
    {
        return $this->destroy($id);
    }

    /**
     * Check if item locked
     * @param $id
     * @return bool
     */
    public function isLocked($id)
    {
        return (bool)$this->find($id);
    }
}