<?php

namespace Detain\SessionSamurai;

class APCuSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private int $ttl;

    public function __construct(int $ttl = 1800)
    {
        $this->ttl = $ttl;
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
        return \apcu_fetch($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $sessionData): bool
    {
        return \apcu_store($sessionId, $sessionData, $this->ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId): bool
    {
        return \apcu_delete($sessionId);
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
        return bin2hex(random_bytes(16));
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($sessionId)
    {
        return (bool) preg_match('/^[0-9a-f]{32}$/', $sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $sessionData)
    {
        return true;
    }
}
