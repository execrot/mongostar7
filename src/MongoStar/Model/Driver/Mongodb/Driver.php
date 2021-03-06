<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver\Mongodb;

/**
 * Class Driver
 * @package MongoStar\Model\Driver\Mongodb
 */
class Driver extends \MongoStar\Model\Driver\DriverAbstract
{
    /**
     * @var \MongoDB\Driver\Manager
     */
    private static $_manager = null;

    /**
     * @return \MongoDB\Driver\Manager
     */
    public static function getManager() : \MongoDB\Driver\Manager
    {
        if (!self::$_manager) {

            $config = \MongoStar\Config::getConfig();

            $credentials = null;
            if (isset($config['username']) && isset($config['password'])) {
                $credentials = implode(':', [$config['username'], $config['password']]) . '@';
            }

            $servers = [];

            foreach ($config['servers'] as $server) {
                $servers[] = implode(':', [$server['host'], $server['port']]);
            }

            $servers = implode(',', $servers) . '/' . $config['db'];

            $replicaSetName = null;
            if (isset($config['replicaSetName']) && !empty($config['replicaSetName'])) {
                $replicaSetName = '?replicaSet=' . $config['replicaSetName'];
            }

            $connection = 'mongodb://' . $credentials . $servers . $replicaSetName;

            self::$_manager = new \MongoDB\Driver\Manager($connection);
        }

        return self::$_manager;
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
     * @return int|null
     */
    public function save()
    {
        $manager = self::getManager();

        $bulk = new \MongoDB\Driver\BulkWrite();
        $data = $this->getModel()->getData();

        if ($this->getModel()->id) {
            $cond = $this->_replaceIdToObjectId(['id' => $this->getModel()->id]);
            $data = $this->_replaceIdToObjectId($data);

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
     * @param array|string|null $cond
     * @param int|null $limit
     *
     * @return int
     */
    public function remove($cond = null, int $limit = null) : int
    {
        if ($this->getModel()->id) {

            $cond = $this->_replaceIdToObjectId([
                'id' => $this->getModel()->id
            ]);

            $limit = 1;
        }
        else {
            list($cond) = $this->_processQuery($cond);
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
     * @param array|string|null $cond
     * @param array|string|null $sort
     *
     * @return \MongoStar\Model|null
     */
    public function fetchOne($cond = null, $sort = null)
    {
        list($cond, $sort) = $this->_processQuery($cond, $sort);

        $query = new \MongoDB\Driver\Query($cond, [
            'limit' => 1,
            'sort' => $sort
        ]);

        $cursor = new Cursor($this->getModel(), $query);

        if ($cursor->count() < 1) {
            return null;
        }

        return $cursor->offsetGet(0);
    }

    /**
     * @param array|string|null $cond
     * @param array|string|null $sort
     *
     * @param int|null $count
     * @param int|null $offset
     *
     * @return Cursor
     */
    public function fetchAll($cond = null, $sort = null, int $count = null, int $offset = null)
    {
        list($cond, $sort) = $this->_processQuery($cond, $sort);

        $query = new \MongoDB\Driver\Query($cond, [
            'sort' => $sort,
            'skip' => $offset,
            'limit' => $count
        ]);

        return new Cursor($this->getModel(), $query);
    }

    /**
     * @param array|string|null $cond
     * @return int
     */
    public function count($cond = null) : int
    {
        list($cond) = $this->_processQuery($cond, null);

        $command = new \MongoDB\Driver\Command([
            "count" => $this->getModel()->getMeta()->getCollection(),
            "query" => $cond
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

            $modelClassName = $this->getModel()->getModelClassName();

            /** @var \MongoStar\Model $model */
            $model = new $modelClassName();
            $model->populate($dataItem);

            $bulk->insert($model->getData());
        }

        if ($bulk->count()) {

            $collectionNamespace = $this->getCollectionNamespace();

            $writeResults = $manager->executeBulkWrite($collectionNamespace, $bulk);
            return $writeResults->getInsertedCount();
        }

        return 0;
    }

    /**
     * @param array|string|null $cond
     * @return mixed
     */
    private function _replaceIdToObjectId($cond = null)
    {
        if (!is_array($cond)) {
            $cond = [$cond];
        }

        if (is_array($cond) && array_key_exists('id', $cond)) {

            $cond['_id'] = new \MongoDB\BSON\ObjectID(
                !empty($cond['id']) ? $cond['id'] : null
            );
            unset($cond['id']);
        }

        return $cond;
    }

    /**
     * @param array|string|null $cond
     * @param array|string|null $sort
     *
     * @return array
     */
    private function _processQuery($cond = null, $sort = null) : array
    {
        if ($cond === null) {
            $cond = [];
        }

        $cond = $this->_replaceIdToObjectId($cond);

        if ($sort === null) {
            $sort = [];
        }

        return [$cond, $sort];
    }
}
