<?php

namespace Detain\SessionSamurai;

class MongoDb implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    protected $mongoConnection;
    protected $sessionCollection;

    public function __construct($mongoConnection) {
        $this->mongoConnection = $mongoConnection;
        $this->sessionCollection = new MongoCollection($this->mongoConnection, "sessions");
    }

    public function open($savePath, $sessionName) {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($id) {
        $cursor = $this->sessionCollection->findOne(array('_id' => $id), array('data' => true));

        if ($cursor !== null) {
            return $cursor['data'];
        }

        return '';
    }

    public function write($id, $data) {
        $options = array('upsert' => true);
        $query = array('_id' => $id);
        $update = array(
            'data' => $data,
            'updated_at' => new MongoDBTimestamp()
        );
        $result = $this->sessionCollection->updateOne($query, array('$set' => $update), $options);
        return $result->getModifiedCount() > 0;
    }

    public function destroy($id) {
        $options = array('w' => 1);
        $query = array('_id' => $id);
        $result = $this->sessionCollection->removeOne($query, $options);
        return $result->getDeletedCount() > 0;
    }

    // SessionIdInterface
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid() {
        $sid = base64_encode(openssl_random_pseudo_bytes(20));
        return preg_replace("/\W/", "", $sid);
    }

    // SessionUpdateTimestampHandlerInterface
    public function validateId($id) {
        return $this->sessionCollection->count(array('_id' => $id)) > 0;
    }

    public function updateTimestamp($id, $timestamp) {
        $options = array('upsert' => true);
        $query = array('_id' => $id);
        $update = array(
            'updated_at' => new MongoDBTimestamp()
        );
        $result = $this->sessionCollection->updateOne($query, array('$set' => $update), $opts);
        return $result->getModifiedCount() > 0;
    }
}
