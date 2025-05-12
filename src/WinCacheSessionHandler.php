<?php

namespace Detain\SessionSamurai;

class WinCacheSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    public function __construct()
    {
        if (!extension_loaded('wincache')) {
            throw new RuntimeException('WinCache extension is not loaded');
        }
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
        return wincache_ucache_get($session_id);
    }

    /**
     * {@inheritdoc}
     */
    public function write($session_id, $session_data): bool
    {
        return wincache_ucache_set($session_id, $session_data, ini_get('session.gc_maxlifetime'));
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($session_id): bool
    {
        wincache_ucache_delete($session_id);
        return true;
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
        return bin2hex(random_bytes(32));
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($session_id)
    {
        return (bool) preg_match('/^[a-f0-9]{64}$/i', $session_id);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($session_id, $session_data)
    {
        return true;
    }
}
