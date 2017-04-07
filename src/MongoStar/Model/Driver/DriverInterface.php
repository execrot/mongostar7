<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver;

/**
 * Interface DriverInterface
 * @package MongoStar\Driver
 */
interface DriverInterface
{
    /**
     * Save object
     *
     * @return mixed
     */
    public function save();

    /**
     * @param array $cond
     * @param int|null $limit
     *
     * @return int
     */
    public function remove(array $cond = [], int $limit = null) : int;


    /**
     * Fetch model, can return Model or null
     *
     * @param array|null $cond
     * @param array|null $sort
     *
     * @return \MongoStar\Model|null
     */
    public function fetchOne(array $cond = [], array $sort = []);

    /**
     * Fetch model, always return Model
     *
     * @param array|null $cond
     * @param array|null $sort
     *
     * @return \MongoStar\Model
     */
    public function fetchObject(array $cond = [], array $sort = []) : \MongoStar\Model;

    /**
     * @param array|null $cond
     * @param array|null $sort
     * @param int|null $count
     * @param int|null $offset
     *
     * @return array
     */
    public function fetchAll(array $cond = [], array $sort = [], int $count = null, int $offset = null);

    /**
     * @param array $cond
     * @return int
     */
    public function count(array $cond = []) : int;

    /**
     * @param array|null $data
     *
     * @return int
     */
    public function batchInsert(array $data = null) : int;

    /**
     * @param \MongoStar\Model $model
     */
    public function setModel(\MongoStar\Model $model);
}