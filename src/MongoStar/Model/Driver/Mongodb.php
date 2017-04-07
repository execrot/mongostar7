<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver;

/**
 * Class Mongodb
 * @package MongoStar\Driver
 */
class Mongodb implements DriverInterface
{
    /**
     * @var \MongoDB\Driver\Manager
     */
    private static $_manager = null;

    /**
     * @var \MongoStar\Model
     */
    private $_model = null;

    /**
     * @var null$
     */
    private $_cursor = null;

    /**
     * @return \MongoDB\Driver\Manager
     */
    public static function getManager() : \MongoDB\Driver\Manager
    {
        if (!self::$_manager) {
            self::$_manager =  new \MongoDB\Driver\Manager(\MongoStar\Config::getConfig()['server']);
        }

        return self::$_manager;
    }

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
     * @return string
     */
    public function getCollectionNamespace()
    {
        return implode('.', [
            \MongoStar\Config::getConfig()['db'],
            $this->getModel()->getMeta()->getCollection()
        ]);
    }

    /**
     * Save object
     *
     * @return mixed
     */
    public function save()
    {
        $manager = self::getManager();

        $bulk = new \MongoDB\Driver\BulkWrite();
        $data = $this->getModel()->getData();

        if ($this->getModel()->id) {
            $cond = $this->_replaceIdToObjectId(['id' => $this->getModel()->id]);
            $bulk->update($cond, ['$set' => $data], ['multi' => true, 'upsert' => false]);
        }
        else {
            $this->getModel()->id = (string)$bulk->insert($data);
        }

        $writeConcern = new \MongoDB\Driver\WriteConcern(
            \MongoDB\Driver\WriteConcern::MAJORITY, 1000
        );

        $result = $manager->executeBulkWrite(
            $this->getCollectionNamespace(), $bulk, $writeConcern
        );

        if (!$this->getModel()->id) {
            return $result->getInsertedCount();
        }

        return $result->getModifiedCount();
    }

    /**
     * @param array $cond
     * @param int|null $limit
     *
     * @return int
     */
    public function remove(array $cond = [], int $limit = null) : int
    {
        if ($this->getModel()->id) {

            $cond = $this->_replaceIdToObjectId([
                'id' => $this->getModel()->id
            ]);

            $limit = 1;
        }

        $bulk = new \MongoDB\Driver\BulkWrite();

        $bulk->delete($cond, ['limit' => $limit]);

        $writeConcern = new \MongoDB\Driver\WriteConcern(
            \MongoDB\Driver\WriteConcern::MAJORITY, 100
        );

        $result = self::getManager()->executeBulkWrite(
            $this->getCollectionNamespace(), $bulk, $writeConcern
        );

        return $result->getDeletedCount();
    }


    /**
     * @param array $cond
     * @param array $sort
     *
     * @return \MongoStar\Model|null
     */
    public function fetchOne(array $cond = [], array $sort = [])
    {
        $manager = self::getManager();

        $query = new \MongoDB\Driver\Query($this->_replaceIdToObjectId($cond), [
            'limit' => 1,
            'sort' => $sort
        ]);

        $collectionNamespace = $this->getCollectionNamespace();

        $cursor = $manager->executeQuery($collectionNamespace, $query);

        $data = json_decode(json_encode($cursor->toArray()), true);

        if (count($data) == 0) {
            return null;
        }

        $cursor = new Mongodb\Cursor($this->getModel(), $data);

        foreach ($cursor as $item) {
            return $item;
        }
    }

    /**
     * Fetch model, always return Model
     *
     * @param array|null $cond
     * @param array|null $sort
     *
     * @return \MongoStar\Model
     */
    public function fetchObject(array $cond = [], array $sort = []) : \MongoStar\Model
    {
        $model = $this->fetchOne($cond, $sort);

        if ($model) {
            return $model;
        }

        return $this->getModel();
    }

    /**
     * @param array|null $cond
     * @param array|null $sort
     * @param int|null $count
     * @param int|null $offset
     *
     * @return Mongodb\Cursor
     */
    public function fetchAll(array $cond = [], array $sort = [], int $count = null, int $offset = null)
    {
        $options = [
            'sort' => $sort,
            'skip' => $offset,
            'limit' => $count
        ];
        $query = new \MongoDB\Driver\Query($this->_replaceIdToObjectId($cond), $options);

        $cursor = self::getManager()->executeQuery($this->getCollectionNamespace(), $query);

        $data = json_decode(json_encode($cursor->toArray()), true);

        return new Mongodb\Cursor($this->getModel(), $data);
    }

    /**
     * @param array $cond
     * @return int
     */
    public function count(array $cond = []) : int
    {
        $command = new \MongoDB\Driver\Command([
            "count" => $this->getModel()->getMeta()->getCollection(),
            "query" => $this->_replaceIdToObjectId($cond)
        ]);

        $manager = self::getManager();

        $cursor = $manager->executeCommand(\MongoStar\Config::getConfig()['db'], $command);

        try {
            $res = current($cursor->toArray());
            return $res->n;
        }
        catch (\Exception $e) {}

        return 0;
    }

    /**
     * @param array|null $data
     *
     * @return int
     */
    public function batchInsert(array $data = null) : int
    {
        $manager = self::getManager();

        $bulk = new \MongoDB\Driver\BulkWrite();

        foreach ($data as $dataItem) {

            if ($this->getModel()->getMeta()->isValid($dataItem)) {
                $bulk->insert($dataItem);
            }
        }

        $collectionNamespace = $this->getCollectionNamespace();

        if ($bulk->count()) {
            $writeResults = $manager->executeBulkWrite($collectionNamespace, $bulk);
            return $writeResults->getInsertedCount();
        }

        return 0;
    }

    /**
     * @param array $cond
     * @return array
     */
    private function _replaceIdToObjectId(array $cond = []) : array
    {
        if (array_key_exists('id', $cond)) {

            $cond['_id'] = new \MongoDB\BSON\ObjectID(
                !empty($cond['id']) ? $cond['id'] : null
            );
            unset($cond['id']);
        }

        return $cond;
    }
}