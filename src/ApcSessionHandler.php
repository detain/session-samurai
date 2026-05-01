<?php

namespace Detain\SessionSamurai;

class ApcSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private int $lifetime;
    private string $prefix;

    public function __construct(int $lifetime = 0, string $prefix = 'apc_sess_')
    {
        $this->lifetime = $lifetime;
        $this->prefix = $prefix;
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
        $value = \apc_fetch($this->prefix . $session_id);
        return is_string($value) ? $value : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $session_id, string $session_data): bool
    {
        return (bool) \apc_store($this->prefix . $session_id, $session_data, $this->lifetime);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $session_id): bool
    {
        \apc_delete($this->prefix . $session_id);
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
        return (bool) preg_match('/^[0-9a-f]{64}$/', $session_id);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp(string $session_id, string $session_data): bool
    {
        \apc_store($this->prefix . $session_id, $session_data, $this->lifetime);
        return true;
    }
}
