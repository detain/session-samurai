<?php

namespace Detain\SessionSamuraiTest;

use Detain\SessionSamurai\MemcachedSessionHandler;

/**
 * Tests for memcached session save handler
 */
class MemcachedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Memcache
     */
    protected $memcached;
    protected $useMock = false;

    /**
     * @var string
     */
    protected $originalSessionSavePath;

    public function setUp(): void
    {
        // fix permission denied warnings by setting to a path we should have write access to
        $this->originalSessionSavePath = session_save_path();
        session_save_path('/tmp');
        if ($this->useMock == true) {
            $this->memcached = $this->getMockBuilder('Memcached')->disableOriginalConstructor()->getMock();
        } else {
            $this->memcached = new \Memcached();
            $this->memcached->addServer(TESTS_MEMCACHE_HOST, TESTS_MEMCACHE_PORT);
        }
    }

    /**
     * @runInSeparateProcess
     */
    public function testReadWrite()
    {
        session_start();
        $saveHandler = new MemcachedSessionHandler($this->memcached);
        $this->assertTrue($saveHandler->open('savepath', 'sessionname'));

        $id = session_id();
        $_SESSION = array('foo' => 'bar', 'bar' => array('foo' => 'bar'));

        $this->assertTrue($saveHandler->write($id, session_encode()));
        $this->assertEquals($_SESSION, json_decode($this->memcached->get("sess-{$id}"), true));
        $serializedSession = $saveHandler->read($id);
        $this->assertTrue(!empty($serializedSession));

        $_SESSION = array('foo' => array(1, 2, 3));

        $this->assertTrue($saveHandler->write($id, serialize($_SESSION)));
        $this->assertEquals($_SESSION, json_decode($this->memcached->get("sess-{$id}"), true));
        $serializedSession2 = $saveHandler->read($id);
        $this->assertTrue(!empty($serializedSession2));
    }

    /**
     * @runInSeparateProcess
     */
    public function testRead()
    {
        if ($this->useMock == true) {
            $this->memcached->method('get')->willReturn('"data"');
        }
        $session = new MemcachedSessionHandler($this->memcached);
        $this->assertEquals('data', $session->read('id'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testWrite()
    {
        if ($this->useMock == true) {
            $this->memcached->method('set')->willReturn(true);
        }
        $session = new MemcachedSessionHandler($this->memcached);
        $this->assertTrue($session->write('id', 'data'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testDestroy()
    {
        session_start();
        $saveHandler = new MemcachedSessionHandler($this->memcached);
        $saveHandler->open('savepath', 'sessionname');

        $id = session_id();
        $_SESSION = array('foo' => 'bar', 'bar' => array('foo' => 'bar'));

        $saveHandler->write($id, serialize($_SESSION));
        $this->assertEquals($_SESSION, json_decode($this->memcached->get("sess-{$id}"), true));

        $saveHandler->destroy($id);
        $this->assertEquals('', $saveHandler->read($id));
        $this->assertFalse($this->memcached->get("sess-{$id}"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGarbageCollection()
    {
        $saveHandler = new MemcachedSessionHandler($this->memcached);
        // should always return true
        $this->assertTrue($saveHandler->gc(-1));
    }

    /**
     * @runInSeparateProcess
     */
    public function testClose()
    {
        $saveHandler = new MemcachedSessionHandler($this->memcached);
        // should always return true
        $this->assertTrue($saveHandler->close());
    }


    /**
     * @runInSeparateProcess
     */
    public function testOpen()
    {
        $session = new MemcachedSessionHandler($this->memcached);
        $this->assertTrue($session->open('/tmp', 'PHPSESSID'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testDestroy()
    {
        if ($this->useMock == true) {
            $this->memcached->method('delete')->willReturn(true);
        }
        $session = new MemcachedSessionHandler($this->memcached);
        $this->assertTrue($session->destroy('id'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGc()
    {
        $session = new MemcachedSessionHandler($this->memcached);
        $this->assertTrue($session->gc(0));
    }

    /**
     * @runInSeparateProcess
     */
    public function testValidateId()
    {
        if ($this->useMock == true) {
            $this->memcached->method('get')->willReturn('data');
        }
        $session = new MemcachedSessionHandler($this->memcached);
        $this->assertEquals('data', $session->validateId('id'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testUpdateTimestamp()
    {
        if ($this->useMock == true) {
            $this->memcached->method('touch')->willReturn(true);
        }
        $session = new MemcachedSessionHandler($this->memcached);
        $this->assertTrue($session->updateTimestamp('id', 'data'));
    }


    public function tearDown(): void
    {
        $this->memcached->flush();
        // reset session save path back to default
        @session_save_path($this->originalSessionSavePath);
    }
}
