<?php

namespace Detain\SessionSamurai;

use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Core\Item\ExtendedCacheItemInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\SessionHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\SessionIdInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\SessionUpdateTimestampHandlerInterface;

class PhpFastCacheSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private $cache;

    public function __construct()
    {
        $cacheConfig = new ConfigurationOption([
            'path' => sys_get_temp_dir(),
            'itemDetailedDate' => true,
        ]);

        $this->cache = CacheManager::getInstance('files', $cacheConfig);
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
        $item = $this->cache->getItem($sessionId);
        return $item->get();
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data): bool
    {
        $item = $this->cache->getItem($sessionId);
        $item->set($data);
        $item->expiresAfter(ini_get('session.gc_maxlifetime'));
        $this->cache->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId): bool
    {
        $this->cache->deleteItem($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($sessionId)
    {
        $item = $this->cache->getItem($sessionId);
        return $item->get();
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $data)
    {
        $item = $this->cache->getItem($sessionId);
        $item->expiresAfter(ini_get('session.gc_maxlifetime'));
        $this->cache->save($item);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    /**
     * {@inheritdoc}
     */
    public function create_sid()
    {
        return bin2hex(random_bytes(32));
    }
}
