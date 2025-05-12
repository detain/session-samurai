<?php

namespace Detain\SessionSamurai;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\AbstractSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\SessionHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\SessionIdInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\SessionUpdateTimestampHandlerInterface;

class SymfonyCache2SessionHandler extends AbstractSessionHandler implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface
{
    private $cache;
    private $ttl;

    public function __construct(CacheItemPoolInterface $cache, $ttl = 0)
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($sessionId)
    {
        $item = $this->cache->getItem($sessionId);
        if (!$item->isHit()) {
            return '';
        }
        return $item->get();
    }

    public function write($sessionId, $sessionData): bool
    {
        $item = $this->cache->getItem($sessionId);
        $item->set($sessionData);
        if ($this->ttl > 0) {
            $item->expiresAfter($this->ttl);
        }
        return $this->cache->save($item);
    }

    public function destroy($sessionId): bool
    {
        return $this->cache->deleteItem($sessionId);
    }

    public function gc($maxlifetime)
    {
        // The garbage collector is handled automatically by the cache implementation.
        return true;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid()
    {
        return bin2hex(random_bytes(32));
    }

    public function updateTimestamp($sessionId, $sessionData)
    {
        $item = $this->cache->getItem($sessionId);
        if ($this->ttl > 0) {
            $item->expiresAfter($this->ttl);
        }
        return $this->cache->save($item);
    }
}
