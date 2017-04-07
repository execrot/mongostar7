<?php

declare( strict_types = 1 );

namespace MongoStar\Model;

/**
 * Interface ModelInterface
 * @package MongoStar\Model
 */
interface ModelInterface
{
    /**
     * @param array $cond
     * @param array $sort
     * @param int|null $count
     * @param int|null $offset
     *
     * @return array|\MongoStar\Model
     */
    public static function fetchAll(array $cond = [], array $sort = [], int $count = null, int $offset = null);

    /**
     * @param array $cond
     * @param array $sort
     *
     * @return null|\MongoStar\Model
     */
    public static function fetchOne(array $cond = [], array $sort = []);

    /**
     * @param array $cond
     * @param array $sort
     * @param int|null $count
     * @param int|null $offset
     *
     * @return \MongoStar\Model
     */
    public static function fetchObject(array $cond = [], array $sort = [], int $count = null, int $offset = null);

    /**
     * @param array $cond
     * @return int
     */
    public static function count(array $cond = []);

    /**
     * @param array $data
     * @return int
     */
    public static function batchInsert(array $data = []);

    /**
     * @param array $cond
     * @param array $data
     *
     * @return int
     */
    public static function update(array $cond = [], array $data = []);

    /**
     * @return array
     */
    public function getData();

    /**
     * @param array $data
     * @return void
     */
    public function populate(array $data);

    /**
     * @return int
     */
    public function save();

    /**
     * @return array
     */
    public function toArray() : array;
}