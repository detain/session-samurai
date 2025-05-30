<?php

namespace Detain\SessionSamurai;

use League\Flysystem\FilesystemInterface;
use SessionHandlerInterface;
use SessionIdInterface;
use SessionUpdateTimestampHandlerInterface;

class FlySystemSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    protected $filesystem;
    protected $path;

    public function __construct(FilesystemInterface $filesystem, $path = '/')
    {
        $this->filesystem = $filesystem;
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function open($save_path, $name)
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
    public function read($session_id)
    {
        $session_path = $this->getSessionPath($session_id);
        if (!$this->filesystem->has($session_path)) {
            return '';
        }
        return $this->filesystem->read($session_path);
    }

    /**
     * {@inheritdoc}
     */
    public function write($session_id, $session_data): bool
    {
        $session_path = $this->getSessionPath($session_id);
        $this->filesystem->put($session_path, $session_data);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($session_id): bool
    {
        $session_path = $this->getSessionPath($session_id);
        if ($this->filesystem->has($session_path)) {
            $this->filesystem->delete($session_path);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        $expired_time = time() - $maxlifetime;
        $expired_sessions = $this->filesystem->listContents($this->path, true);
        foreach ($expired_sessions as $session) {
            if ($session['timestamp'] < $expired_time) {
                $this->filesystem->delete($session['path']);
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
    public function validateId($session_id)
    {
        $session_path = $this->getSessionPath($session_id);
        return $this->filesystem->has($session_path);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($session_id, $session_data)
    {
        $session_path = $this->getSessionPath($session_id);
        $timestamp = time();
        $this->filesystem->updateTimestamp($session_path, $timestamp);
        return true;
    }

    protected function getSessionPath($session_id)
    {
        return $this->path . '/' . $session_id;
    }
}
