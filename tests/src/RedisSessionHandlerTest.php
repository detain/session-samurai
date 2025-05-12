<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\RedisSessionHandler;

class RedisSessionHandlerTest extends TestCase
{
    protected static $redis;
    protected static $sessionId;

    public static function setUpBeforeClass(): void
    {
        self::$redis = new \Redis();
        self::$redis->connect('127.0.0.1', 6379);
        self::$sessionId = bin2hex(random_bytes(32));
    }

    public function testOpen()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $this->assertTrue($handler->open('', ''));
    }

    public function testClose()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $this->assertTrue($handler->close());
    }

    public function testRead()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $this->assertEquals('', $handler->read(self::$sessionId));
    }

    public function testWrite()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $this->assertTrue($handler->write(self::$sessionId, 'test data'));
        $this->assertEquals('test data', $handler->read(self::$sessionId));
    }

    public function testGc()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $this->assertTrue($handler->gc(0));
    }

    public function testCreateSid()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $sid = $handler->create_sid();
        $this->assertIsString($sid);
        $this->assertNotEquals('', $sid);
    }

    public function testValidateId()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $this->assertTrue($handler->validateId(self::$sessionId));
        $this->assertFalse($handler->validateId('invalid-session-id'));
    }

    public function testUpdateTimestamp()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $this->assertTrue($handler->write(self::$sessionId, 'test data'));
        $this->assertTrue($handler->updateTimestamp(self::$sessionId, 'test data'));
    }

    public function testDestroy()
    {
        $handler = new RedisSessionHandler(self::$redis);
        $this->assertTrue($handler->destroy(self::$sessionId));
        $this->assertEquals('', $handler->read(self::$sessionId));
    }
}
