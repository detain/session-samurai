<?php

namespace Detain\SessionSamurai;

use Redis;
use SessionHandlerInterface;
use SessionIdInterface;
use SessionUpdateTimestampHandlerInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class RedisSessionHandler
 *
 * A session handler that stores PHP session data in Redis.
 */
class RedisSessionHandler implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface
{
    /** @var Redis */
    private Redis $redis;

    /** @var int Session ttl in seconds */
    private int $ttl;

    /** @var string Prefix for all session keys in Redis */
    private string $keyPrefix;

    /**
     * RedisSessionHandler constructor.
     *
     * @param Redis|null $redis     An existing Redis connection (optional).
     * @param string     $host      Redis host (used if no $redis provided).
     * @param int        $port      Redis port.
     * @param int|null   $ttl  Session ttl in seconds (defaults to ini setting).
     * @param string     $keyPrefix Key prefix to isolate sessions.
     *
     * @throws RuntimeException If unable to connect to Redis.
     */
    public function __construct(Redis &$redis, int $ttl = 86400, string $keyPrefix = 'PHPREDIS_SESSION:') {
        $this->redis = &$redis;
        $this->ttl = $ttl;
        $this->keyPrefix = $keyPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        // Nothing to do since connection is done in constructor.
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return $this->redis->close();
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        $data = $this->redis->get($this->keyPrefix . $sessionId);
        return $data === false ? '' : $data;
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        $key = $this->keyPrefix . $sessionId;
        // Use SETEX to write data and expiry at once
        return (bool) $this->redis->setex($key, $this->ttl, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        $this->redis->del($this->keyPrefix . $sessionId);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxttl)
    {
        // Redis handles expiry automatically via TTL; no action needed.
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function create_sid()
    {
        // Generate a 32‐byte random ID, hex‐encoded, for 64 chars.
        return bin2hex(random_bytes(32));
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($sessionId)
    {
        // Check existence without resetting TTL
        return $this->redis->exists($this->keyPrefix . $sessionId) === 1;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $data)
    {
        $key = $this->keyPrefix . $sessionId;
        if (!$this->redis->exists($key)) {
            return false;
        }
        // Only update TTL, do not rewrite the payload
        return (bool) $this->redis->expire($key, $this->ttl);
    }
}
