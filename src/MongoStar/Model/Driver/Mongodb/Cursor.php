<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver\Mongodb;

/**
 * Class Cursor
 * @package MongoStar\Model\Driver\Mongodb
 */
class Cursor implements \MongoStar\Model\Driver\CursorInterface,  \Iterator
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
     * @return mixed
     */
    public function current()
    {
        $data = (array)$this->_cursorData[$this->_cursorIndex];

        $modelClassName = get_class($this->_model);
        $model = new $modelClassName();

        foreach ($this->_model->getMeta()->getProperties() as $property)
        {
            $propertyName = $property->getName();

            if ($propertyName == 'id') {
                $propertyName = '_id';
                $value = $data[$propertyName]['$oid'];
            }
            else {
                $value = isset($data[$propertyName]) ? $data[$propertyName] : null;
            }

            $model->{$property->getName()} = $value;
        }

        return $model;
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
}