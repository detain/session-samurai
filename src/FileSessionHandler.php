<?php

namespace Detain\SessionSamurai;

class FileSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private $savePath;

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

    #[\ReturnTypeWillChange]
    /**
     * {@inheritdoc}
     */
    public function gc(int $max_lifetime) //: int|false
    {
        foreach (glob("$this->savePath/sess_*") as $file) {
            if (filemtime($file) + $max_lifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }
        // return value should be true for success or false for failure
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function open(string $path, string $name): bool
    {
        $this->savePath = $savePath;
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0777);
        }
        // return value should be true for success or false for failure
        return true;
    }

    #[\ReturnTypeWillChange]
    /**
     * {@inheritdoc}
     */
    public function read(string $id) //: string|false
    {
        // return value should be the session data or an empty string
        return (string)@file_get_contents("$this->savePath/sess_$id");
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $id, string $data): bool
    {
        // return value should be true for success or false for failure
        return file_put_contents("$this->savePath/sess_$id", $data) === false ? false : true;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    /**
     * {@inheritdoc}
     */
    public function create_sid()
    //public function create_sid(): string
    {
        // available since PHP 5.5.1
        // invoked internally when a new session id is needed
        // no parameter is needed and return value should be the new session id created
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp(string $id, string $data): bool
    {
        // implements SessionUpdateTimestampHandlerInterface::validateId()
        // available since PHP 7.0
        // return value should be true for success or false for failure
    }

    /**
     * {@inheritdoc}
     */
    public function validateId(string $id): bool
    {
        // implements SessionUpdateTimestampHandlerInterface::validateId()
        // available since PHP 7.0
        // return value should be true if the session id is valid otherwise false
        // if false is returned a new session id will be generated by php internally
    }
}
