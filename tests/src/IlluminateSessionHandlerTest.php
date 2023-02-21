<?php

namespace Detain\SessionSamuraiTest;

use Detain\SessionSamurai\IlluminateSessionHandler;

class IlluminateSessionHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Mock object of Illuminate\Session\Store
     *
     * @var Illuminate\Session\Store
     */
    protected $store;

    /**
     * Set up test with mock object
     */
    public function setUp()
    {
        $this->store = $this->getMockBuilder('Illuminate\Session\Store')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test Illuminate\Session\Handler::open()
     */
    public function testOpen()
    {
        $this->assertTrue($this->store->open());
    }

    /**
     * Test Illuminate\Session\Handler::close()
     */
    public function testClose()
    {
        $this->assertTrue($this->store->close());
    }

    /**
     * Test Illuminate\Session\Handler::read()
     */
    public function testRead()
    {
        $sessionId = uniqid();
        $expectedValue = 'value';
        $this->store->expects($this->once())
            ->method('read')
            ->with($sessionId)
            ->willReturn($expectedValue);
        $actualValue = $this->store->read($sessionId);
        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * Test Illuminate\Session\Handler::write()
     */
    public function testWrite()
    {
        $sessionId = uniqid();
        $sessionData = 'value';
        $this->store->expects($this->once())
            ->method('write')
            ->with($sessionId, $sessionData)
            ->willReturn(true);
        $this->assertTrue($this->store->write($sessionId, $sessionData));
    }

    /**
     * Test Illuminate\Session\Handler::destroy()
     */
    public function testDestroy()
    {
        $sessionId = uniqid();
        $this->store->expects($this->once())
            ->method('destroy')
            ->with($sessionId)
            ->willReturn(true);
        $this->assertTrue($this->store->destroy($sessionId));
    }

    /**
     * Test Illuminate\Session\Handler::gc()
     */
    public function testGc()
    {
        $this->assertTrue($this->store->gc(1));
    }

    /**
     * Test Illuminate\Session\Handler::create_sid()
     */
    public function testCreateSid()
    {
        $sessionId = uniqid();
        $this->store->expects($this->once())
            ->method('create_sid')
            ->willReturn($sessionId);
        $actualValue = $this->store->create_sid();
        $this->assertEquals($sessionId, $actualValue);
    }
}
