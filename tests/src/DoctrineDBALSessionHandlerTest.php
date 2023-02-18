<?php

declare(strict_types=1);

namespace Detain\SessionSamuraiTest;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\SessionHandlerDBAL;
use PHPUnit\Framework\TestCase;

class DoctrineDBALSessionHandlerTest extends TestCase
{
    /** @var Connection */
    protected $connection;

    /** @var SessionHandlerDBAL */
    protected $dbalSessionHandler;

    public function setUp(): void
    {
        $ids = [
            'db_name' => 'foo_db',
            'db_user' => 'foo_user',
            'db_pass' => 'foo_pass',
        ];

        $pdo_dsn = "mysql:dbname={$ids['db_name']};"
            . "host=localhost";

        $this->connection = DriverManager::getConnection(
            [
                'pdo' => new \PDO($pdo_dsn, $ids['db_user'], $ids['db_pass'])
            ]
        );

        $this->dbalSessionHandler = new SessionHandlerDBAL(
            $this->connection
        );
    }

    public function testOpenSession(): void
    {
        $this->assertEquals(
            true,
            $this->dbalSessionHandler->open('foo', 'bar')
        );
    }

    public function testCloseSession(): void
    {
        $this->assertEquals(
            true,
            $this->dbalSessionHandler->close()
        );
    }

    public function testReadSession(): void
    {
        $this->assertEquals(
            '',
            $this->dbalSessionHandler->read('foo')
        );
    }

    public function testWriteSession(): void
    {
        $this->assertEquals(
            true,
            $this->dbalSessionHandler->write('foo', 'bar')
        );
    }

    public function testDestroySession(): void
    {
        $this->assertEquals(
            true,
            $this->dbalSessionHandler->destroy('foo')
        );
    }

    public function testGcSession(): void
    {
        $this->assertEquals(
            true,
            $this->dbalSessionHandler->gc(100)
        );
    }
}
