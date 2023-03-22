<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\SemaphoreSessionHandler;

class SemaphoreSessionHandlerTest extends TestCase
{
    private $sessionHandler;

    public function setUp(): void
    {
        $this->sessionHandler = new SemaphoreSessionHandler();
    }

    public function testOpen()
    {
        $this->assertTrue($this->sessionHandler->open('test', 'sess_1'));
    }

    public function testClose()
    {
        $this->assertTrue($this->sessionHandler->close());
    }

    public function testRead()
    {
        $this->assertNotEmpty($this->sessionHandler->read('test'));
    }

    public function testWrite()
    {
        $value = 'value';
        $this->assertTrue($this->sessionHandler->write('test', $value));
        $this->assertEquals($value, $this->sessionHandler->read('test'));
    }

    public function testDestroy()
    {
        $this->assertTrue($this->sessionHandler->destroy('test'));
        $this->assertEquals('', $this->sessionHandler->read('test'));
    }

    public function testGc()
    {
        $this->assertTrue($this->sessionHandler->gc(time()));
    }

    public function testUpdateTimestamp()
    {
        $this->assertTrue($this->sessionHandler->updateTimestamp('test', time()));
    }
}
