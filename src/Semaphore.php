<?php

class Semaphore implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface {

    private $sessionId;
    private $lock;
    private $data;
    private $sessionIdKey = 'semaphore_session_id';
    private $sessionLifetime = 3600;

    public function open($savePath, $sessionName) {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($sessionId) {
        $this->lock = sem_get($sessionId);
        sem_acquire($this->lock);
        $this->sessionId = $sessionId;
        $this->data = shm_attach($sessionId);
        if (shm_has_var($this->data, $this->sessionIdKey)) {
            $sessionData = shm_get_var($this->data, $this->sessionIdKey);
            sem_release($this->lock);
            return $sessionData;
        }
        sem_release($this->lock);
        return '';
    }

    public function write($sessionId, $sessionData) {
        $this->lock = sem_get($sessionId);
        sem_acquire($this->lock);
        $this->sessionId = $sessionId;
        $this->data = shm_attach($sessionId);
        shm_put_var($this->data, $this->sessionIdKey, $sessionData);
        sem_release($this->lock);
        return true;
    }

    public function destroy($sessionId) {
        $this->lock = sem_get($sessionId);
        sem_acquire($this->lock);
        $this->sessionId = $sessionId;
        $this->data = shm_attach($sessionId);
        shm_remove($this->data, $this->sessionIdKey);
        shm_remove($this->data);
        sem_release($this->lock);
        return true;
    }

    public function gc($maxLifetime) {
        return true;
    }

    public function create_sid() {
        $sessionId = uniqid();
        $this->lock = sem_get($sessionId);
        sem_acquire($this->lock);
        $this->sessionId = $sessionId;
        $this->data = shm_attach($sessionId);
        sem_release($this->lock);
        return $sessionId;
    }

    public function validateId($sessionId) {
        $this->lock = sem_get($sessionId);
        sem_acquire($this->lock);
        $this->sessionId = $sessionId;
        $this->data = shm_attach($sessionId);
        $sessionExists = shm_has_var($this->data, $this->sessionIdKey);
        sem_release($this->lock);
        if ($sessionExists) {
            $lastModifiedTime = $this->read($sessionId . '_last_modified_time');
            if ($lastModifiedTime + $this->sessionLifetime < time()) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function updateTimestamp($sessionId, $sessionData) {
        $this->lock = sem_get($sessionId);
        sem_acquire($this->lock);
        $this->sessionId = $sessionId;
        $this->data = shm_attach($sessionId);
        shm_put_var($this->data, $sessionId . '_last_modified_time', time());
        sem_release($this->lock);
        return true;
    }
}
