<?php

declare(strict_types=1);

namespace Detain\SessionSamuraiTest;

use PHPUnit\Framework\TestCase;
use Detain\SessionSamurai\DoctrineDBALSessionHandler;

class DoctrineDBALSessionHandlerTest extends TestCase
{
    public function setUp(): void
    {
        if (!class_exists('Doctrine\DBAL\Connection')) {
            $this->markTestSkipped('doctrine/dbal is not installed');
        }
    }

    public function testOpenSession(): void
    {
        $connection = $this->createMockConnection();
        $handler = new DoctrineDBALSessionHandler($connection);
        $this->assertTrue($handler->open('', ''));
    }

    public function testCloseSession(): void
    {
        $connection = $this->createMockConnection();
        $handler = new DoctrineDBALSessionHandler($connection);
        $this->assertTrue($handler->close());
    }

    public function testReadSession(): void
    {
        $connection = $this->createMockConnection();
        $handler = new DoctrineDBALSessionHandler($connection);
        $this->assertIsString($handler->read('nonexistent'));
    }

    public function testWriteSession(): void
    {
        $connection = $this->createMockConnection();
        $handler = new DoctrineDBALSessionHandler($connection);
        $this->assertTrue($handler->write('foo', 'bar'));
    }

    public function testDestroySession(): void
    {
        $connection = $this->createMockConnection();
        $handler = new DoctrineDBALSessionHandler($connection);
        $this->assertTrue($handler->destroy('foo'));
    }

    public function testGcSession(): void
    {
        $connection = $this->createMockConnection();
        $handler = new DoctrineDBALSessionHandler($connection);
        $this->assertNotFalse($handler->gc(100));
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function testCreate_sid(): void
    {
        $connection = $this->createMockConnection();
        $handler = new DoctrineDBALSessionHandler($connection);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $handler->create_sid());
    }

    private function createMockConnection(): \Doctrine\DBAL\Connection
    {
        $schemaManager = $this->createMock(\Doctrine\DBAL\Schema\AbstractSchemaManager::class);
        $schemaManager->method('listTableColumns')->willReturn([
            'id' => true,
            'data' => true,
            'time' => true,
        ]);

        $stmt = $this->createMock(\Doctrine\DBAL\Result::class);
        $stmt->method('fetch')->willReturn(false);
        $stmt->method('fetchColumn')->willReturn(false);
        $stmt->method('rowCount')->willReturn(0);

        $qb = $this->createMock(\Doctrine\DBAL\Query\QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('update')->willReturnSelf();
        $qb->method('set')->willReturnSelf();
        $qb->method('insert')->willReturnSelf();
        $qb->method('values')->willReturnSelf();
        $qb->method('delete')->willReturnSelf();
        $qb->method('execute')->willReturn($stmt);

        $connection = $this->createMock(\Doctrine\DBAL\Connection::class);
        $connection->method('getSchemaManager')->willReturn($schemaManager);
        $connection->method('createQueryBuilder')->willReturn($qb);

        return $connection;
    }
}
