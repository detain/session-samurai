<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\SemaphoreSessionHandler;

class SemaphoreSessionHandlerTest extends TestCase
{
    private SemaphoreSessionHandler $sessionHandler;
    private string $sessionId;

    public function setUp(): void
    {
        $this->sessionHandler = new SemaphoreSessionHandler();
        $this->sessionId = $this->sessionHandler->create_sid();
    }

    public function testOpen()
    {
        $this->assertTrue($this->sessionHandler->open('test', 'PHPSESSID'));
    }

    public function testClose()
    {
        $this->assertTrue($this->sessionHandler->close());
    }

    public function testReadEmpty()
    {
        $this->assertSame('', $this->sessionHandler->read($this->sessionId));
    }

    public function testWrite()
    {
        $value = 'hello world';
        $this->assertTrue($this->sessionHandler->write($this->sessionId, $value));
        $this->assertEquals($value, $this->sessionHandler->read($this->sessionId));
    }

    public function testDestroy()
    {
        $this->sessionHandler->write($this->sessionId, 'data');
        $this->assertTrue($this->sessionHandler->destroy($this->sessionId));
    }

    public function testGc()
    {
        $this->assertNotFalse($this->sessionHandler->gc(3600));
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function testCreate_sid()
    {
        $sid = $this->sessionHandler->create_sid();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $sid);
    }

    public function testUpdateTimestamp()
    {
        $this->sessionHandler->write($this->sessionId, 'data');
        $this->assertTrue($this->sessionHandler->updateTimestamp($this->sessionId, 'data'));
    }
}
