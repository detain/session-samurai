<?php

namespace Detain\SessionSamurai;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Session\SessionUpdateTimestampHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\StrictSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\WriteCheckSessionHandler;

class SymfonyCacheSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private $cache;
    private $ttl;
    private $prefix;

    public function __construct(AdapterInterface $cache, $ttl = 0, $prefix = 'session.')
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
        $this->prefix = $prefix;
    }

    public function open($savePath, $sessionName)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($sessionId)
    {
        $key = $this->prefix.$sessionId;
        $data = $this->cache->getItem($key);

        if (!$data->isHit()) {
            return '';
        }

        return $data->get();
    }

    public function write($sessionId, $data)
    {
        $key = $this->prefix.$sessionId;
        $item = $this->cache->getItem($key);
        $item->set($data);

        if ($this->ttl > 0) {
            $item->expiresAfter($this->ttl);
        }

        return $this->cache->save($item);
    }

    public function destroy($sessionId)
    {
        $key = $this->prefix.$sessionId;

        return $this->cache->deleteItem($key);
    }

    public function gc($maxLifetime)
    {
        return true;
    }

    public function create_sid()
    {
        return md5(uniqid('', true));
    }

    public function updateTimestamp($sessionId, $data)
    {
        return $this->write($sessionId, $data);
    }
}
