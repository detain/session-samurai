<?php

namespace Detain\SessionSamurai;

use Phpfastcache\CacheManager;
use Phpfastcache\Drivers\Files\Driver;
use SessionHandlerInterface;
use SessionIdInterface;
use SessionUpdateTimestampHandlerInterface;

class PhpFastCache2SessionHandler implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface
{
    protected $cache;

    public function __construct()
    {
        $this->cache = CacheManager::getInstance('files');
        $this->cache->driver()->setPath(sys_get_temp_dir());
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
    public function read($session_id)
    {
        return $this->cache->getItem($session_id)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function write($session_id, $session_data): bool
    {
        $item = $this->cache->getItem($session_id)->set($session_data);
        $item->expiresAfter(ini_get('session.gc_maxlifetime'));
        return $this->cache->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($session_id): bool
    {
        return $this->cache->deleteItem($session_id);
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
    public function create_sid()
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($session_id)
    {
        return ctype_alnum($session_id) && strlen($session_id) === 32;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($session_id, $session_data)
    {
        $item = $this->cache->getItem($session_id);
        if (!$item->isHit()) {
            return false;
        }
        $item->expiresAfter(ini_get('session.gc_maxlifetime'));
        return $this->cache->save($item);
    }
}
