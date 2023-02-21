<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;

// Test for Redis session handler class
class RedisTest extends TestCase
{
    protected $redis;

    public function setUp()
    {
        $this->redis = new Redis();
        $this->assertTrue($this->redis->connect('127.0.0.1', 6379));
    }

    public function tearDown()
    {
        unset($this->redis);
    }

    public function testGetSessionId()
    {
        $handler = new RedisHandler($this->redis);
        $sessionId = $handler->getSessionId();
        $this->assertNotNull($sessionId);
    }

    public function testWriteRead()
    {
        $handler = new RedisHandler($this->redis);
        $sessionId = $handler->getSessionId();
        $this->assertTrue($handler->write($sessionId, 'Test data'));
        $data = $handler->read($sessionId);
        $this->assertEquals('Test data', $data);
    }

    public function testUpdateTimestamp()
    {
        $handler = new RedisHandler($this->redis);
        $sessionId = $handler->getSessionId();
        $this->assertTrue($handler->updateTimestamp($sessionId, 'Test data'));
    }
}
