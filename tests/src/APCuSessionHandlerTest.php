<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\APCuSessionHandler;

/**
 * @requires extension apcu
 */
class APCuSessionHandlerTest extends TestCase
{
    private APCuSessionHandler $handler;

    public function setUp(): void
    {
        $this->handler = new APCuSessionHandler();
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(\SessionHandlerInterface::class, $this->handler);
        $this->assertInstanceOf(\SessionIdInterface::class, $this->handler);
        $this->assertInstanceOf(\SessionUpdateTimestampHandlerInterface::class, $this->handler);
    }

    public function testOpen()
    {
        $this->assertTrue($this->handler->open(__DIR__, 'test'));
    }

    public function testClose()
    {
        $this->assertTrue($this->handler->close());
    }

    public function testReadMissing()
    {
        $this->assertSame('', $this->handler->read('nonexistent_key_' . bin2hex(random_bytes(8))));
    }

    public function testWriteRead()
    {
        $string = '{"foo": "bar"}';
        $key = 'test_apcu_' . bin2hex(random_bytes(4));
        $this->assertTrue($this->handler->write($key, $string));
        $this->assertSame($string, $this->handler->read($key));
    }

    public function testDestroy()
    {
        $key = 'test_apcu_' . bin2hex(random_bytes(4));
        $this->handler->write($key, 'testvalue');
        $this->assertTrue($this->handler->destroy($key));
        $this->assertSame('', $this->handler->read($key));
    }

    public function testGc()
    {
        $this->assertNotFalse($this->handler->gc(0));
    }
}
