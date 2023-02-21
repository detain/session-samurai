<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use MongoDB\Client;
use Detain\SessionSamurai\MongoDbSessionHandler;

class MongoDBSessionHandlerTest extends TestCase
{
    protected static $sessionHandler;

    private static $client;

    public static function setUpBeforeClass(): void
    {
        self::$client = new Client();

        self::$sessionHandler = new MongoDBSessionHandler(self::$client);
    }

    public static function tearDownAfterClass(): void
    {
        self::$sessionHandler->empty();
    }

    public function testOpen(): void
    {
        $this->assertTrue(self::$sessionHandler->open('', ''));
    }

    public function testRead(): void
    {
        $sessionId = 'mysessionid';
        $data = [
            'foo' => 'bar',
            'baz' => 'boo'
        ];

        $this->assertTrue(self::$sessionHandler->write($sessionId, json_encode($data)));

        $this->assertEquals(json_encode($data), self::$sessionHandler->read($sessionId));
    }

    public function testWrite(): void
    {
        $sessionId = 'myothersessionid';
        $data = [
            'foo' => 'bar',
            'baz' => 'boo'
        ];

        $this->assertTrue(self::$sessionHandler->write($sessionId, json_encode($data)));
    }

    public function testDelete(): void
    {
        $sessionId = 'mydeletesessionid';
        $data = [
            'foo' => 'bar',
            'baz' => 'boo'
        ];

        $this->assertTrue(self::$sessionHandler->write($sessionId, json_encode($data)));
        $this->assertTrue(self::$sessionHandler->destroy($sessionId));
    }

    public function testGC(): void
    {
        $expireTime = time() + (1000 * 60);
        $this->assertTrue(self::$sessionHandler->gc($expireTime));
    }
}
