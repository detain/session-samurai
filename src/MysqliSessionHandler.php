<?php

namespace Detain\SessionSamurai;

class MysqliSessionHandler implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface
{
    protected $db;

    public function __construct(\mysqli &$db)
    {
        $this->db = &$db;
    }

    //open a connection to the session storage
    /**
     * {@inheritdoc}
     */
    public function open($save_path, $name)
    {
        return true;
    }

    //close the connection to the session storage
    /**
     * {@inheritdoc}
     */
    public function close(): bool
    {
        return true;
    }

    //read the session data for this session
    /**
     * {@inheritdoc}
     */
    public function read($sid)
    {
        $select_statement = $this->db->prepare("SELECT data FROM sessions WHERE sid=?");
        $select_statement->bind_param('s', $sid);
        $select_statement->execute();
        $select_statement->bind_result($data);
        $select_statement->fetch();

        return $data;
    }

    //write the session data to the session storage
    /**
     * {@inheritdoc}
     */
    public function write($sid, $data)
    {
        $update_statement = $this->db->prepare("UPDATE sessions SET data=?, timestamp=UNIX_TIMESTAMP() WHERE sid=?");
        $update_statement->bind_param('ss', $data, $sid);
        $update_statement->execute();

        if ($update_statement->affected_rows > 0) {
            return true;
        } else {
            $insert_statement = $this->db->prepare("INSERT INTO sessions (sid, data) VALUES (?, ?)");
            $insert_statement->bind_param('ss', $sid, $data);
            $insert_statement->execute();
            return $insert_statement->affected_rows > 0;
        }
    }

    //destroy the session data from the session storage
    /**
     * {@inheritdoc}
     */
    public function destroy($sid)
    {
        $delete_statement = $this->db->prepare("DELETE FROM sessions WHERE sid=?");
        $delete_statement->bind_param('s', $sid);
        $delete_statement->execute();
        return $delete_statement->affected_rows > 0;
    }

    //Garbage collection of expired sessions from the session storage
    /**
     * {@inheritdoc}
     */
    public function gc($maxLifeTime)
    {
        $timestamp = time() - $maxLifeTime;
        $delete_statement = $this->db->prepare("DELETE FROM sessions WHERE timestamp < ?");
        $delete_statement->bind_param('i', $timestamp);
        $delete_statement->execute();
        return $delete_statement->affected_rows;
    }

    //Generate a new Session ID
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    /**
     * {@inheritdoc}
     */
    public function create_sid()
    {
        return bin2hex(random_bytes(32));
    }
}
