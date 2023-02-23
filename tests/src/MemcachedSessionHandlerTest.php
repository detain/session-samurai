<?php

namespace Detain\SessionSamuraiTest;

use Detain\SessionSamurai\MemcachedSessionHandler;

class MemcachedSessionHandlerTest extends \PHPUnit\Framework\TestCase
{
    public $memcachedMock;

    public function setUp(): void
    {
        session_start();
        $this->memcachedMock = $this->getMockBuilder('Memcached')->disableOriginalConstructor()->getMock();
    }

    /**
     * @runInSeparateProcess
     */
    public function testOpen()
    {
        $session = new MemcachedSessionHandler($this->memcachedMock);
        $this->assertTrue($session->open('/tmp', 'PHPSESSID'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testClose()
    {
        $session = new MemcachedSessionHandler($this->memcachedMock);
        $this->assertTrue($session->close());
    }

    /**
     * @runInSeparateProcess
     */
    public function testRead()
    {
        $this->memcachedMock->method('get')->willReturn('"data"');
        $session = new MemcachedSessionHandler($this->memcachedMock);
        $this->assertEquals('data', $session->read('id'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testWrite()
    {
        $this->memcachedMock->method('set')->willReturn(true);
        $session = new MemcachedSessionHandler($this->memcachedMock);
        $this->assertTrue($session->write('id', 'data'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testDestroy()
    {
        $this->memcachedMock->method('delete')->willReturn(true);
        $session = new MemcachedSessionHandler($this->memcachedMock);
        $this->assertTrue($session->destroy('id'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGc()
    {
        $session = new MemcachedSessionHandler($this->memcachedMock);
        $this->assertTrue($session->gc(0));
    }

    /**
     * @runInSeparateProcess
     */
    public function testValidateId()
    {
        $this->memcachedMock->method('get')->willReturn('data');
        $session = new MemcachedSessionHandler($this->memcachedMock);
        $this->assertEquals('data', $session->validateId('id'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testUpdateTimestamp()
    {
        $this->memcachedMock->method('touch')->willReturn(true);
        $session = new MemcachedSessionHandler($this->memcachedMock);
        $this->assertTrue($session->updateTimestamp('id', 'data'));
    }
}
