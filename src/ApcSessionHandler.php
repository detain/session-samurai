<?php

namespace Detain\SessionSamurai;

class ApcSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private $lifetime;
    private $prefix;

    public function __construct($lifetime = 0, $prefix = 'apc_sess_')
    {
        $this->lifetime = $lifetime;
        $this->prefix = $prefix;
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
        return \apc_fetch($this->prefix . $session_id);
    }

    /**
     * {@inheritdoc}
     */
    public function write($session_id, $session_data): bool
    {
        return \apc_store($this->prefix . $session_id, $session_data, $this->lifetime);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($session_id): bool
    {
        return \apc_delete($this->prefix . $session_id);
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
        $sid = session_create_id();
        return $this->prefix . $sid;
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($session_id)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($session_id, $session_data)
    {
        return true;
    }
}
