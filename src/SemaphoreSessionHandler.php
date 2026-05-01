<?php

namespace Detain\SessionSamurai;

class SemaphoreSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private const VAR_DATA = 1;
    private const VAR_TIMESTAMP = 2;

    private int $sessionLifetime = 3600;

    /**
     * {@inheritdoc}
     */
    public function open(string $savePath, string $sessionName): bool
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
    public function read(string $sessionId): string
    {
        $key = $this->sessionToKey($sessionId);
        $lock = sem_get($key);
        if ($lock === false) {
            return '';
        }
        sem_acquire($lock);
        $shm = @shm_attach($key);
        if ($shm === false) {
            sem_release($lock);
            return '';
        }
        $rawData = shm_has_var($shm, self::VAR_DATA) ? shm_get_var($shm, self::VAR_DATA) : '';
        $data = is_string($rawData) ? $rawData : '';
        shm_detach($shm);
        sem_release($lock);
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $sessionId, string $sessionData): bool
    {
        $key = $this->sessionToKey($sessionId);
        $lock = sem_get($key);
        if ($lock === false) {
            return false;
        }
        sem_acquire($lock);
        $shm = shm_attach($key);
        if ($shm === false) {
            sem_release($lock);
            return false;
        }
        shm_put_var($shm, self::VAR_DATA, $sessionData);
        shm_put_var($shm, self::VAR_TIMESTAMP, time());
        shm_detach($shm);
        sem_release($lock);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $sessionId): bool
    {
        $key = $this->sessionToKey($sessionId);
        $lock = sem_get($key);
        if ($lock === false) {
            return true;
        }
        sem_acquire($lock);
        $shm = @shm_attach($key);
        if ($shm !== false) {
            shm_remove($shm);
        }
        sem_release($lock);
        sem_remove($lock);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc(int $maxLifetime): int|false
    {
        return 0;
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
        $key = $this->sessionToKey($sessionId);
        $lock = sem_get($key);
        if ($lock === false) {
            return false;
        }
        sem_acquire($lock);
        $shm = @shm_attach($key);
        if ($shm === false) {
            sem_release($lock);
            return false;
        }
        $exists = shm_has_var($shm, self::VAR_DATA);
        if ($exists && shm_has_var($shm, self::VAR_TIMESTAMP)) {
            $tsRaw = shm_get_var($shm, self::VAR_TIMESTAMP);
            $ts = is_int($tsRaw) ? $tsRaw : 0;
            if ($ts + $this->sessionLifetime < time()) {
                $exists = false;
            }
        }
        shm_detach($shm);
        sem_release($lock);
        return $exists;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp(string $sessionId, string $sessionData): bool
    {
        $key = $this->sessionToKey($sessionId);
        $lock = sem_get($key);
        if ($lock === false) {
            return false;
        }
        sem_acquire($lock);
        $shm = @shm_attach($key);
        if ($shm === false) {
            sem_release($lock);
            return false;
        }
        shm_put_var($shm, self::VAR_TIMESTAMP, time());
        shm_detach($shm);
        sem_release($lock);
        return true;
    }

    private function sessionToKey(string $sessionId): int
    {
        return abs(crc32($sessionId)) + 1;
    }
}
