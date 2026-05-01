<?php

namespace Detain\SessionSamurai;

/**
 * Session handler using PHP files compiled by OPcache for fast reads.
 * Data is serialized into PHP files under $cacheDir; OPcache keeps them
 * in memory so subsequent reads bypass disk I/O.
 */
class OpCacheSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private string $cacheDir;

    public function __construct(string $cacheDir = '')
    {
        $this->cacheDir = $cacheDir !== '' ? $cacheDir : sys_get_temp_dir() . '/opcache_sessions';
    }

    /**
     * {@inheritdoc}
     */
    public function open(string $savePath, string $sessionName): bool
    {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0700, true);
        }
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
        $file = $this->getFilePath($sessionId);
        if (!file_exists($file)) {
            return '';
        }
        $data = @include($file);
        return is_string($data) ? $data : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $sessionId, string $sessionData): bool
    {
        $file = $this->getFilePath($sessionId);
        $content = '<?php return ' . var_export($sessionData, true) . ';';
        if (file_put_contents($file, $content, LOCK_EX) === false) {
            return false;
        }
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $sessionId): bool
    {
        $file = $this->getFilePath($sessionId);
        if (file_exists($file)) {
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($file, true);
            }
            unlink($file);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc(int $maxLifetime): int|false
    {
        $count = 0;
        $expiry = time() - $maxLifetime;
        foreach (glob($this->cacheDir . '/*.php') ?: [] as $file) {
            if (filemtime($file) < $expiry) {
                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate($file, true);
                }
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
        return file_exists($this->getFilePath($sessionId));
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp(string $sessionId, string $sessionData): bool
    {
        $file = $this->getFilePath($sessionId);
        if (!file_exists($file)) {
            return false;
        }
        return touch($file);
    }

    private function getFilePath(string $sessionId): string
    {
        return $this->cacheDir . '/' . preg_replace('/[^a-f0-9]/', '', $sessionId) . '.php';
    }
}
