<?php

namespace Detain\SessionSamurai;

use MongoDB\Collection;
use MongoDB\BSON\UTCDateTime;

class MongoDbSessionHandler implements \SessionHandlerInterface, \SessionIdInterface, \SessionUpdateTimestampHandlerInterface
{
    protected Collection $sessionCollection;

    public function __construct(Collection $sessionCollection)
    {
        $this->sessionCollection = $sessionCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function open(string $savePath, string $sessionName): bool
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
    public function read(string $id): string
    {
        $document = $this->sessionCollection->findOne(['_id' => $id]);

        if ($document === null) {
            return '';
        }
        $docArray = (array) $document;
        return isset($docArray['data']) && is_string($docArray['data']) ? $docArray['data'] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $id, string $data): bool
    {
        $options = ['upsert' => true];
        $query = ['_id' => $id];
        $update = [
            '$set' => [
                'data' => $data,
                'updated_at' => new UTCDateTime(),
            ]
        ];
        $result = $this->sessionCollection->updateOne($query, $update, $options);
        return $result->getModifiedCount() > 0 || $result->getUpsertedCount() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $id): bool
    {
        $result = $this->sessionCollection->deleteOne(['_id' => $id]);
        return $result->getDeletedCount() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function gc(int $maxlifetime): int|false
    {
        $expiry = new UTCDateTime((time() - $maxlifetime) * 1000);
        $result = $this->sessionCollection->deleteMany(['updated_at' => ['$lt' => $expiry]]);
        return (int) $result->getDeletedCount();
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
        return $this->sessionCollection->countDocuments(['_id' => $id]) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp(string $id, string $data): bool
    {
        $options = ['upsert' => false];
        $query = ['_id' => $id];
        $update = ['$set' => ['updated_at' => new UTCDateTime()]];
        $result = $this->sessionCollection->updateOne($query, $update, $options);
        return $result->getModifiedCount() > 0;
    }
}
