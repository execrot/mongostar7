<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver;

/**
 * Interface DocumentInterface
 * @package MongoStar\Driver
 */
abstract class DocumentAbstract implements \ArrayAccess
{
    /**
     * @var \MongoStar\Model|null
     */
    private $_model = null;

    /**
     * @var array
     */
    private $_data = [];

    /**
     * Document constructor.
     * @param \MongoStar\Model $model
     */
    public function __construct(\MongoStar\Model $model)
    {
        $this->_model = $model;
    }

    /**
     * @return \MongoStar\Model|null
     */
    public function getModel()
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
    public function getData() : array
    {
        return $this->_data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->_data = $data;
    }

    /**
     * Populate model with data
     *
     * @param array $data
     * @return bool
     */
    public function populate(array $data) : bool
    {
        if ($this->getModel()->getMeta()->isValid($data)) {

            foreach ($this->getModel()->getMeta()->getProperties() as $property) {

                if (isset($data[$property->getName()])) {
                    $this->__set($property->getName(), $data[$property->getName()]);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->getProperty($name);
    }

    /**
     * @param string $name
     * @param bool $toArray
     *
     * @return mixed
     *
     * @throws Exception\PropertyWasNotFound
     */
    public function getProperty(string $name, bool $toArray = false)
    {
        $data = $this->getData();

        foreach ($this->getModel()->getMeta()->getProperties() as $property) {

            if ($property->getName() == $name) {

                $value = isset($data[$name])?$data[$name]:null;
                return $this->_castDataType($property, $value, false, $toArray);
            }
        }

        throw new Exception\PropertyWasNotFound($this->getModel()->getMeta()->getCollection(), $name);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $arrayData = [];

        foreach ($this->getModel()->getMeta()->getProperties() as $property) {
            $arrayData[$property->getName()] = $this->getProperty($property->getName(), true);
        }

        return $arrayData;
    }

    /**
     * @param string $name
     * @param $value
     *
     * @throws Exception\PropertyWasNotFound
     */
    public function __set(string $name, $value)
    {
        $isSet = false;

        foreach ($this->getModel()->getMeta()->getProperties() as $property) {

            if ($property->getName() == $name) {

                $this->_data[$name] = $this->_castDataType($property, $value, true);
                $isSet = true;
            }
        }

        if (!$isSet) {
            throw new Exception\PropertyWasNotFound($this->getModel()->getMeta()->getCollection(), $name);
        }
    }

    /**
     * @param \MongoStar\Model\Meta\Property $property
     * @param $value
     * @param bool $isSet
     * @param bool $toArray
     *
     * @return array|bool|float|int|\MongoStar\Model|null|string
     * @throws Exception\PropertyHasDifferentType
     */
    private function _castDataType(\MongoStar\Model\Meta\Property $property, $value, bool $isSet = true, bool $toArray = false)
    {
        if (gettype($value) == 'object') {

            if (($value instanceof \stdClass)) {
                return (array)$value;
            }

            if (get_class($value) == $property->getType()) {

                if (is_subclass_of($value, '\\MongoStar\\Model')) {

                    /** @var \MongoStar\Model $value*/

                    if ($toArray) {
                        return $value->toArray();
                    }

                    if ($isSet) {
                        return $value->{$value->getMeta()->getPrimary()};
                    }

                    return $value;
                }
            }
        }

        else if (!$isSet && class_exists('\\' . $property->getType()) && is_subclass_of('\\' . $property->getType(), '\\MongoStar\\Model')) {

            $modelClassName = '\\' . $property->getType();

            if ($value) {
                /** @var \MongoStar\Model $modelClassName */
                $model = $modelClassName::fetchObject([
                    $this->getModel()->getMeta()->getPrimary() => $value
                ]);
            }
            else {
                $model = new $modelClassName();
            }

            if ($toArray) {
                return $model->toArray();
            }

            return $model;
        }

        else if (is_scalar($value)) {

            if ($property->getType() == 'string') {
                $value = strval($value);
            }

            else if ($property->getType() == 'float') {
                $value = floatval($value);
            }

            else if ($property->getType() == 'double') {
                $value = doubleval($value);
            }

            else if ($property->getType() == 'boolean' || $property->getType() == 'bool') {
                $value = boolval($value);
            }

            else if ($property->getType() == 'int' || $property->getType() == 'integer' ) {
                $value = intval($value);
            }

            return $value;
        }

        else if (gettype($value) == $property->getType()) {
            return $value;
        }

        else if (is_null($value)) {
            return null;
        }

        throw new Exception\PropertyHasDifferentType(
            $this->getModel()->getMeta()->getCollection(),
            $property->getName(),
            $property->getType(),
            gettype($value)
        );
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        foreach ($this->getModel()->getMeta()->getProperties() as $property) {

            if ($property->getName() == $offset) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $this->__set($offset, null);
    }
}