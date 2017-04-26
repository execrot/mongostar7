<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver;

/**
 * Interface DriverInterface
 * @package MongoStar\Driver
 */
abstract class DriverAbstract
{
    /**
     * @var \MongoStar\Model
     */
    private $_model = null;

    /**
     * @param \MongoStar\Model $model
     */
    public function setModel(\MongoStar\Model $model)
    {
        $this->_model = $model;
    }

    /**
     * @return \MongoStar\Model
     */
    public function getModel(): \MongoStar\Model
    {
        return $this->_model;
    }

    /**
     * @param array|string|null $cond
     * @param array|string|null $sort
     *
     * @return \MongoStar\Model
     */
    public function fetchObject($cond = null, array $sort = null) : \MongoStar\Model
    {
        $model = static::fetchOne($cond, $sort);

        if ($model) {
            return $model;
        }

        return $this->getModel();
    }

    /**
     * Save object
     *
     * @return mixed
     */
    abstract public function save();

    /**
     * @param array|string|null $cond
     * @param int|null $limit
     *
     * @return int
     */
    abstract public function remove($cond = null, int $limit = null) : int;


    /**
     * @param array|string|null $cond
     * @param array|string|null $sort
     *
     * @return mixed|null
     */
    abstract public function fetchOne($cond = null, $sort = null);

    /**
     * @param array|string|null $cond
     * @param array|string|null $sort
     *
     * @param int|null $count
     * @param int|null $offset
     *
     * @return CursorAbstract
     */
    abstract public function fetchAll($cond = null, $sort = null, int $count = null, int $offset = null);

    /**
     * @param array|string|null $cond
     * @return int
     */
    abstract public function count($cond = null) : int;

    /**
     * @param array|null $data
     *
     * @return int
     */
    abstract public function batchInsert(array $data = null) : int;
}