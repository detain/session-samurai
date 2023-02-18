<?php

class MemcachedSessionHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testOpen()
    {
        $memcachedMock = $this->getMockBuilder('Memcached')
            ->disableOriginalConstructor()
            ->getMock();

        $session = new MemcachedSessionHandler($memcachedMock);

        $this->assertTrue($session->open('/tmp', 'PHPSESSID'));
    }

    public function testClose()
    {
        $memcachedMock = $this->getMockBuilder('Memcached')
            ->disableOriginalConstructor()
            ->getMock();

        $session = new MemcachedSessionHandler($memcachedMock);

        $this->assertTrue($session->close());
    }

    public function testRead()
    {
        $memcachedMock = $this->getMockBuilder('Memcached')
            ->disableOriginalConstructor()
            ->getMock();

        $memcachedMock->method('get')
            ->willReturn('data');

        $session = new MemcachedSessionHandler($memcachedMock);

        $this->assertEquals('data', $session->read('id'));
    }

    public function testWrite()
    {
        $memcachedMock = $this->getMockBuilder('Memcached')
            ->disableOriginalConstructor()
            ->getMock();

        $memcachedMock->method('set')
            ->willReturn(true);

        $session = new MemcachedSessionHandler($memcachedMock);

        $this->assertTrue($session->write('id', 'data'));
    }

    public function testDestroy()
    {
        $memcachedMock = $this->getMockBuilder('Memcached')
            ->disableOriginalConstructor()
            ->getMock();

        $memcachedMock->method('delete')
            ->willReturn(true);

        $session = new MemcachedSessionHandler($memcachedMock);

        $this->assertTrue($session->destroy('id'));
    }

    public function testGc()
    {
        $memcachedMock = $this->getMockBuilder('Memcached')
            ->disableOriginalConstructor()
            ->getMock();

        $session = new MemcachedSessionHandler($memcachedMock);

        $this->assertTrue($session->gc(0));
    }

    public function testValidateId()
    {
        $memcachedMock = $this->getMockBuilder('Memcached')
            ->disableOriginalConstructor()
            ->getMock();

        $memcachedMock->method('get')
            ->willReturn('data');

        $session = new MemcachedSessionHandler($memcachedMock);

        $this->assertEquals('data', $session->validateId('id'));
    }

    public function testUpdateTimestamp()
    {
        $memcachedMock = $this->getMockBuilder('Memcached')
            ->disableOriginalConstructor()
            ->getMock();

        $memcachedMock->method('get')
            ->willReturn(true);

        $session = new MemcachedSessionHandler($memcachedMock);

        $this->assertTrue($session->updateTimestamp('id', 'data'));
    }
}