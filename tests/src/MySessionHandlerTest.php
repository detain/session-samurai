<?php

namespace Detain\SessionSamuraiTest;

use Detain\SessionSamurai\MySessionHandler;

class MySessionHandlerTest extends \PHPUnit\Framework\TestCase
{
    protected $handler;

    protected function setUp(): void
    {
        // Create a new instance of the session handler to be tested
        $this->handler = new MySessionHandler();
        $this->handler->open(sys_get_temp_dir(), 'test');
    }

    protected function tearDown(): void
    {
        $this->handler->close();
    }

    public function testImplementsInterfaces()
    {
        $this->assertInstanceOf(\SessionHandlerInterface::class, $this->handler);
        $this->assertInstanceOf(\SessionIdInterface::class, $this->handler);
        $this->assertInstanceOf(\SessionUpdateTimestampHandlerInterface::class, $this->handler);
    }

    public function testOpen()
    {
        // Ensure the open() method returns true
        $this->assertTrue($this->handler->open('', ''));
    }

    public function testClose()
    {
        // Ensure the close() method returns true
        $this->assertTrue($this->handler->close());
    }

    public function testRead()
    {
        // Create a new session ID and data
        $sessionId = $this->handler->create_sid();
        // Ensure the read() method returns an empty string for a non-existent session ID
        $this->assertEquals('', $this->handler->read($sessionId));
        // Save the session data using the handler
        $this->handler->write($sessionId, 'test');
        // Ensure the read() method returns the saved session data
        $this->assertEquals('test', $this->handler->read($sessionId));
    }

    public function testWrite()
    {
        // Create a new session ID and data
        $sessionId = $this->handler->create_sid();
        // Ensure the write() method returns true
        $this->assertTrue($this->handler->write($sessionId, 'test'));
        // Ensure the saved session data can be retrieved using the read() method
        $this->assertEquals('test', file_get_contents(sys_get_temp_dir() . '/sess_' . $sessionId));
    }

    public function testDestroy()
    {
        // Create a new session ID and data
        $sessionId = $this->handler->create_sid();
        // Save the session data using the handler
        $this->assertTrue($this->handler->write($sessionId, 'test'));
        // Ensure the destroy() method returns true
        $this->assertTrue($this->handler->destroy($sessionId));
        // Ensure the session data can no longer be retrieved using the read() method
        $this->assertSame('', $this->handler->read($sessionId));
        // ensure the file no longer exists
        $this->assertFalse(file_exists(sys_get_temp_dir() . '/sess_' . $sessionId));
    }

    public function testGc()
    {
        // Create a new session ID and data
        $sessionId1 = $this->handler->create_sid();
        $sessionId2 = $this->handler->create_sid();
        // Save the session data using the handler
        $this->assertTrue($this->handler->write($sessionId1, 'test'));
        $this->assertTrue($this->handler->write($sessionId2, 'test'));
        // ensure both sessions are valid
        $this->assertTrue($this->handler->validateId($sessionId1));
        $this->assertTrue($this->handler->validateId($sessionId2));
        // sleep for 2 seconds to enssure a different timestamp
        sleep(2);
        // update the timestamp on session1
        $this->assertTrue($this->handler->updateTimestamp($sessionId1, 'test'));
        // Ensure the gc() method returns true
        $this->assertTrue($this->handler->gc(0));
        // ensure the one session is valid and the other invalid
        $this->assertTrue($this->handler->validateId($sessionId1));
        $this->assertFalse($this->handler->validateId($sessionId2));
    }


    public function testCreateSid()
    {
        // Ensure the createSid() method returns a valid session ID string
        $this->assertIsString($this->handler->create_sid());
    }

    public function testValidateId()
    {
        // Ensure the validateId() method returns true for a valid session ID
        $sessionId = session_create_id();
        $this->assertTrue($this->handler->write($sessionId, 'test'));
        $this->assertTrue($this->handler->validateId($sessionId));
        // Ensure the validateId() method returns false for an invalid session ID
        $this->assertFalse($this->handler->validateId('invalid-session-id'));
    }

    public function testUpdateTimestamp()
    {
        // Create a new session ID and data
        $sessionId = session_create_id();
        $sessionData = 'some session data';
        // Save the session data using the handler
        $this->assertTrue($this->handler->write($sessionId, 'test'));
        $this->assertTrue($this->handler->updateTimestamp($sessionId, 'test'));
    }
}
