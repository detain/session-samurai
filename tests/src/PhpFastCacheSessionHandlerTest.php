<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\PhpFastCacheSessionHandler;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;

class PhpFastCacheSessionHandlerTest extends TestCase
{
    private PhpFastCacheSessionHandler $handler;
    private CacheItemPoolInterface $cache;
    private string $sessionId;

    public function setUp(): void
    {
        $this->cache = $this->createMock(CacheItemPoolInterface::class);
        $this->handler = new PhpFastCacheSessionHandler($this->cache);
        $this->sessionId = $this->handler->create_sid();
    }

    public function testOpen()
    {
        $this->assertTrue($this->handler->open('', ''));
    }

    public function testClose()
    {
        $this->assertTrue($this->handler->close());
    }

    public function testReadMiss()
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->method('isHit')->willReturn(false);
        $this->cache->method('getItem')->willReturn($item);
        $this->assertSame('', $this->handler->read($this->sessionId));
    }

    public function testReadHit()
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->method('isHit')->willReturn(true);
        $item->method('get')->willReturn('foo');
        $this->cache->method('getItem')->willReturn($item);
        $this->assertSame('foo', $this->handler->read($this->sessionId));
    }

    public function testWrite()
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->method('set')->willReturn($item);
        $item->method('expiresAfter')->willReturn($item);
        $this->cache->method('getItem')->willReturn($item);
        $this->cache->method('save')->willReturn(true);
        $this->assertTrue($this->handler->write($this->sessionId, 'foo'));
    }

    public function testDestroy()
    {
        $this->cache->method('deleteItem')->willReturn(true);
        $this->assertTrue($this->handler->destroy($this->sessionId));
    }

    public function testGc()
    {
        $this->assertNotFalse($this->handler->gc(0));
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function testCreate_sid()
    {
        $sid = $this->handler->create_sid();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $sid);
    }

    public function testValidateId()
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->method('isHit')->willReturn(true);
        $this->cache->method('getItem')->willReturn($item);
        $this->assertTrue($this->handler->validateId($this->sessionId));
    }

    public function testUpdateTimestamp()
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->method('expiresAfter')->willReturn($item);
        $this->cache->method('getItem')->willReturn($item);
        $this->cache->method('save')->willReturn(true);
        $this->assertTrue($this->handler->updateTimestamp($this->sessionId, 'foo'));
    }
}
