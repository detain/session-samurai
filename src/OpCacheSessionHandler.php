<?php

namespace Detain\SessionSamurai;

class OpCacheSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private $sessionId;

    public function __construct()
    {
        // Initialize the OpCache extension
        \opcache_reset();
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
        $cached = \opcache_get($sessionId);
        if ($cached === false) {
            return '';
        }
        return $cached['payload'];
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $sessionData): bool
    {
        \opcache_compile_file(__FILE__);
        \opcache_set($sessionId, ['payload' => $sessionData]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId): bool
    {
        \opcache_delete($sessionId);
        return true;
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
        $this->sessionId = bin2hex(random_bytes(32));
        return $this->sessionId;
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($sessionId)
    {
        $cached = \opcache_get($sessionId);
        return $cached !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $sessionData)
    {
        $cached = \opcache_get($sessionId);
        if ($cached !== false) {
            \opcache_set($sessionId, ['payload' => $sessionData]);
        }
        return true;
    }
}
