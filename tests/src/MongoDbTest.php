<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\MongoDbSessionHandler;

class MongoDbTest extends TestCase
{
    private $sessionHandler;

    public function setUp()
    {
        $mongoConnection = new MongoClient();
        $this->sessionHandler = new MongoDBSessionHandler($mongoConnection);
    }

    // Test open method
    public function testOpenReturnsTrue()
    {
        $this->assertTrue(
            $this->sessionHandler->open('my_save_path', 'my_session_name')
        );
    }

    // Test close method
    public function testCloseReturnsTrue()
    {
        $this->assertTrue(
            $this->sessionHandler->close()
        );
    }

    // Test read method
    public function testReadReturnsData()
    {
        $data = '{foo:bar}';
        $id = $this->sessionHandler->create_sid();

        $this->sessionHandler->write($id, $data);
        $this->assertEquals($data, $this->sessionHandler->read($id));
    }

    // Test write method
    public function testWriteReturnsTrue()
    {
        $data = '{foo:bar}';
        $id = $this->sessionHandler->create_sid();
        $this->assertTrue($this->sessionHandler->write($id, $data));
        $this->assertEquals($data, $this->sessionHandler->read($id));
    }

    // Test destroy method
    public function testDestroyReturnsTrue()
    {
        $data = '{foo:bar}';
        $id = $this->sessionHandler->create_sid();

        $this->sessionHandler->write($id, $data);
        $this->assertTrue($this->sessionHandler->destroy($id));
        $this->assertEquals('', $this->sessionHandler->read($id));
    }

    // Test create_sid method
    public function testCreateSidReturnsString()
    {
        $this->assertTrue(is_string($this->sessionHandler->create_sid()));
    }

    // Test validateId method
    public function testValidateIdReturnsBoolean()
    {
        $data = '{foo:bar}';
        $id = $this->sessionHandler->create_sid();
        $this->sessionHandler->write($id, $data);
        $this->assertTrue(is_bool($this->sessionHandler->validateId($id)));
    }

    // Test updateTimestamp method
    public function testUpdateTimestampReturnsBoolean()
    {
        $data = '{foo:bar}';
        $id = $this->sessionHandler->create_sid();
        $this->sessionHandler->write($id, $data);
        $this->assertTrue(is_bool($this->sessionHandler->updateTimestamp($id, time())));
    }
}
