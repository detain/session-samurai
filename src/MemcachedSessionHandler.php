<?php

namespace Detain\SessionSamurai;

class MemcachedSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    protected \Memcached $memcached;
    protected string $sessionName;
    protected int $expire = 0;
    protected string $prefix = '';

    public function __construct(\Memcached $memcached, string $prefix = 'sess-')
    {
        $this->memcached = $memcached;
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function open(string $path, string $name): bool
    {
        $this->sessionName = $name;
        $this->expire = (int) ini_get('session.gc_maxlifetime');
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
    public function read(string $id): string
    {
        $data = $this->memcached->get($this->prefix . $id);
        if ($data === false) {
            return '';
        }
        return is_string($data) ? $data : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $id, string $data): bool
    {
        return (bool) $this->memcached->set($this->prefix . $id, $data, $this->expire);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $id): bool
    {
        return (bool) $this->memcached->delete($this->prefix . $id);
    }

    /**
     * {@inheritdoc}
     */
    public function gc(int $max_lifetime): int|false
    {
        $this->expire = $max_lifetime;
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid(): string
    {
        do {
            $sessionId = bin2hex(random_bytes(32));
        } while ($this->memcached->get($this->prefix . $sessionId) !== false);

        return $sessionId;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp(string $id, string $data): bool
    {
        return (bool) $this->memcached->touch($this->prefix . $id, $this->expire);
    }

    /**
     * {@inheritdoc}
     */
    public function validateId(string $id): bool
    {
        return $this->memcached->get($this->prefix . $id) !== false;
    }
}
