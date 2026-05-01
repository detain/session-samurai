<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\PDOSessionHandler;

class PdoSessionHandlerTest extends TestCase
{
    private PDOSessionHandler $handler;
    private \PDO $pdo;

    public function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->exec('CREATE TABLE sessions (id TEXT PRIMARY KEY, data TEXT, updated_at INTEGER)');
        $this->handler = new PDOSessionHandler($this->pdo);
    }

    public function testOpen()
    {
        $this->assertTrue($this->handler->open('', 'PHPSESSID'));
    }

    public function testClose()
    {
        $this->assertTrue($this->handler->close());
    }

    public function testReadEmpty()
    {
        $this->assertSame('', $this->handler->read('nonexistent'));
    }

    public function testWrite()
    {
        $key = $this->handler->create_sid();
        $this->assertTrue($this->handler->write($key, 'my test data'));
    }

    public function testWriteRead()
    {
        $data = 'my test data';
        $key = $this->handler->create_sid();
        $this->handler->write($key, $data);
        $this->assertSame($data, $this->handler->read($key));
    }

    public function testDestroy()
    {
        $key = $this->handler->create_sid();
        $this->handler->write($key, 'data');
        $this->assertTrue($this->handler->destroy($key));
        $this->assertSame('', $this->handler->read($key));
    }

    public function testGc()
    {
        $key = $this->handler->create_sid();
        $this->handler->write($key, 'data');
        $this->assertNotFalse($this->handler->gc(0));
    }

    public function testUpdateTimestamp()
    {
        $key = $this->handler->create_sid();
        $this->handler->write($key, 'data');
        $this->assertTrue($this->handler->updateTimestamp($key, 'data'));
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function testCreate_sid()
    {
        $sid = $this->handler->create_sid();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $sid);
    }
}
