<?php

namespace Detain\SessionSamurai;

use League\Flysystem\FilesystemOperator;

class FlySystemSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    protected FilesystemOperator $filesystem;
    protected string $path;

    public function __construct(FilesystemOperator $filesystem, string $path = '/')
    {
        $this->filesystem = $filesystem;
        $this->path = rtrim($path, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function open(string $save_path, string $name): bool
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
    public function read(string $session_id): string
    {
        $session_path = $this->getSessionPath($session_id);
        try {
            return $this->filesystem->read($session_path);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $session_id, string $session_data): bool
    {
        $session_path = $this->getSessionPath($session_id);
        $this->filesystem->write($session_path, $session_data);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $session_id): bool
    {
        $session_path = $this->getSessionPath($session_id);
        try {
            $this->filesystem->delete($session_path);
        } catch (\Throwable $e) {
            // file may not exist
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc(int $maxlifetime): int|false
    {
        $expired_time = time() - $maxlifetime;
        $count = 0;
        foreach ($this->filesystem->listContents($this->path) as $item) {
            if ($item->lastModified() < $expired_time) {
                $this->filesystem->delete($item->path());
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
    public function validateId(string $session_id): bool
    {
        $session_path = $this->getSessionPath($session_id);
        return $this->filesystem->fileExists($session_path);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp(string $session_id, string $session_data): bool
    {
        $session_path = $this->getSessionPath($session_id);
        try {
            $existing = $this->filesystem->read($session_path);
            $this->filesystem->write($session_path, $existing);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function getSessionPath(string $session_id): string
    {
        return $this->path . '/' . $session_id;
    }
}
