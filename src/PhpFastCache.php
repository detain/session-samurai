<?php

namespace Detain\SessionSamurai;

use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Core\Item\ExtendedCacheItemInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\SessionHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\SessionIdInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\SessionUpdateTimestampHandlerInterface;

class PhpCacheSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
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
        $item = $this->cache->getItem($sessionId);
        return $item->get();
    }

    public function write($sessionId, $data)
    {
        $item = $this->cache->getItem($sessionId);
        $item->set($data);
        $item->expiresAfter(ini_get('session.gc_maxlifetime'));
        $this->cache->save($item);
    }

    public function destroy($sessionId)
    {
        $this->cache->deleteItem($sessionId);
    }

    public function gc($maxlifetime)
    {
        return true;
    }

    public function updateTimestamp($sessionId, $data)
    {
        $item = $this->cache->getItem($sessionId);
        $item->expiresAfter(ini_get('session.gc_maxlifetime'));
        $this->cache->save($item);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps 
    public function create_sid()
    {
        return bin2hex(random_bytes(32));
    }
}
