<?php

namespace Detain\SessionSamurai;

class PDOSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName): bool
    {
        // No action needed since PDO handles the connection.
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): bool
    {
        // No action needed since PDO handles the connection.
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        $statement = $this->pdo->prepare("SELECT data FROM sessions WHERE id = :id");
        $statement->execute(['id' => $sessionId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['data'] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data): bool
    {
        $statement = $this->pdo->prepare("REPLACE INTO sessions (id, data, updated_at) VALUES (:id, :data, :updated_at)");
        $statement->execute(['id' => $sessionId, 'data' => $data, 'updated_at' => time()]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId): bool
    {
        $statement = $this->pdo->prepare("DELETE FROM sessions WHERE id = :id");
        $statement->execute(['id' => $sessionId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        $statement = $this->pdo->prepare("DELETE FROM sessions WHERE updated_at < :expiry");
        $statement->execute(['expiry' => time() - $maxlifetime]);
        return true;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    /**
     * {@inheritdoc}
     */
    public function create_sid()
    {
        return uniqid();
    }

    /**
     * {@inheritdoc}
     */
    public function validateId($sessionId)
    {
        $statement = $this->pdo->prepare("SELECT updated_at FROM sessions WHERE id = :id");
        $statement->execute(['id' => $sessionId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row && $row['updated_at'] >= time() - ini_get('session.gc_maxlifetime');
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $sessionData)
    {
        $statement = $this->pdo->prepare("UPDATE sessions SET updated_at = :updated_at WHERE id = :id");
        $statement->execute(['id' => $sessionId, 'updated_at' => time()]);
        return true;
    }
}
