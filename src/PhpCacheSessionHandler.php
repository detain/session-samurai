<?php

namespace Detain\SessionSamurai;

use Cache\Cache;
use SessionHandlerInterface;
use SessionIdInterface;
use SessionUpdateTimestampHandlerInterface;

class PhpCacheSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    protected $cache;
    protected $prefix;
    protected $lifetime;

    public function __construct(Cache $cache, $prefix = 'session_', $lifetime = 1440)
    {
        $this->cache = $cache;
        $this->prefix = $prefix;
        $this->lifetime = $lifetime;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName): bool
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
    public function read($sessionId)
    {
        $data = $this->cache->get($this->prefix . $sessionId);

        return $data !== false ? $data : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data): bool
    {
        return $this->cache->set($this->prefix . $sessionId, $data, $this->lifetime);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId): bool
    {
        return $this->cache->delete($this->prefix . $sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxLifetime)
    {
        return true;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    /**
     * {@inheritdoc}
     */
    public function create_sid()
    {
        return uniqid($this->prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($sessionId)
    {
        return preg_match('/^[a-zA-Z0-9,\-]{1,128}$/', $sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $sessionData)
    {
        return true;
    }
}
