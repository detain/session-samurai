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

    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($sessionId)
    {
        return $this->handler->read($sessionId);
    }

    public function write($sessionId, $data): bool
    {
        $this->handler->write($sessionId, $data);
    }

    public function destroy($sessionId): bool
    {
        $this->handler->destroy($sessionId);
    }

    public function gc($maxLifetime)
    {
        $this->handler->gc($maxLifetime);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid()
    {
        return $this->manager->driver()->generateSessionId();
    }

    public function validateId($sessionId)
    {
        return $this->handler->validateId($sessionId);
    }

    public function updateTimestamp($sessionId, $data)
    {
        return $this->handler->updateTimestamp($sessionId, $data);
    }
}
