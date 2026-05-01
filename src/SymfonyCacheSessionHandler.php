<?php

namespace Detain\SessionSamurai;

use Psr\Cache\CacheItemPoolInterface;

class SymfonyCacheSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private CacheItemPoolInterface $cache;
    private int $ttl;
    private string $prefix;

    public function __construct(CacheItemPoolInterface $cache, int $ttl = 0, string $prefix = 'session.')
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function open(string $savePath, string $sessionName): bool
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
    public function read(string $sessionId): string
    {
        $key = $this->prefix . $sessionId;
        $data = $this->cache->getItem($key);

        if (!$data->isHit()) {
            return '';
        }

        $value = $data->get();
        return is_string($value) ? $value : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $sessionId, string $data): bool
    {
        $key = $this->prefix . $sessionId;
        $item = $this->cache->getItem($key);
        $item->set($data);

        if ($this->ttl > 0) {
            $item->expiresAfter($this->ttl);
        }

        return $this->cache->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $sessionId): bool
    {
        $key = $this->prefix . $sessionId;

        return $this->cache->deleteItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function gc(int $maxLifetime): int|false
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function validateId(string $id): bool
    {
        $key = $this->prefix . $id;
        return $this->cache->getItem($key)->isHit();
    }

    /**
     * {@inheritdoc}
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp(string $sessionId, string $data): bool
    {
        return $this->write($sessionId, $data);
    }
}
