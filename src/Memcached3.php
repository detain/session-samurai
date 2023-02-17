<?php
declare(strict_types=1);

class Memcached3 implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface
{
    /**
     * @var Memcached The memcached object
     */
    protected $memcached;

    /**
     * @var string The namespace prefix to prepend to session IDs
     */
    protected $prefix;

    /**
     * Constructor.
     *
     * @param Memcached $memcached The memcached instance
     * @param string    $prefix    A prefix to apply to the session ID
     */
    public function __construct(Memcached $memcached, string $prefix = '')
    {
        $this->memcached = $memcached;
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionId)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        return (string) $this->memcached->get($this->prefix . $sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        return (bool) $this->memcached->set($this->prefix . $sessionId, $data, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        return (bool) $this->memcached->delete($this->prefix . $sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function create_sid()
    {
        do {
            $sessionId = md5(uniqid('', true));
        } while ($this->memcached->get($this->prefix . $sessionId));

        return $sessionId;
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($sessionId)
    {
        return (bool) $this->idExists($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $data)
    {
        // "touch" the session
        return (bool) $this->memcached->touch($this->prefix . $sessionId, 0);
    }

    /**
     * Checks if a session ID exists in the memcached instance.
     *
     * @param string $sessionId The session ID to check
     *
     * @return bool
     */
    public function idExists($sessionId)
    {
        return (bool) $this->memcached->get($this->prefix . $sessionId);
    }
}
