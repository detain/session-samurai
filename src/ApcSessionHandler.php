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

    public function open($save_path, $session_name)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($session_id)
    {
        return apc_fetch($this->prefix . $session_id);
    }

    public function write($session_id, $session_data)
    {
        return apc_store($this->prefix . $session_id, $session_data, $this->lifetime);
    }

    public function destroy($session_id)
    {
        return apc_delete($this->prefix . $session_id);
    }

    public function gc($maxlifetime)
    {
        return true;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid()
    {
        $sid = session_create_id();
        return $this->prefix . $sid;
    }

    public function validateId($session_id)
    {
        return true;
    }

    public function updateTimestamp($session_id, $session_data)
    {
        return true;
    }
}
