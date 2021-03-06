<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver\Mongodb;

/**
 * Class Cursor
 * @package MongoStar\Model\Driver\Mongodb
 */
class Cursor extends \MongoStar\Model\Driver\CursorAbstract
{
    /**
     * @var \MongoDB\Driver\Cursor
     */
    private $_cursor = null;

    /**
     * @var \IteratorIterator
     */
    private $_iterator = null;

    /**
     * @var int
     */
    private $_count = -1;

    /**
     * @var \MongoDB\Driver\Query
     */
    private $_query = null;

    /**
     * Cursor constructor.
     *
     * @param \MongoStar\Model $model
     * @param \MongoDB\Driver\Query $query
     */
    public function __construct(\MongoStar\Model $model, \MongoDB\Driver\Query $query)
    {
        parent::__construct($model, []);
        $this->_query = $query;
    }

    /**
     * @return \MongoStar\Model
     */
    private function _getDataModel() : \MongoStar\Model
    {
        $data = json_decode(json_encode($this->_iterator->current()), true);

        $modelClassName = $this->getModel()->getModelClassName();

        /** @var \MongoStar\Model $model */
        $model = new $modelClassName();
        $model->populate($this->processDataRow($data));

        return $model;
    }

    /*************** \ArrayIterator implementation ***********/

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (is_numeric($offset)) {

            $this->rewind();

            for ($i=0; $i<$offset; $i++) {
                $this->next();
            }

            return $this->valid();
        }

        return false;
    }

    /**
     * @param mixed $offset
     *
     * @return \MongoStar\Model
     * @throws \MongoStar\Model\Driver\Exception\IndexOutOfRange
     */
    public function offsetGet($offset)
    {
        if (isset($this->_documents[$offset])) {
            return $this->_documents[$offset];
        }

        $this->rewind();

        for ($i=0; $i<$offset; $i++) {

            try {
                $this->_iterator->next();
            }
            catch (\Exception $e) {
                throw new \MongoStar\Model\Driver\Exception\IndexOutOfRange($offset, $i+1);
            }
        }

        $this->_documents[$offset] = $this->_getDataModel();
        return $this->_documents[$offset];
    }



    /*************** \Iterator implementation ***********/

    /**
     * @return \MongoStar\Model
     */
    public function current()
    {
        $offset = $this->_iterator->key();

        if (isset($this->_documents[$offset])) {
            return $this->_documents[$offset];
        }

        $this->_documents[$offset] = $this->_getDataModel();
        return $this->_documents[$offset];
    }

    /**
     * return null
     */
    public function next()
    {
        $this->_iterator->next();
    }

    /**
     * @return int|null
     */
    public function key()
    {
        return $this->_iterator->key();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->_iterator->valid();
    }

    /**
     *
     */
    public function rewind()
    {
        $this->_cursor = $this->_executeQuery($this->_query);
        $this->_iterator = new \IteratorIterator($this->_cursor);

        $this->_iterator->rewind();
    }


    /*************** \Countable implementation ***********/

    /**
     * @return int
     */
    public function count() : int
    {
        if ($this->_count == -1) {

            $queryResult = $this->_executeQuery($this->_query);
            $this->_count = count($queryResult->toArray());
        }

        return $this->_count;
    }

    /**
     * @param array $data
     * @return array
     */
    public function processDataRow(array $data) : array
    {
        if (isset($data['_id'])) {
            $data['id'] = $data['_id']['$oid'];
            unset($data['_id']);
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getCollectionNamespace() : string
    {
        return implode('.', [
            \MongoStar\Config::getConfig()['db'],
            $this->getModel()->getMeta()->getCollection()
        ]);
    }

    /**
     * @param \MongoDB\Driver\Query $query
     * @return \MongoDB\Driver\Cursor
     */
    private function _executeQuery(\MongoDB\Driver\Query $query) : \MongoDB\Driver\Cursor
    {
        $manager = Driver::getManager();

        return $manager->executeQuery(
            $this->getCollectionNamespace(),
            $query
        );
    }
}
