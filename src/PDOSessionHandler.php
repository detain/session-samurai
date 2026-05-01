<?php

namespace Detain\SessionSamurai;

class PDOSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    protected \PDO $pdo;

    public function __construct(\PDO $pdo)
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
    public function read(string $sessionId): string
    {
        $statement = $this->pdo->prepare("SELECT data FROM sessions WHERE id = :id");
        $statement->execute(['id' => $sessionId]);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        if (!is_array($row) || !isset($row['data']) || !is_string($row['data'])) {
            return '';
        }
        return $row['data'];
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
    public function gc(int $maxlifetime): int|false
    {
        $statement = $this->pdo->prepare("DELETE FROM sessions WHERE updated_at < :expiry");
        $statement->execute(['expiry' => time() - $maxlifetime]);
        return $statement->rowCount();
    }

    /**
     * {@inheritdoc}
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * {@inheritdoc}
     */
    public function validateId(string $sessionId): bool
    {
        $statement = $this->pdo->prepare("SELECT updated_at FROM sessions WHERE id = :id");
        $statement->execute(['id' => $sessionId]);
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        if (!is_array($row) || !isset($row['updated_at'])) {
            return false;
        }
        $rawAt = $row['updated_at'];
        $updatedAt = is_int($rawAt) ? $rawAt : (is_string($rawAt) ? (int) $rawAt : 0);
        return $updatedAt >= time() - (int) ini_get('session.gc_maxlifetime');
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp(string $sessionId, string $sessionData): bool
    {
        $statement = $this->pdo->prepare("UPDATE sessions SET updated_at = :updated_at WHERE id = :id");
        $statement->execute(['id' => $sessionId, 'updated_at' => time()]);
        return true;
    }
}
