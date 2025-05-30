<?php

namespace Detain\SessionSamurai;

use Doctrine\DBAL\Connection;
use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;
use SessionIdInterface;

class DoctrineDBALSessionHandler implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface
{
    private $connection;

    private $tableName = 'sessions';

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        $tableColumns = $this->connection->getSchemaManager()->listTableColumns($this->tableName);

        if (
            !array_key_exists('id', $tableColumns) ||
            !array_key_exists('data', $tableColumns) ||
            !array_key_exists('time', $tableColumns)
        ) {
            throw new \Exception('Required columns not found in table');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('data')
            ->from($this->tableName)
            ->where('id = ?')
            ->setParameter(0, $sessionId);

        $stmt = $qb->execute();
        $row = $stmt->fetch(\PDO::FETCH_NUM);

        if ($row) {
            return $row[0];
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data): bool
    {
        $time = time();

        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
            ->from($this->tableName)
            ->where('id = ?')
            ->setParameter(0, $sessionId);

        $stmt = $qb->execute();
        $row = $stmt->fetch();

        if ($row) {
            $qb = $this->connection->createQueryBuilder();
            $qb->update($this->tableName)
                ->set('data', '?')
                ->set('time', $time)
                ->where('id = ?')
                ->setParameter(0, $data)
                ->setParameter(1, $sessionId);

            $qb->execute();
        } else {
            $qb = $this->connection->createQueryBuilder();

            $qb->insert($this->tableName)
                ->values(
                    [
                        'id' => '?',
                        'data' => '?',
                        'time' => $time
                    ]
                )
                ->setParameter(0, $sessionId)
                ->setParameter(1, $data);

            $qb->execute();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId): bool
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->delete($this->tableName)
            ->where('id = ?')
            ->setParameter(0, $sessionId);

        $qb->execute();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime)
    {
        // Delete all records that are older than the lifetime
        $qb = $this->connection->createQueryBuilder();
        $qb->delete($this->tableName)
            ->where('time < ?')
            ->setParameter(0, (time() - (int) $lifetime));

        $qb->execute();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($sessionId)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('id')
            ->from($this->tableName)
            ->where('id = ?')
            ->setParameter(0, $sessionId);

        $stmt = $qb->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $sessionData)
    {
        $time = time();

        $qb = $this->connection->createQueryBuilder();
        $qb->update($this->tableName)
            ->set('time', $time)
            ->where('id = ?')
            ->setParameter(0, $sessionId);

        $qb->execute();

        return true;
    }
}
