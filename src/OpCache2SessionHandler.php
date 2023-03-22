<?php

namespace Detain\SessionSamurai;

class OpCache2SessionHandler implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface
{
    protected $savePath;

    public function __construct($savePath) {
        $this->savePath = $savePath;
    }

    public function open($savePath, $sessionName) {
        if (!$this->savePath) {
            $this->savePath = $savePath;
        }
        return true;
    }

    public function close() {
        return true;
    }

    public function read($sessionId) {
        $file = $this->savePath . '/sess_' . $sessionId;
        if (!file_exists($file)) {
            return '';
        }
        $data = opcache_compile_file($file);
        return (string) $data;
    }

    public function write($sessionId, $data) {
        $file = $this->savePath . '/sess_' . $sessionId;
        $data = opcache_invalidate($file, true);
        return file_put_contents($file, $data) === false ? false : true;
    }

    public function destroy($sessionId) {
        $file = $this->savePath . '/sess_' . $sessionId;
        if (file_exists($file)) {
            unlink($file);
        }
        return true;
    }

    public function gc($lifetime) {
        foreach (glob($this->savePath . '/sess_*') as $file) {
            if (filemtime($file) + $lifetime < time()) {
                unlink($file);
            }
        }
        return true;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid() {
        return md5(uniqid());
    }

    public function validateId($sessionId) {
        return (bool) preg_match('/^[a-zA-Z0-9]{32}$/', $sessionId);
    }

    public function updateTimestamp($sessionId, $data) {
        $file = $this->savePath . '/sess_' . $sessionId;
        $data = opcache_invalidate($file, true);
        return file_put_contents($file, $data) === false ? false : true;
    }
}
