<?php

namespace Detain\SessionSamurai;

class WinCacheSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    public function __construct()
    {
        if (!extension_loaded('wincache')) {
            throw new \RuntimeException('WinCache extension is not loaded');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function open(string $save_path, string $session_name): bool
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
    public function read(string $session_id): string
    {
        $value = wincache_ucache_get($session_id);
        return is_string($value) ? $value : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $session_id, string $session_data): bool
    {
        return (bool) wincache_ucache_set($session_id, $session_data, (int) ini_get('session.gc_maxlifetime'));
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $session_id): bool
    {
        wincache_ucache_delete($session_id);
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
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * {@inheritdoc}
     */
    public function validateId(string $session_id): bool
    {
        return (bool) preg_match('/^[a-f0-9]{64}$/i', $session_id);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp(string $session_id, string $session_data): bool
    {
        return (bool) wincache_ucache_set($session_id, $session_data, (int) ini_get('session.gc_maxlifetime'));
    }
}
