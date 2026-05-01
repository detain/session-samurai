<?php

namespace Detain\SessionSamurai;

class FileSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private string $savePath = '';

    /**
     * {@inheritdoc}
     */
    public function close(): bool
    {
        // return value should be true for success or false for failure
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $id): bool
    {
        $file = "$this->savePath/sess_$id";
        if (file_exists($file)) {
            unlink($file);
        }
        // return value should be true for success or false for failure
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc(int $max_lifetime): int|false
    {
        $count = 0;
        foreach (glob("$this->savePath/sess_*") ?: [] as $file) {
            if (filemtime($file) + $max_lifetime < time() && file_exists($file)) {
                unlink($file);
                $count++;
            }
        }
        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function open(string $path, string $name): bool
    {
        $this->savePath = $path;
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0777);
        }
        // return value should be true for success or false for failure
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $id): string
    {
        $data = @file_get_contents("$this->savePath/sess_$id");
        return $data !== false ? $data : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $id, string $data): bool
    {
        // return value should be true for success or false for failure
        return file_put_contents("$this->savePath/sess_$id", $data) === false ? false : true;
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
    public function updateTimestamp(string $id, string $data): bool
    {
        return touch("$this->savePath/sess_$id");
    }

    /**
     * {@inheritdoc}
     */
    public function validateId(string $id): bool
    {
        return file_exists("$this->savePath/sess_$id");
    }
}
