<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\APCuSessionHandler;

class APCuSessionHandlerTest extends TestCase
{
    public function testConstructor()
    {
        $handler = new APCuSessionHandler();
        $this->assertInstanceOf(SessionHandlerInterface::class, $handler);
        $this->assertInstanceOf(SessionIdInterface::class, $handler);
        $this->assertInstanceOf(SessionUpdateTimestampHandlerInterface::class, $handler);
    }

    public function testOpen()
    {
        $handler = new APCuSessionHandler();
        $this->assertTrue($handler->open(__DIR__, 'test'));
    }

    public function testClose()
    {
        $handler = new APCuSessionHandler();
        $this->assertTrue($handler->close());
    }

    public function testRead()
    {
        $handler = new APCuSessionHandler();
        $this->assertEmpty($handler->read());

        $string = '{"foo": "bar"}';
        $key = 'test';
        apcu_store($key, $string);

        $this->assertSame($string, $handler->read($key));
    }

    public function testWrite()
    {
        $handler = new APCuSessionHandler();

        $string = '{"foo": "bar"}';
        $key = 'test';
        $this->assertTrue($handler->write($key, $string));

        $this->assertSame($string, apcu_fetch($key));
    }

    public function testDestroy()
    {
        $handler = new APCuSessionHandler();

        apcu_store('testkey', ' testvalue');
        $this->assertTrue($handler->destroy('testkey'));
        $this->assertFalse(apcu_fetch('testkey'));
    }

    public function testGc()
    {
        $handler = new APCuSessionHandler();
        $this->assertTrue($handler->gc(0));
    }
}
