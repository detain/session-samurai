<?php

namespace Detain\SessionSamurai;

class RedisSessionHandler implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface
{
    private $redis;
    private $ttl;

    public function __construct(\Redis $redis, int $ttl = 86400)
    {
        $this->redis = $redis;
        $this->ttl = $ttl;
    }

    public function open($save_path, $session_name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($session_id): string
    {
        $data = $this->redis->get($session_id);
        return $data !== false ? $data : '';
    }

    public function write($session_id, $session_data): bool
    {
        return $this->redis->setex($session_id, $this->ttl, $session_data);
    }

    public function destroy($session_id): bool
    {
        return $this->redis->del($session_id) > 0;
    }

    public function gc($maxlifetime): bool
    {
        return true;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid(): string
    {
        $length = 32;
        $bytes = random_bytes($length);
        return bin2hex($bytes);
    }

    public function validateId($session_id): bool
    {
        return ctype_xdigit($session_id) && strlen($session_id) === 64;
    }

    public function updateTimestamp($session_id, $session_data): bool
    {
        return $this->redis->expire($session_id, $this->ttl);
    }
}
