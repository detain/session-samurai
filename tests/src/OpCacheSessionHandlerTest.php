<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\OpCacheSessionHandler;

// include your OPCache Session Handler here

class OpCacheSessionHandlerTest extends TestCase
{
    /**
     * @var OPCacheSessionHandler
     */
    protected $handler;

    public function setUp(): void
    {
        $this->handler = new OpCacheSessionHandler();
    }

    public function testSessionOpen()
    {
        $this->assertTrue($this->handler->open('test', 'test'));
    }

    public function testSessionClose()
    {
        $this->assertTrue($this->handler->close());
    }

    public function testRead()
    {
        $this->assertEquals('', $this->handler->read('test'));
    }

    public function testWrite()
    {
        $data = json_encode(['data' => 'hello world']);
        $this->assertTrue($this->handler->write('test', $data));

        $read = $this->handler->read('test');
        $this->assertEquals($data, $read);
    }

    public function testDestroy()
    {
        $this->assertTrue($this->handler->destroy('test'));
    }

    public function testGc()
    {
        $this->assertTrue($this->handler->gc(100));
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function testCreate_sid()
    {
        $sid = $this->handler->create_sid();
        $this->assertIsString($sid);
    }

    public function testValidateId()
    {
        $this->assertTrue($this->handler->validateId('test'));
    }

    public function testUpdateTimestamp()
    {
        $this->assertTrue($this->handler->updateTimestamp('test', 100));
    }
}
