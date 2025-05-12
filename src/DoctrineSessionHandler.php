<?php

namespace Detain\SessionSamurai;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\StrictSessionHandler;

class DoctrineSessionHandler extends PdoSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private $connection;

    public function __construct(Connection $connection, array $options = [])
    {
        parent::__construct($connection->getWrappedConnection(), $options);
        $this->connection = $connection;
    }

    public function read($sessionId)
    {
        return (string) $this->connection->fetchColumn(
            'SELECT session_data FROM sessions WHERE session_id = ?',
            [$sessionId],
            0,
            $this->getOptions()['driverOptions'] ?? []
        );
    }

    public function write($sessionId, $sessionData): bool
    {
        $sql = 'INSERT INTO sessions (session_id, session_data, session_time) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE session_data = VALUES(session_data), session_time = VALUES(session_time)';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, $sessionId);
        $stmt->bindValue(2, $sessionData);
        $stmt->bindValue(3, time());
        $stmt->execute();
    }

    public function destroy($sessionId): bool
    {
        $this->connection->executeQuery('DELETE FROM sessions WHERE session_id = ?', [$sessionId]);
    }

    public function gc($maxLifetime)
    {
        $this->connection->executeQuery('DELETE FROM sessions WHERE session_time < ?', [time() - $maxLifetime]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid()
    {
        return md5(uniqid(mt_rand(), true));
    }

    public function validateId($sessionId)
    {
        $stmt = $this->connection->executeQuery('SELECT session_time FROM sessions WHERE session_id = ?', [$sessionId]);
        return $stmt->fetchColumn() !== false;
    }

    public function updateTimestamp($sessionId, $sessionData)
    {
        $sql = 'UPDATE sessions SET session_time = ? WHERE session_id = ?';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(1, time());
        $stmt->bindValue(2, $sessionId);
        $stmt->execute();
    }
}
