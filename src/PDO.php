<?php

namespace Detain\SessionSamurai;

class PDO implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    /**
    * @var \Memcached
    */
    protected \Memcached $memcached;

    /**
     * Create new memcached session save handler
     * @param \Memcached $memcached
     */
    public function __construct(\Memcached $memcached)
    {
        $this->memcached = $memcached;
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
     * Destroy session
     *
     * @param string $id
     * @return boolean
     */
    public function destroy(string $id): bool
    {
        return $this->memcached->delete("sessions/{$id}");
    }

    /**
     * Garbage collect. Memcache handles this with expiration times.
     *
     * @param int $maxlifetime
     * @return int|false true successs false failure?
     */
    #[\ReturnTypeWillChange]
    public function gc(int $max_lifetime) //: int|false
    {
        // let memcached handle this with expiration time
        return true;
    }

    /**
     * Open session
     *
     * @param string $savePath
     * @param string $name
     * @return boolean
     */
    public function open(string $path, string $name): bool
    {
        // Note: session save path is not used
        $this->sessionName = $name;
        $this->lifetime = ini_get('session.gc_maxlifetime');
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string|false
     */
    #[\ReturnTypeWillChange]
    public function read(string $id) //: string|false
    {
        $_SESSION = json_decode($this->memcached->get("sessions/{$id}"), true);

        if (isset($_SESSION) && !empty($_SESSION) && $_SESSION != null)
        {
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
    public function write(string $id, string $data): bool
    {
        // note: $data is not used as it has already been serialised by PHP,
        // so we use $_SESSION which is an unserialised version of $data.
        return $this->memcached->set("sessions/{$id}", json_encode($_SESSION), $this->lifetime);
    }

    /**
    * Creates a new SID
    *
    * @return string
    */
    public function create_sid(): string
    {
        // available since PHP 5.5.1
        // invoked internally when a new session id is needed
        // no parameter is needed and return value should be the new session id created
    }

    /**
    * Update the session timestamp
    *
    * @param string $id
    * @param string $data
    * @return bool
    */
    public function updateTimestamp(string $id, string $data): bool
    {
        // implements SessionUpdateTimestampHandlerInterface::validateId()
        // available since PHP 7.0
        // return value should be true for success or false for failure
    }

    /**
    * Verifies a session id
    *
    * @param string $id
    * @return bool
    */
    public function validateId(string $id): bool
    {
        // implements SessionUpdateTimestampHandlerInterface::validateId()
        // available since PHP 7.0
        // return value should be true if the session id is valid otherwise false
        // if false is returned a new session id will be generated by php internally
    }
}
