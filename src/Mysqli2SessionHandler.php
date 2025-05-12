<?php

namespace Detain\SessionSamurai;

class Mysqli2SessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    protected $db;

    /**
    * Session Handler Constructor
    *
    * @param array $info array of information needed to connect to a Mysql DB
    */
    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
    * Open Session - Wrapper for SessionHandlerInterface
    *
    * @param string $save_path save path
    * @param string $session_name session name
    */
    /**
     * {@inheritdoc}
     */
    public function open($save_path, $session_name): bool
    {
        return true;
    }

    /**
    * Close Session - Wrapper for SessionHandlerInterface
    */
    /**
     * {@inheritdoc}
     */
    public function close(): bool
    {
        return true;
    }

    /**
    * Write Session - Wrapper for SessionHandlerInterface
    *
    * @param string $session_id session id
    * @param string $data serialized session data
    */
    /**
     * {@inheritdoc}
     */
    public function write($session_id, $data)
    {
        $query = sprintf("REPLACE INTO session (id, data, date_created) VALUES ('%s', '%s', NOW())", $this->db->real_escape_string($session_id), $this->db->real_escape_string($data));
        $this->db->query($query);
        return $this->db->affected_rows;
    }

    /**
    * Read Session - Wrapper for SessionHandlerInterface
    *
    * @param string $session_id session id
    */
    /**
     * {@inheritdoc}
     */
    public function read($session_id)
    {
        $query = sprintf("SELECT data FROM session WHERE id = '%s'", $this->db->real_escape_string($session_id));
        if ($result = $this->db->query($query)) {
            if ($row = $result->fetch_object()) {
                return $row->data;
            }
        }
        return false;
    }

    /**
    * Destroy Session - Wrapper for SessionHandlerInterface
    *
    * @param string $session_id session id
    */
    /**
     * {@inheritdoc}
     */
    public function destroy($session_id): bool
    {
        $query = sprintf("DELETE FROM session WHERE id = '%s'", $this->db->real_escape_string($session_id));
        $this->db->query($query);
        return $this->db->affected_rows;
    }

    /**
    * Garbage Collection - Wrapper for SessionHandlerInterface
    *
    * @param int $maxLifetime the session lifetime in seconds
    */
    /**
     * {@inheritdoc}
     */
    public function gc($maxLifetime)
    {
        $query = sprintf('DELETE FROM session WHERE DATE_ADD(date_created, INTERVAL %d SECOND) < NOW()', (int)$maxLifetime);
        $this->db->query($query);
        return $this->db->affected_rows;
    }
}
