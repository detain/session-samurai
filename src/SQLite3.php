<?php

namespace Detain\SessionSamurai;

class SQLite3 implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    private $db;
    private $table = 'sessions';
    private $lifetime = 1440;

    public function open($savePath, $sessionName) {
        $this->db = new SQLite3($savePath . '/' . $sessionName . '.db');
        $this->db->exec("CREATE TABLE IF NOT EXISTS {$this->table} (id TEXT PRIMARY KEY, data TEXT, timestamp INTEGER)");
        return true;
    }

    public function close() {
        $this->db->close();
        return true;
    }

    public function read($sessionId) {
        $stmt = $this->db->prepare("SELECT data FROM {$this->table} WHERE id = :id AND timestamp >= :timestamp");
        $stmt->bindValue(':id', $sessionId, SQLITE3_TEXT);
        $stmt->bindValue(':timestamp', time() - $this->lifetime, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $data = $result->fetchArray(SQLITE3_ASSOC);
        return $data['data'] ?? '';
    }

    public function write($sessionId, $data) {
        $stmt = $this->db->prepare("REPLACE INTO {$this->table} (id, data, timestamp) VALUES (:id, :data, :timestamp)");
        $stmt->bindValue(':id', $sessionId, SQLITE3_TEXT);
        $stmt->bindValue(':data', $data, SQLITE3_TEXT);
        $stmt->bindValue(':timestamp', time(), SQLITE3_INTEGER);
        $stmt->execute();
        return true;
    }

    public function destroy($sessionId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindValue(':id', $sessionId, SQLITE3_TEXT);
        $stmt->execute();
        return true;
    }

    public function gc($maxlifetime) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE timestamp < :timestamp");
        $stmt->bindValue(':timestamp', time() - $this->lifetime, SQLITE3_INTEGER);
        $stmt->execute();
        return true;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid() {
        return uniqid();
    }

    public function validateId($sessionId) {
        return preg_match('/^[a-f\d]{32}$/i', $sessionId);
    }

    public function updateTimestamp($sessionId, $sessionData) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET timestamp = :timestamp WHERE id = :id");
        $stmt->bindValue(':id', $sessionId, SQLITE3_TEXT);
        $stmt->bindValue(':timestamp', time(), SQLITE3_INTEGER);
        $stmt->execute();
        return true;
    }

    public function setLifetime($lifetime) {
        $this->lifetime = $lifetime;
    }
}
