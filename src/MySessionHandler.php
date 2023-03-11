<?php

namespace Detain\SessionSamurai;

class MySessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private $savePath;

    public function open($savePath, $sessionName)
    {
        $this->savePath = $savePath;
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($sessionId)
    {
        $sessionFile = $this->savePath . '/sess_' . $sessionId;
        if (file_exists($sessionFile)) {
            return file_get_contents($sessionFile);
        }
        return '';
    }

    public function write($sessionId, $data)
    {
        $sessionFile = $this->savePath . '/sess_' . $sessionId;
        return file_put_contents($sessionFile, $data) !== false;
    }

    public function destroy($sessionId)
    {
        $sessionFile = $this->savePath . '/sess_' . $sessionId;
        if (file_exists($sessionFile)) {
            unlink($sessionFile);
        }
        return true;
    }

    public function gc($maxlifetime)
    {
        foreach (glob($this->savePath . '/sess_*') as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }
        return true;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid()
    {
        return md5(uniqid());
    }

    public function validateId($sessionId)
    {
        $sessionFile = $this->savePath . '/sess_' . $sessionId;
        return file_exists($sessionFile);
    }

    public function updateTimestamp($sessionId, $sessionData)
    {
        $sessionFile = $this->savePath . '/sess_' . $sessionId;
        return touch($sessionFile);
    }
}
