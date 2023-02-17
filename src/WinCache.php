<?php

class WinCacheSessionHandler implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface {

    public function __construct() {
        if (!extension_loaded('wincache')) {
            throw new RuntimeException('WinCache extension is not loaded');
        }
    }

    public function open($save_path, $session_name) {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($session_id) {
        return wincache_ucache_get($session_id);
    }

    public function write($session_id, $session_data) {
        return wincache_ucache_set($session_id, $session_data, ini_get('session.gc_maxlifetime'));
    }

    public function destroy($session_id) {
        wincache_ucache_delete($session_id);
        return true;
    }

    public function gc($maxlifetime) {
        return true;
    }

    public function create_sid() {
        return bin2hex(random_bytes(32));
    }

    public function validateId($session_id) {
        return (bool) preg_match('/^[a-f0-9]{64}$/i', $session_id);
    }

    public function updateTimestamp($session_id, $session_data) {
        return true;
    }
}

