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

    /** @var int Session lifetime in seconds */
    private int $ttl;

    /**
    * RedisSessionHandler constructor.
    *
    * @param \Redis $redis     An existing Redis connection
    * @param int $ttl Session lifetime in seconds (defaults to 86400)
    *
    * @throws RuntimeException If unable to connect to Redis.
    */
    public function __construct(\Redis &$redis, int $ttl = 86400)
    {
        $this->redis = &$redis;
        $this->ttl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function open($save_path, $session_name): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($session_id): string
    {
        $data = $this->redis->get($session_id);
        return $data !== false ? $data : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($session_id, $session_data): bool
    {
        return $this->redis->setex($session_id, $this->ttl, $session_data);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($session_id): bool
    {
        return $this->redis->del($session_id) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    /**
     * {@inheritdoc}
     */
    public function create_sid(): string
    {
        $length = 32;
        return bin2hex(random_bytes($length));
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($session_id): bool
    {
        return ctype_xdigit($session_id) && strlen($session_id) === 64;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($session_id, $session_data): bool
    {
        return $this->redis->expire($session_id, $this->ttl);
    }
}
