<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class SymfonyCacheSessionHandlerTest extends TestCase
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var SymfonyCacheSessionHandler
     */
    private $handler;

    public function setUp(): void
    {
        $this->cache = $this->createMock(CacheItemPoolInterface::class);
        $this->handler = new SymfonyCacheSessionHandler($this->cache);
    }

    public function testOpen()
    {
        $this->assertTrue($this->handler->open('', ''));
    }

    public function testClose()
    {
        $this->assertTrue($this->handler->close());
    }

    public function testRead()
    {
        $sessionId = 'foo';
        $sessionData = 'bar';

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(true);
        $cacheItem->method('get')->willReturn($sessionData);
        $this->cache->method('getItem')->with($sessionId)->willReturn($cacheItem);

        $this->assertEquals($sessionData, $this->handler->read($sessionId));
    }

    public function testReadNonExistent()
    {
        $sessionId = 'foo';

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(false);
        $this->cache->method('getItem')->with($sessionId)->willReturn($cacheItem);

        $this->assertEquals('', $this->handler->read($sessionId));
    }

    public function testWrite()
    {
        $sessionId = 'foo';
        $sessionData = 'bar';

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('set')->with($sessionData)->willReturn($cacheItem);
        $cacheItem->method('expiresAfter')->with(123)->willReturn($cacheItem);
        $this->cache->method('getItem')->with($sessionId)->willReturn($cacheItem);
        $this->cache->method('save')->with($cacheItem)->willReturn(true);

        $this->assertTrue($this->handler->write($sessionId, $sessionData));
    }

    public function testDestroy()
    {
        $sessionId = 'foo';

        $this->cache->method('deleteItem')->with($sessionId)->willReturn(true);

        $this->assertTrue($this->handler->destroy($sessionId));
    }

    public function testGc()
    {
        $maxlifetime = 123;

        $this->assertTrue($this->handler->gc($maxlifetime));
    }

    public function testCreateSid()
    {
        $sid = $this->handler->create_sid();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $sid);
    }

    public function testUpdateTimestamp()
    {
        $sessionId = 'foo';
        $sessionData = 'bar';

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('set')->with($sessionData)->willReturn($cacheItem);
        $cacheItem->method('expiresAfter')->with(123)->willReturn($cacheItem);
        $this->cache->method('getItem')->with($sessionId)->willReturn($cacheItem);
        $this->cache->method('save')->with($cacheItem)->willReturn(true);

        $this->assertTrue($this->handler->updateTimestamp($sessionId, $sessionData));
    }
}
