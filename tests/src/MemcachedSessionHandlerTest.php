<?php

namespace Detain\SessionSamuraiTest;

use Detain\SessionSamurai\MemcachedSessionHandler;
use PHPUnit\Framework\TestCase;

class MemcachedSessionHandlerTest extends TestCase
{
    private \Memcached $memcached;
    private MemcachedSessionHandler $handler;

    public function setUp(): void
    {
        $this->memcached = $this->createMock(\Memcached::class);
        $this->handler = new MemcachedSessionHandler($this->memcached);
        $this->handler->open('', 'PHPSESSID');
    }

    public function testOpen()
    {
        $this->assertTrue($this->handler->open('/tmp', 'PHPSESSID'));
    }

    public function testClose()
    {
        $this->assertTrue($this->handler->close());
    }

    public function testWrite()
    {
        $this->memcached->method('set')->willReturn(true);
        $this->assertTrue($this->handler->write('id', 'data'));
    }

    public function testDestroy()
    {
        $this->memcached->method('delete')->willReturn(true);
        $this->assertTrue($this->handler->destroy('id'));
    }

    public function testGc()
    {
        $this->assertNotFalse($this->handler->gc(3600));
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function testCreate_sid()
    {
        $this->memcached->method('get')->willReturn(false);
        $sid = $this->handler->create_sid();
        $this->assertIsString($sid);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $sid);
    }

    public function testValidateId()
    {
        $this->memcached->method('get')->willReturn('somedata');
        $this->assertTrue($this->handler->validateId('id'));
    }

    public function testValidateIdMissing()
    {
        $this->memcached->method('get')->willReturn(false);
        $this->assertFalse($this->handler->validateId('nonexistent'));
    }

    public function testUpdateTimestamp()
    {
        $this->memcached->method('touch')->willReturn(true);
        $this->assertTrue($this->handler->updateTimestamp('id', 'data'));
    }
}
