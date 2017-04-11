<?php

class CustomDriver implements \MongoStar\Model\Driver\DriverInterface
{
    public function fetchObject(array $cond = [], array $sort = []): \MongoStar\Model
    {
        // TODO: Implement fetchObject() method.
    }

    public function fetchOne(array $cond = [], array $sort = [])
    {

    }

    public function fetchAll(array $cond = [], array $sort = [], int $count = null, int $offset = null)
    {
        // TODO: Implement fetchAll() method.
    }

    public function remove(array $cond = [], int $limit = null): int
    {
        // TODO: Implement remove() method.
    }

    public function setModel(\MongoStar\Model $model)
    {
        // TODO: Implement setModel() method.
    }

    public function save()
    {
        // TODO: Implement save() method.
    }

    public function count(array $cond = []): int
    {
        // TODO: Implement count() method.
    }

    public function batchInsert(array $data = null): int
    {
        // TODO: Implement batchInsert() method.
    }
}