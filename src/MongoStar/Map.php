<?php

declare( strict_types = 1 );

namespace MongoStar;

/**
 * Class Map
 * @package MongoStar
 */
abstract class Map implements Map\MapInterface, \Iterator
{
    /**
     * @var array|object|Model
     */
    private $_data = [];

    /**
     * @var array
     */
    private $_userData = [];

    /**
     * @var int
     */
    private $_index = 0;

    /**
     * @var string
     */
    private $_context = null;

    /**
     * @return array|Model|object
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param array|Model|object $data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * @return array
     */
    public function getUserData(): array
    {
        return $this->_userData;
    }

    /**
     * @param array $userData
     */
    public function setUserData(array $userData = [])
    {
        $this->_userData = $userData;
    }

    /**
     * @return string
     */
    public function getContext() : string
    {
        return $this->_context;
    }

    /**
     * @param string $context
     */
    public function setContext(string $context)
    {
        $this->_context = $context;
    }

    /**
     * @return array|object
     */
    public function current()
    {
        $contextMap = $this->_getContextMap($this->getContext());

        $transformedDataRow = [];

        foreach ($contextMap as $name => $value) {

            if ($this->_isSingleData()) {
                $transformedDataRow[$value] = $this->_transform($this->getData(), $name);
            }
            else {
                $transformedDataRow[$value] = $this->_transform($this->_getDataRow($this->_index), $name);
            }

        }

        return $transformedDataRow;
    }

    /**
     * return null
     */
    public function next() {
        $this->_index++;
    }

    /**
     * @return int|null
     */
    public function key()
    {
        if ($this->_isSetDataRow($this->_index)) {
            return $this->_index;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->_isSetDataRow($this->_index);
    }

    /**
     * Move cursor to the begin
     * return null;
     */
    public function rewind()
    {
        $this->_index = 0;
    }

    /**
     * @param array|Model\ModelInterface $data
     * @param string $context
     * @param array  $userData
     *
     * @return Map
     *
     * @throws Map\Exception\MapContextWasNotFound
     */
    public static function execute($data, string $context = 'common', array $userData = []) : Map
    {
        $mapClassName = static::class;

        /** @var Map $map */
        $map = new $mapClassName();
        $method = [$map, $context];

        if (is_callable($method)) {

            $map->setData($data);
            $map->setUserData($userData);
            $map->setContext($context);

            return $map;
        }

        throw new Map\Exception\MapContextWasNotFound($mapClassName, $context);
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        if ($this->_isSingleData()) {
            return $this->current();
        }

        $arrayData = [];

        foreach ($this as $dataRow) {
            $arrayData[] = $dataRow;
        }

        return $arrayData;
    }

    /**
     * @return array|Model|null|object
     */
    public function getRow()
    {
        if ($this->_isSingleData()) {
            return $this->getData();
        }

        return $this->_getDataRow($this->_index);
    }

    /**
     * @param int $index
     *
     * @return array|object|Model|null
     */
    private function _getDataRow(int $index)
    {
        foreach ($this->getData() as $dataRowIndex => $dataRow) {
            if ($index == $dataRowIndex) {
                return $dataRow;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    private function _isSingleData() : bool
    {
        $data = $this->getData();

        if (is_object($data) && $data instanceof Model\Driver\CursorAbstract) {
            return false;
        }

        if (is_array($data) && isset($data[0])) {
            return false;
        }

        return true;
    }

    /**
     * @param int $index
     * @return bool
     */
    private function _isSetDataRow(int $index) : bool
    {
        foreach ($this->getData() as $dataRowIndex => $dataRow) {
            if ($index == $dataRowIndex) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $dataRow
     * @param string $name
     *
     * @return mixed
     */
    private function _transform($dataRow, string $name)
    {
        $getter = [$this, 'get' . ucfirst($name)];

        if (is_callable($getter)) {
            return call_user_func_array($getter, ['data' => $dataRow]);
        }

        if (is_array($dataRow)) {
            return isset($dataRow[$name]) ? $dataRow[$name] : null;
        }

        if (is_object($dataRow)) {
            return isset($dataRow->$name) ? $dataRow->$name : null;
        }

        return null;
    }

    /**
     * @param string $context
     * @return array
     */
    private function _getContextMap(string $context) : array
    {
        return call_user_func_array([$this, $context], []);
    }
}