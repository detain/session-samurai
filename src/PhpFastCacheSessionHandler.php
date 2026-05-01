<?php

namespace Detain\SessionSamurai;

use Psr\Cache\CacheItemPoolInterface;

class PhpFastCacheSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private CacheItemPoolInterface $cache;

    public function __construct(CacheItemPoolInterface $cache = null)
    {
        if ($cache === null) {
            $cacheConfig = new \Phpfastcache\Config\ConfigurationOption([
                'path' => sys_get_temp_dir(),
                'itemDetailedDate' => true,
            ]);
            $cache = \Phpfastcache\CacheManager::getInstance('files', $cacheConfig);
        }
        $this->cache = $cache;
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
        $item = $this->cache->getItem($sessionId);
        if (!$item->isHit()) {
            return '';
        }
        return (string) $item->get();
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $sessionId, string $data): bool
    {
        $item = $this->cache->getItem($sessionId);
        $item->set($data);
        $item->expiresAfter((int) ini_get('session.gc_maxlifetime'));
        $this->cache->save($item);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $sessionId): bool
    {
        $this->cache->deleteItem($sessionId);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc(int $maxlifetime): int|false
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function validateId(string $sessionId): bool
    {
        return $this->cache->getItem($sessionId)->isHit();
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp(string $sessionId, string $data): bool
    {
        $item = $this->cache->getItem($sessionId);
        $item->expiresAfter((int) ini_get('session.gc_maxlifetime'));
        $this->cache->save($item);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid(): string
    {
        return bin2hex(random_bytes(32));
    }
}
