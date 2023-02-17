<?php

namespace Detain\SessionSamurai;

// Redis session handler class
class Redis2 implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    // Namespace for keys for Redis
    public const NS = 'session';

    // TTL in seconds
    public const DEFAULT_TTL = 86400;

    // Instance of redis
    protected $redis;

    // Active session id
    protected $sessionId;

    // Flag used to determine if changes were made to the session
    protected $isChanged = false;

    // New instance of redis session handler
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    // Closes the session
    public function close()
    {
        if (!$this->isChanged) {
            return true;
        }
        $keys = $this->redis->keys($this->sessionId . ':*');
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
        return true;
    }

    // Destroys n active session
    public function destroy($sessionId)
    {
        $this->redis->del($sessionId);
    }

    // Cleanup old session
    public function gc($maxlifetime)
    {
        $interval = $maxlifetime * 1000;
        $this->redis->zremrangebyscore($this->sessionId . ':timestamp', 0, (microtime(true) * 1000 - $interval));
    }

    // Gets the session id
    public function getSessionId()
    {
        return $this->sessionId;
    }

    // Reads data from active session
    public function read($sessionId)
    {
        $this->sessionId = $sessionId;
        $data = $this->redis->get($this->sessionId);
        return $data ? $data : '';
    }

    // Writes data to the active session
    public function write($sessionId, $data)
    {
        $this->isChanged = true;
        $this->sessionId = $sessionId;
        $this->redis->setex($this->sessionId, self::DEFAULT_TTL, $data);
        $this->redis->zadd($this->sessionId . ':timestamp', microtime(true) * 1000, microtime(true) * 1000);
        return true;
    }

    // Updates the timestamp of the active session
    public function updateTimestamp($sessionId, $data)
    {
        $this->sessionId = $sessionId;
        $this->redis->zadd($this->sessionId . ':timestamp', microtime(true) * 1000, microtime(true) * 1000);
        return true;
    }
}
