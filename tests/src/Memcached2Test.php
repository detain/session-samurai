<?php

namespace Detain\SessionSamurailTests;

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


    public function openSucceeds()
    {
        $sessionId = 'TEST_SESSION';
        $savePath = '/tmp';
        $data = 'My session data';

        $this->assertTrue($this->handler->open($savePath, $sessionId));
    }

    public function readSucceeds()
    {
        $sessionId = 'TEST_SESSION';
        $savePath = '/tmp';
        $data = 'My session data';
        $this->memcached->set($this->prefix . $sessionId, $data);

        $this->assertEquals($data, $this->handler->read($sessionId));
    }

    public function writeSucceeds()
    {
        $sessionId = 'TEST_SESSION';
        $savePath = '/tmp';
        $data = 'My session data';

        $this->assertTrue($this->handler->write($sessionId, $data));
    }

    public function destroySucceeds()
    {
        $sessionId = 'TEST_SESSION';
        $savePath = '/tmp';
        $data = 'My session data';
        $this->memcached->set($this->prefix . $sessionId, $data);

        $this->assertTrue($this->handler->destroy($sessionId));
    }


    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sidSucceeds()
    {
        $sessionId = $this->handler->create_sid();
        $this->assertNotEmpty($sessionId);
    }

    public function validateIdSucceeds()
    {
        $sessionId = 'TEST_SESSION';
        $savePath = '/tmp';
        $data = 'My session data';
        $this->memcached->set($this->prefix . $sessionId, $data);

        $this->assertTrue($this->handler->validateId($sessionId));
    }

    public function updateTimestampSucceeds()
    {
        $sessionId = 'TEST_SESSION';
        $savePath = '/tmp';
        $data = 'My session data';
        $this->memcached->set($this->prefix . $sessionId, $data);

        $this->assertTrue($this->handler->updateTimestamp($sessionId, $data));
    }
}
