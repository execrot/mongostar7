<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver;

/**
 * Interface CursorAbstract
 * @package MongoStar\Driver
 */
abstract class CursorAbstract implements \Iterator, \ArrayAccess, \Countable
{
    /**
     * @var \MongoStar\Model
     */
    private $_model = null;

    /**
     * @var int
     */
    private $_cursorIndex = 0;

    /**
     * @var array
     */
    private $_cursorData = [];

    /**
     * @var \MongoStar\Model[]
     */
    protected $_documents = [];

    /**
     * MongodbCursor constructor.
     *
     * @param \MongoStar\Model $model
     * @param array $data
     */
    public function __construct(\MongoStar\Model $model, array $data)
    {
        $this->_model = $model;
        $this->_cursorData = $data;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $arrayData = [];

        foreach ($this as $document)
        {
            $arrayData[] = $document->toArray();
        }

        return $arrayData;
    }

    /**
     * @return \MongoStar\Model
     */
    public function getModel() : \MongoStar\Model
    {
        return $this->_model;
    }

    /**
     * @param \MongoStar\Model $model
     */
    public function setModel(\MongoStar\Model $model)
    {
        $this->_model = $model;
    }

    /**
     * @return array
     */
    public function getCursorData(): array
    {
        return $this->_cursorData;
    }

    /**
     * @return int
     */
    public function getCursorIndex(): int
    {
        return $this->_cursorIndex;
    }

    /**
     * @param int $cursorIndex
     */
    public function setCursorIndex(int $cursorIndex)
    {
        $this->_cursorIndex = $cursorIndex;
    }

    /**
     * @param array $cursorData
     */
    public function setCursorData(array $cursorData)
    {
        $this->_cursorData = $cursorData;
    }

    /**
     * @return int
     */
    public function save() : int
    {
        $savedCount = 0;

        foreach ($this->_documents as $document) {
            $savedCount += $document->save();
        }

        return $savedCount;
    }

    /**
     * @param array $data
     * @param int $index
     *
     * @return \MongoStar\Model
     */
    public function getRowWithIndex(array $data, int $index)
    {
        if (isset($this->_documents[$index])) {
            return $this->_documents[$index];
        }

        if (isset($data[$index])) {

            $modelClassName = $this->getModel()->getModelClassName();

            /** @var \MongoStar\Model $model */
            $model = new $modelClassName();
            $model->populate(static::processDataRow($data[$index]));

            $this->_documents[$index] = $model;

            return $model;
        }

        return null;
    }

    /**
     * Can be overi
     *
     * @param array $data
     * @return array
     */
    public function processDataRow(array $data) : array
    {
        return $data;
    }


    /*************** \ArrayIterator implementation ***********/

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (is_numeric($offset)) {
            $data = $this->getCursorData();
            return isset($data[$offset]);
        }

        return false;
    }

    /**
     * @param mixed $offset
     * @return \MongoStar\Model
     */
    public function offsetGet($offset)
    {
        return $this->getRowWithIndex($this->getCursorData(), $offset);
    }

    /**
     * @param null $offset
     * @param null $value
     * @throws Exception\UnsupportedCursorOperation
     */
    public function offsetSet($offset = null, $value = null)
    {
        throw new Exception\UnsupportedCursorOperation("offsetSet - " . $offset);
    }

    /**
     * @param null $offset
     * @throws Exception\UnsupportedCursorOperation
     */
    public function offsetUnset($offset = null)
    {
        throw new Exception\UnsupportedCursorOperation("offsetUnset - " . $offset);
    }


    /*************** \Iterator implementation ***********/

    /**
     * @return \MongoStar\Model
     */
    public function current()
    {
        return $this->getRowWithIndex($this->getCursorData(), $this->_cursorIndex);
    }

    /**
     * return null
     */
    public function next() {
        $this->_cursorIndex++;
    }

    /**
     * @return int|null
     */
    public function key()
    {
        if (isset($this->_cursorData[$this->_cursorIndex])) {
            return $this->_cursorIndex;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->_cursorData[$this->_cursorIndex]);
    }

    /**
     *
     */
    public function rewind()
    {
        $this->_cursorIndex = 0;
    }


    /*************** \Countable implementation ***********/

    /**
     * @return int
     */
    public function count() : int
    {
        return count($this->_cursorData);
    }
}