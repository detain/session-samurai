<?php

namespace Detain\SessionSamurai;

class Memcached implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    /**
    * @var Memcached The memcached object
    */
    protected $memcached;
    protected $sessionName;
    protected $expire;
    /**
    * @var string The namespace prefix to prepend to session IDs
    */
    protected $prefix = '';

    /**
    * Create new memcached session save handler
    * @param \Memcached $memcached
    */
    public function __construct(\Memcached $memcached, string $prefix = 'sess-')
    {
        $this->memcached = $memcached;
        $this->prefix = $prefix;
    }

    /**
    * Open session
    *
    * @param string $savePath
    * @param string $name
    * @return boolean
    */
    //public function open(string $path, string $name): bool
    public function open($path, $name)
    {
        // Note: session save path is not used
        $this->sessionName = $name;
        $this->expire = ini_get('session.gc_maxlifetime');
        return true;
    }

    /**
    * Close session
    *
    * @return boolean
    */
    public function close(): bool
    {
        // return value should be true for success or false for failure
        return true;
    }

    /**
    * Read session data
    *
    * @param string $id
    * @return string|false
    */
    #[\ReturnTypeWillChange]
    public function read($id) //: string|false
    //public function read(string $id) //: string|false
    {
        $_SESSION = json_decode((string) $this->memcached->get($this->prefix . $id), true);
        if (isset($_SESSION) && !empty($_SESSION) && $_SESSION != null) {
            return session_encode();
        }
        return '';
    }

    /**
    * Write session data
    *
    * @param string $id
    * @param string $data
    * @return boolean
    */
    public function write($id, $data)
    //public function write(string $id, string $data): bool
    {
        // note: $data is not used as it has already been serialised by PHP,
        // so we use $_SESSION which is an unserialised version of $data.
        return (bool) $this->memcached->set($this->prefix . $id, json_encode($_SESSION), $this->expire);

    }

    /**
    * Destroy session
    *
    * @param string $id
    * @return boolean
    */
    public function destroy($id)
    //public function destroy(string $id): bool
    {
        return (bool) $this->memcached->delete($this->prefix . $id);
    }

    /**
    * Garbage collect. Memcache handles this with expiration times.
    *
    * @param int $maxlifetime
    * @return int|false true successs false failure?
    */
    #[\ReturnTypeWillChange]
    public function gc($max_lifetime) //: int|false
    //public function gc(int $max_lifetime) //: int|false
    {
        // let memcached handle this with expiration time
        $this->expire = $max_lifetime;
        return true;
    }

    /**
    * Creates a new SID
    *
    * @return string
    */
    public function create_sid()
    //public function create_sid(): string
    {
        // available since PHP 5.5.1
        // invoked internally when a new session id is needed
        // no parameter is needed and return value should be the new session id created
       do {
            $sessionId = md5(uniqid('', true));
        } while ($this->memcached->get($this->prefix . $sessionId));

        return $sessionId;
    }

    /**
    * Update the session timestamp
    *
    * @param string $id
    * @param string $data
    * @return bool
    */
    public function updateTimestamp($id, $data)
    //public function updateTimestamp(string $id, string $data): bool
    {
        // implements SessionUpdateTimestampHandlerInterface::validateId()
        // available since PHP 7.0
        // return value should be true for success or false for failure
        return (bool) $this->memcached->touch($this->prefix . $id, 0);
    }

    /**
    * Verifies a session id
    *
    * @param string $id
    * @return bool
    */
    public function validateId($id)
    //public function validateId(string $id): bool
    {
        // implements SessionUpdateTimestampHandlerInterface::validateId()
        // available since PHP 7.0
        // return value should be true if the session id is valid otherwise false
        // if false is returned a new session id will be generated by php internally
        return (bool) $this->idExists($id);
    }


    /**
    * Checks if a session ID exists in the memcached instance.
    *
    * @param string $sessionId The session ID to check
    *
    * @return bool
    */
    public function idExists($id)
    {
        return (bool) $this->memcached->get($this->prefix . $id);
    }
}
