<?php

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\MongoDbSessionHandler;
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;
use MongoDB\UpdateResult;
use MongoDB\DeleteResult;

class MongoDBSessionHandlerTest extends TestCase
{
    private Collection $collection;
    private MongoDbSessionHandler $sessionHandler;

    public function setUp(): void
    {
        $this->collection = $this->createMock(Collection::class);
        $this->sessionHandler = new MongoDbSessionHandler($this->collection);
    }

    public function testOpen(): void
    {
        $this->assertTrue($this->sessionHandler->open('', ''));
    }

    public function testRead(): void
    {
        $sessionId = 'mysessionid';
        $data = '{"foo":"bar"}';

        $doc = new BSONDocument(['_id' => $sessionId, 'data' => $data]);
        $this->collection->method('findOne')->willReturn($doc);

        $this->assertEquals($data, $this->sessionHandler->read($sessionId));
    }

    public function testWrite(): void
    {
        $updateResult = $this->createMock(UpdateResult::class);
        $updateResult->method('getUpsertedCount')->willReturn(1);
        $updateResult->method('getModifiedCount')->willReturn(0);
        $this->collection->method('updateOne')->willReturn($updateResult);

        $this->assertTrue($this->sessionHandler->write('myothersessionid', '{"foo":"bar"}'));
    }

    public function testDestroy(): void
    {
        $deleteResult = $this->createMock(DeleteResult::class);
        $deleteResult->method('getDeletedCount')->willReturn(1);
        $this->collection->method('deleteOne')->willReturn($deleteResult);

        $this->assertTrue($this->sessionHandler->destroy('mydeletesessionid'));
    }

    public function testGc(): void
    {
        $deleteResult = $this->createMock(DeleteResult::class);
        $deleteResult->method('getDeletedCount')->willReturn(0);
        $this->collection->method('deleteMany')->willReturn($deleteResult);

        $this->assertNotFalse($this->sessionHandler->gc(3600));
    }
}
