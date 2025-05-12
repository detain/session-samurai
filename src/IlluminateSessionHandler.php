<?php

namespace Detain\SessionSamurai;

use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Arr;

class IlluminateSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    protected $manager;
    protected $handler;

    public function __construct(SessionManager $manager)
    {
        $this->manager = $manager;
        $this->handler = $this->manager->driver()->getHandler();
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
        return $this->handler->read($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data): bool
    {
        $this->handler->write($sessionId, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId): bool
    {
        $this->handler->destroy($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxLifetime)
    {
        $this->handler->gc($maxLifetime);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    /**
     * {@inheritdoc}
     */
    public function create_sid()
    {
        return $this->manager->driver()->generateSessionId();
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($sessionId)
    {
        return $this->handler->validateId($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $data)
    {
        return $this->handler->updateTimestamp($sessionId, $data);
    }
}
