<?php

namespace Detain\SessionSamurai;

class MySessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private string $savePath = '';

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
    public function read(string $sessionId): string
    {
        $sessionFile = $this->savePath . '/sess_' . $sessionId;
        if (file_exists($sessionFile)) {
            $data = file_get_contents($sessionFile);
            return $data !== false ? $data : '';
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
    public function gc(int $maxlifetime): int|false
    {
        $count = 0;
        foreach (glob($this->savePath . '/sess_*') ?: [] as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
                $count++;
            }
        }
        return $count;
    }

    /**
     * {@inheritdoc}
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * {@inheritdoc}
     */
    public function validateId(string $sessionId): bool
    {
        $sessionFile = $this->savePath . '/sess_' . $sessionId;
        return file_exists($sessionFile);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp(string $sessionId, string $sessionData): bool
    {
        $sessionFile = $this->savePath . '/sess_' . $sessionId;
        return touch($sessionFile);
    }
}
