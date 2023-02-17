<?php

class OpCache implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface {
    private $sessionId;

    public function __construct() {
        // Initialize the OpCache extension
        opcache_reset();
    }

    public function open($savePath, $sessionName) {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($sessionId) {
        $cached = opcache_get($sessionId);
        if ($cached === false) {
            return '';
        }
        return $cached['payload'];
    }

    public function write($sessionId, $sessionData) {
        opcache_compile_file(__FILE__);
        opcache_set($sessionId, ['payload' => $sessionData]);
        return true;
    }

    public function destroy($sessionId) {
        opcache_delete($sessionId);
        return true;
    }

    public function gc($maxLifetime) {
        return true;
    }

    public function create_sid() {
        $this->sessionId = bin2hex(random_bytes(32));
        return $this->sessionId;
    }

    public function validateId($sessionId) {
        $cached = opcache_get($sessionId);
        return $cached !== false;
    }

    public function updateTimestamp($sessionId, $sessionData) {
        $cached = opcache_get($sessionId);
        if ($cached !== false) {
            opcache_set($sessionId, ['payload' => $sessionData]);
        }
        return true;
    }
}
