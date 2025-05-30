<?php

namespace Detain\SessionSamurai;

class MySessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private $savePath;

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName): bool
    {
        $this->savePath = $savePath;
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
        $sessionFile = $this->savePath . '/sess_' . $sessionId;
        if (file_exists($sessionFile)) {
            return file_get_contents($sessionFile);
        }
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data): bool
    {
        $sessionFile = $this->savePath . '/sess_' . $sessionId;
        return file_put_contents($sessionFile, $data) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId): bool
    {
        $sessionFile = $this->savePath . '/sess_' . $sessionId;
        if (file_exists($sessionFile)) {
            unlink($sessionFile);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
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
    /**
     * {@inheritdoc}
     */
    public function create_sid()
    {
        return md5(uniqid());
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($sessionId)
    {
        $sessionFile = $this->savePath . '/sess_' . $sessionId;
        return file_exists($sessionFile);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $sessionData)
    {
        $sessionFile = $this->savePath . '/sess_' . $sessionId;
        return touch($sessionFile);
    }
}
