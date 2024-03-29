<?php

namespace Detain\SessionSamurai;

class Mysqli3SessionHandler implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface
{
    private $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function open($savePath, $sessionName)
    {
        // no action required here
        return true;
    }

    public function close()
    {
        $this->db->close();
        return true;
    }

    public function read($sessionId)
    {
        $stmt = $this->db->prepare("SELECT data FROM sessions WHERE id = ?");
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        $stmt->bind_result($data);
        $result = $stmt->fetch() ? $data : '';
        $stmt->close();
        return $result;
    }

    public function write($sessionId, $data)
    {
        $stmt = $this->db->prepare("REPLACE INTO sessions (id, data, last_updated) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $sessionId, $data, time());
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function destroy($sessionId)
    {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = ?");
        $stmt->bind_param("s", $sessionId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function gc($maxLifetime)
    {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE last_updated < ?");
        $stmt->bind_param("s", time() - $maxLifetime);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid()
    {
        return bin2hex(random_bytes(16));
    }

    public function validateId($sessionId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sessions WHERE id = ?");
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }

    public function updateTimestamp($sessionId, $sessionData)
    {
        $stmt = $this->db->prepare("UPDATE sessions SET last_updated = ? WHERE id = ?");
        $stmt->bind_param("ss", time(), $sessionId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
