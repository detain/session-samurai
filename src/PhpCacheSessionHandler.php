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

    public function open($savePath, $sessionName)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($sessionId)
    {
        $data = $this->cache->get($this->prefix . $sessionId);

        return $data !== false ? $data : '';
    }

    public function write($sessionId, $data)
    {
        return $this->cache->set($this->prefix . $sessionId, $data, $this->lifetime);
    }

    public function destroy($sessionId)
    {
        return $this->cache->delete($this->prefix . $sessionId);
    }

    public function gc($maxLifetime)
    {
        return true;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps 
    public function create_sid()
    {
        return uniqid($this->prefix);
    }

    public function validateId($sessionId)
    {
        return preg_match('/^[a-zA-Z0-9,\-]{1,128}$/', $sessionId);
    }

    public function updateTimestamp($sessionId, $sessionData)
    {
        return true;
    }
}
