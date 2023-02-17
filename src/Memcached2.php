<?php

namespace Detain\SessionSamurai;

class Memcached2 implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    /**
    * @var \Memcached
    */
    protected \Memcached $handler;
    protected $expire;

    /**
    * Session Handler Constructor
    *
    * @param string $host the memcached host
    * @param int $port the memcached port
    * @param int $timeout timeout in seconds
    */
    public function __construct($host, $port, $timeout) {
        $this->handler = new Memcached();
        $this->handler->addServer($host, $port, $timeout);
    }

    /**
    * Open Session - Wrapper for SessionHandlerInterface
    *
    * @param string $save_path save path
    * @param string $session_name session name
    */
    public function open($save_path, $session_name) {
        return true;
    }

    /**
    * Close Session - Wrapper for SessionHandlerInterface
    */
    public function close() {
        return true;
    }

    /**
    * Write Session - Wrapper for SessionHandlerInterface
    *
    * @param string $session_id session id
    * @param string $data serialized session data
    */
    public function write($session_id, $data) {
        return $this->handler->set($session_id,$data,$this->expire);
    }

    /**
    * Read Session - Wrapper for SessionHandlerInterface
    *
    * @param string $session_id session id
    */
    public function read($session_id) {
        return $this->handler->get($session_id);
    }

    /**
    * Destroy Session - Wrapper for SessionHandlerInterface
    *
    * @param string $session_id session id
    */
    public function destroy($session_id) {
        return $this->handler->delete($session_id);
    }

    /**
    * Garbage Collection - Wrapper for SessionHandlerInterface
    *
    * @param int $maxLifetime the session lifetime in seconds
    */
    public function gc($maxLifetime) {

        $this->expire = $maxLifetime;

        return true;
    }
}
