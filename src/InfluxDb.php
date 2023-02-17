<?php

namespace Detain\SessionSamurai;

use InfluxDB\Client;
use InfluxDB\Point;
use SessionHandlerInterface;
use SessionIdInterface;
use SessionUpdateTimestampHandlerInterface;

class InfluxDBSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    protected $client;
    protected $database;
    protected $measurement;

    public function __construct(Client $client, string $database, string $measurement = 'sessions')
    {
        $this->client = $client;
        $this->database = $database;
        $this->measurement = $measurement;
    }

    public function open($save_path, $session_name)
    {
        // No action necessary because connection is established in constructor
        return true;
    }

    public function close()
    {
        // No action necessary because connection is closed in destructor
        return true;
    }

    public function read($session_id)
    {
        $result = $this->client->query("SELECT * FROM {$this->measurement} WHERE session_id = '$session_id'", $this->database);
        if ($result->getPoints()) {
            return $result->getPoints()[0]['session_data'];
        } else {
            return '';
        }
    }

    public function write($session_id, $session_data)
    {
        $point = new Point(
            $this->measurement,
            null,
            ['session_id' => $session_id],
            ['session_data' => $session_data],
            time()
        );
        $this->client->writePoints([$point], $this->database);
        return true;
    }

    public function destroy($session_id)
    {
        $this->client->query("DELETE FROM {$this->measurement} WHERE session_id = '$session_id'", $this->database);
        return true;
    }

    public function gc($maxlifetime)
    {
        $maxlifetime = time() - $maxlifetime;
        $this->client->query("DELETE FROM {$this->measurement} WHERE time < $maxlifetime", $this->database);
        return true;
    }

    public function create_sid()
    {
        return bin2hex(random_bytes(16));
    }

    public function validateId($session_id)
    {
        return preg_match('/^[0-9a-f]{32}$/', $session_id) === 1;
    }

    public function updateTimestamp($session_id, $session_data)
    {
        return $this->write($session_id, $session_data);
    }
}
