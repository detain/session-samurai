<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\MongoDbSessionHandler;
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;
use MongoDB\UpdateResult;
use MongoDB\DeleteResult;
use MongoDB\Driver\WriteResult;

class MongoDbTest extends TestCase
{
    private MongoDbSessionHandler $sessionHandler;
    private Collection $collection;

    public function setUp(): void
    {
        $this->collection = $this->createMock(Collection::class);
        $this->sessionHandler = new MongoDbSessionHandler($this->collection);
    }

    public function testOpenReturnsTrue()
    {
        $this->assertTrue($this->sessionHandler->open('', ''));
    }

    public function testCloseReturnsTrue()
    {
        $this->assertTrue($this->sessionHandler->close());
    }

    public function testReadReturnsData()
    {
        $data = '{foo:bar}';
        $id = $this->sessionHandler->create_sid();

        $doc = new BSONDocument(['_id' => $id, 'data' => $data]);
        $this->collection->method('findOne')->willReturn($doc);

        $this->assertEquals($data, $this->sessionHandler->read($id));
    }

    public function testReadMissingReturnsEmpty()
    {
        $this->collection->method('findOne')->willReturn(null);
        $this->assertEquals('', $this->sessionHandler->read('nonexistent'));
    }

    public function testWriteReturnsTrue()
    {
        $updateResult = $this->createMock(UpdateResult::class);
        $updateResult->method('getUpsertedCount')->willReturn(1);
        $updateResult->method('getModifiedCount')->willReturn(0);
        $this->collection->method('updateOne')->willReturn($updateResult);

        $id = $this->sessionHandler->create_sid();
        $this->assertTrue($this->sessionHandler->write($id, '{foo:bar}'));
    }

    public function testDestroyReturnsTrue()
    {
        $deleteResult = $this->createMock(DeleteResult::class);
        $deleteResult->method('getDeletedCount')->willReturn(1);
        $this->collection->method('deleteOne')->willReturn($deleteResult);

        $id = $this->sessionHandler->create_sid();
        $this->assertTrue($this->sessionHandler->destroy($id));
    }

    public function testCreateSidReturnsString()
    {
        $sid = $this->sessionHandler->create_sid();
        $this->assertIsString($sid);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $sid);
    }

    public function testValidateIdReturnsBoolean()
    {
        $this->collection->method('countDocuments')->willReturn(1);
        $id = $this->sessionHandler->create_sid();
        $this->assertIsBool($this->sessionHandler->validateId($id));
    }

    public function testUpdateTimestampReturnsBoolean()
    {
        $updateResult = $this->createMock(UpdateResult::class);
        $updateResult->method('getModifiedCount')->willReturn(1);
        $this->collection->method('updateOne')->willReturn($updateResult);

        $id = $this->sessionHandler->create_sid();
        $this->assertIsBool($this->sessionHandler->updateTimestamp($id, 'data'));
    }
}
