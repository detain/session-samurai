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
        $value = \apcu_fetch($sessionId);
        return is_string($value) ? $value : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $sessionId, string $sessionData): bool
    {
        return (bool) \apcu_store($sessionId, $sessionData, $this->ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $sessionId): bool
    {
        \apcu_delete($sessionId);
        return true;
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
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * {@inheritdoc}
     */
    public function validateId(string $sessionId): bool
    {
        return (bool) preg_match('/^[0-9a-f]{64}$/', $sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp(string $sessionId, string $sessionData): bool
    {
        \apcu_store($sessionId, $sessionData, $this->ttl);
        return true;
    }
}
