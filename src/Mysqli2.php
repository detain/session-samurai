<?php

namespace Detain\SessionSamurai;

class Mysqli2 implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    protected $handler;

    /**
    * Session Handler Constructor
    *
    * @param array $info array of information needed to connect to a Mysql DB
    */
    public function __construct($info) {
        $this->handler = new mysqli($info['host'], $info['username'], $info['password'], $info['dbname']);
    }

    /**
    * Open Session - Wrapper for SessionHandlerInterface
    *
    * @param string $save_path save path
    * @param string $session_name session name
    */
    public function open($save_path, $session_name) {
        return true;
    }

    /**
    * Close Session - Wrapper for SessionHandlerInterface
    */
    public function close() {
        return true;
    }

    /**
    * Write Session - Wrapper for SessionHandlerInterface
    *
    * @param string $session_id session id
    * @param string $data serialized session data
    */
    public function write($session_id, $data) {
        $query = sprintf("REPLACE INTO session (id, data, date_created) VALUES ('%s', '%s', NOW())", $this->handler->real_escape_string($session_id), $this->handler->real_escape_string($data));
        $this->handler->query($query);
        return $this->handler->affected_rows;
    }

    /**
    * Read Session - Wrapper for SessionHandlerInterface
    *
    * @param string $session_id session id
    */
    public function read($session_id) {
        $query = sprintf("SELECT data FROM session WHERE id = '%s'", $this->handler->real_escape_string($session_id));
        if ($result = $this->handler->query($query)) {
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
    public function destroy($session_id) {
        $query = sprintf("DELETE FROM session WHERE id = '%s'", $this->handler->real_escape_string($session_id));
        $this->handler->query($query);
        return $this->handler->affected_rows;
    }

    /**
    * Garbage Collection - Wrapper for SessionHandlerInterface
    *
    * @param int $maxLifetime the session lifetime in seconds
    */
    public function gc($maxLifetime) {
        $query = sprintf('DELETE FROM session WHERE DATE_ADD(date_created, INTERVAL %d SECOND) < NOW()', (int)$maxLifetime);
        $this->handler->query($query);
        return $this->handler->affected_rows;
    }
}
