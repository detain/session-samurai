<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\ApcSessionHandler;

/**
 * @requires extension apc
 */
class APCSessionHandlerTest extends TestCase
{
    private ApcSessionHandler $handler;

    public function setUp(): void
    {
        $this->handler = new ApcSessionHandler();
        $this->handler->open('test_session', 'test_save_path');
    }

    public function testOpen(): void
    {
        $this->assertTrue($this->handler->open('test_session', 'test_save_path'));
    }

    public function testClose(): void
    {
        $this->assertTrue($this->handler->close());
    }

    public function testWrite(): void
    {
        $this->assertTrue($this->handler->write('key', 'value'));
    }

    public function testRead(): void
    {
        $this->handler->write('key', 'value');
        $this->assertEquals('value', $this->handler->read('key'));
    }

    public function testDestroy(): void
    {
        $this->handler->write('destroykey', 'value');
        $this->assertTrue($this->handler->destroy('destroykey'));
        $this->assertSame('', $this->handler->read('destroykey'));
    }
}
