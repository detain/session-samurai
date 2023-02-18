<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;

class PhpFastCacheSessionHandlerTest extends TestCase
{
    protected $handler;
    protected $sessionId;

    public function setUp()
    {
        $this->handler = new PhpFastCacheSessionHandler();
        $this->sessionId = uniqid();
    }

    public function tearDown()
    {
        $this->handler = null;
        $this->sessionId = null;
    }

    public function testOpen()
    {
        $this->assertTrue($this->handler->open('', ''));
        $this->handler->close();
    }

    public function testClose()
    {
        $this->assertTrue($this->handler->close());
    }

    public function testRead()
    {
        $this->handler->write($this->sessionId, 'foo');
        $this->assertEquals('foo', $this->handler->read($this->sessionId));
        $this->handler->destroy($this->sessionId);
    }

    public function testWrite()
    {
        $this->assertTrue($this->handler->write($this->sessionId, 'foo'));
        $this->handler->destroy($this->sessionId);
    }

    public function testDestroy()
    {
        $this->handler->write($this->sessionId, 'foo');
        $this->assertTrue($this->handler->destroy($this->sessionId));
    }

    public function testGc()
    {
        $this->assertTrue($this->handler->gc(0));
    }

    public function testCreateSid()
    {
        $sid = $this->handler->create_sid();
        $this->assertIsString($sid);
    }

    public function testValidateId()
    {
        $this->assertTrue($this->handler->validateId($this->sessionId));
    }

    public function testUpdateTimestamp()
    {
        $this->assertTrue($this->handler->updateTimestamp($this->sessionId, 'foo'));
    }
}
