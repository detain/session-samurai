<?php

namespace Detain\SessionSamurai;

class SQLite3SessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private \SQLite3 $db;
    private string $table = 'sessions';
    private int $lifetime = 1440;

    /**
     * {@inheritdoc}
     */
    public function open(string $savePath, string $sessionName): bool
    {
        $this->db = new \SQLite3($savePath . '/' . $sessionName . '.db');
        $this->db->exec("CREATE TABLE IF NOT EXISTS {$this->table} (id TEXT PRIMARY KEY, data TEXT, timestamp INTEGER)");
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): bool
    {
        $this->db->close();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $sessionId): string
    {
        $stmt = $this->db->prepare("SELECT data FROM {$this->table} WHERE id = :id AND timestamp >= :timestamp");
        if ($stmt === false) {
            return '';
        }
        $stmt->bindValue(':id', $sessionId, SQLITE3_TEXT);
        $stmt->bindValue(':timestamp', time() - $this->lifetime, SQLITE3_INTEGER);
        $result = $stmt->execute();
        if ($result === false) {
            return '';
        }
        $row = $result->fetchArray(SQLITE3_ASSOC);
        if (!is_array($row) || !isset($row['data']) || !is_string($row['data'])) {
            return '';
        }
        return $row['data'];
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $sessionId, string $data): bool
    {
        $stmt = $this->db->prepare("REPLACE INTO {$this->table} (id, data, timestamp) VALUES (:id, :data, :timestamp)");
        if ($stmt === false) {
            return false;
        }
        $stmt->bindValue(':id', $sessionId, SQLITE3_TEXT);
        $stmt->bindValue(':data', $data, SQLITE3_TEXT);
        $stmt->bindValue(':timestamp', time(), SQLITE3_INTEGER);
        $stmt->execute();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $sessionId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        if ($stmt === false) {
            return false;
        }
        $stmt->bindValue(':id', $sessionId, SQLITE3_TEXT);
        $stmt->execute();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc(int $maxlifetime): int|false
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE timestamp < :timestamp");
        if ($stmt === false) {
            return false;
        }
        $stmt->bindValue(':timestamp', time() - $maxlifetime, SQLITE3_INTEGER);
        $stmt->execute();
        return $this->db->changes();
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
        return (bool) preg_match('/^[a-f0-9]{64}$/', $sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp(string $sessionId, string $sessionData): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET timestamp = :timestamp WHERE id = :id");
        if ($stmt === false) {
            return false;
        }
        $stmt->bindValue(':id', $sessionId, SQLITE3_TEXT);
        $stmt->bindValue(':timestamp', time(), SQLITE3_INTEGER);
        $stmt->execute();
        return true;
    }

    public function setLifetime(int $lifetime): void
    {
        $this->lifetime = $lifetime;
    }
}
