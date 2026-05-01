<?php

namespace Detain\SessionSamurai;

class MysqliSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    protected \mysqli $db;

    public function __construct(\mysqli &$db)
    {
        $this->db = &$db;
    }

    /**
     * {@inheritdoc}
     */
    public function open(string $save_path, string $name): bool
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
    public function read(string $sid): string
    {
        $stmt = $this->db->prepare("SELECT data FROM sessions WHERE sid=?");
        if ($stmt === false) {
            return '';
        }
        $stmt->bind_param('s', $sid);
        $stmt->execute();
        $data = '';
        $stmt->bind_result($data);
        $stmt->fetch();
        return is_string($data) ? $data : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $sid, string $data): bool
    {
        $stmt = $this->db->prepare("UPDATE sessions SET data=?, timestamp=UNIX_TIMESTAMP() WHERE sid=?");
        if ($stmt === false) {
            return false;
        }
        $stmt->bind_param('ss', $data, $sid);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return true;
        }

        $insert = $this->db->prepare("INSERT INTO sessions (sid, data) VALUES (?, ?)");
        if ($insert === false) {
            return false;
        }
        $insert->bind_param('ss', $sid, $data);
        $insert->execute();
        return $insert->affected_rows > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $sid): bool
    {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE sid=?");
        if ($stmt === false) {
            return false;
        }
        $stmt->bind_param('s', $sid);
        $stmt->execute();
        return $stmt->affected_rows >= 0;
    }

    /**
     * {@inheritdoc}
     */
    public function gc(int $maxLifeTime): int|false
    {
        $timestamp = time() - $maxLifeTime;
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE timestamp < ?");
        if ($stmt === false) {
            return false;
        }
        $stmt->bind_param('i', $timestamp);
        $stmt->execute();
        return (int) $stmt->affected_rows;
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
    public function validateId(string $id): bool
    {
        $stmt = $this->db->prepare("SELECT sid FROM sessions WHERE sid=?");
        if ($stmt === false) {
            return false;
        }
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp(string $id, string $data): bool
    {
        $stmt = $this->db->prepare("UPDATE sessions SET timestamp=UNIX_TIMESTAMP() WHERE sid=?");
        if ($stmt === false) {
            return false;
        }
        $stmt->bind_param('s', $id);
        $stmt->execute();
        return true;
    }
}
