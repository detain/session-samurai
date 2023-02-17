<?php

use PHPUnit\Framework\TestCase;
use Memcached;

class MemcachedSessionHandlerTest extends TestCase
{
    private $memcached;
    private $prefix;
    private $handler;

    // setup data before start tests
    public function setUp()
    {
        $this->memcached = new Memcached();
        $this->prefix = 'TEST';
        $this->handler = new MemcachedSessionHandler($this->memcached, $this->prefix);
    }


    /**
     * @test
     */
    public function open_succeeds()
    {
        $sessionId = 'TEST_SESSION';
        $savePath = '/tmp';
        $data = 'My session data';

        $this->assertTrue($this->handler->open($savePath, $sessionId));
    }

    /**
     * @test
     */
    public function read_succeeds()
    {
        $sessionId = 'TEST_SESSION';
        $savePath = '/tmp';
        $data = 'My session data';
        $this->memcached->set($this->prefix . $sessionId, $data);

        $this->assertEquals($data, $this->handler->read($sessionId));
    }

    /**
     * @test
     */
    public function write_succeeds()
    {
        $sessionId = 'TEST_SESSION';
        $savePath = '/tmp';
        $data = 'My session data';

        $this->assertTrue($this->handler->write($sessionId, $data));
    }

    /**
     * @test
     */
    public function destroy_succeeds()
    {
        $sessionId = 'TEST_SESSION';
        $savePath = '/tmp';
        $data = 'My session data';
        $this->memcached->set($this->prefix . $sessionId, $data);

        $this->assertTrue($this->handler->destroy($sessionId));
    }

    /**
     * @test
     */
    public function create_sid_succeeds()
    {
        $sessionId = $this->handler->create_sid();
        $this->assertNotEmpty($sessionId);
    }

    /**
     * @test
     */
    public function validateId_succeeds()
    {
        $sessionId = 'TEST_SESSION';
        $savePath = '/tmp';
        $data = 'My session data';
        $this->memcached->set($this->prefix . $sessionId, $data);

        $this->assertTrue($this->handler->validateId($sessionId));
    }

    /**
     * @test
     */
    public function updateTimestamp_succeeds()
    {
        $sessionId = 'TEST_SESSION';
        $savePath = '/tmp';
        $data = 'My session data';
        $this->memcached->set($this->prefix . $sessionId, $data);

        $this->assertTrue($this->handler->updateTimestamp($sessionId, $data));
	}
}
