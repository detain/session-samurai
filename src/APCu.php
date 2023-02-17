<?php

class APCu implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface {

    private $ttl;

    public function __construct($ttl = 1800) {
        $this->ttl = $ttl;
    }

    public function open($savePath, $sessionName) {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($sessionId) {
        return apcu_fetch($sessionId);
    }

    public function write($sessionId, $sessionData) {
        return apcu_store($sessionId, $sessionData, $this->ttl);
    }

    public function destroy($sessionId) {
        return apcu_delete($sessionId);
    }

    public function gc($maxLifetime) {
        return true;
    }

    public function create_sid() {
        return bin2hex(random_bytes(16));
    }

    public function validateId($sessionId) {
        return (bool) preg_match('/^[0-9a-f]{32}$/', $sessionId);
    }

    public function updateTimestamp($sessionId, $sessionData) {
        return true;
    }

}
