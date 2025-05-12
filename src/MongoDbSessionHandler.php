<?php

namespace Detain\SessionSamurai;

class MongoDbSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    protected $mongoConnection;
    protected $sessionCollection;

    public function __construct($mongoConnection)
    {
        $this->mongoConnection = $mongoConnection;
        $this->sessionCollection = new MongoCollection($this->mongoConnection, "sessions");
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName): bool
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
    public function read($id)
    {
        $cursor = $this->sessionCollection->findOne(['_id' => $id], ['data' => true]);

        if ($cursor !== null) {
            return $cursor['data'];
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($id, $data)
    {
        $options = ['upsert' => true];
        $query = ['_id' => $id];
        $update = [
            'data' => $data,
            'updated_at' => new MongoDBTimestamp()
        ];
        $result = $this->sessionCollection->updateOne($query, ['$set' => $update], $options);
        return $result->getModifiedCount() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($id)
    {
        $options = ['w' => 1];
        $query = ['_id' => $id];
        $result = $this->sessionCollection->removeOne($query, $options);
        return $result->getDeletedCount() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime): bool
    {
        return true;
    }

    // SessionIdInterface
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    /**
     * {@inheritdoc}
     */
    public function create_sid()
    {
        $sid = base64_encode(openssl_random_pseudo_bytes(20));
        return preg_replace("/\W/", "", $sid);
    }

    // SessionUpdateTimestampHandlerInterface
    /**
     * {@inheritdoc}
     */
    public function validateId($id)
    {
        return $this->sessionCollection->count(['_id' => $id]) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($id, $timestamp)
    {
        $options = ['upsert' => true];
        $query = ['_id' => $id];
        $update = [
            'updated_at' => new MongoDBTimestamp()
        ];
        $result = $this->sessionCollection->updateOne($query, ['$set' => $update], $opts);
        return $result->getModifiedCount() > 0;
    }
}
