<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver\Mongodb;

/**
 * Class Document
 * @package MongoStar\Model\Driver\Mongodb
 */
class Document implements \MongoStar\Model\Driver\DocumentInterface
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
     * @return array
     */
    public function getData() : array
    {
        return $this->_data;
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
     * @param string $name
     *
     * @return mixed
     *
     * @throws \MongoStar\Model\Exception\PropertyWasNotFound
     */
    public function __get(string $name)
    {
        return $this->_getProperty($name);
    }

    /**
     * @param string $name
     * @param bool $toArray
     *
     * @return mixed|null
     *
     * @throws \MongoStar\Model\Exception\PropertyWasNotFound
     */
    private function _getProperty(string $name, bool $toArray = false)
    {
        foreach ($this->getModel()->getMeta()->getProperties() as $property) {

            if ($property->getName() == $name) {

                if (isset($this->_data[$name])) {

                    if (class_exists('\\' . $property->getType())) {

                        $modelClassName = '\\' . $property->getType();

                        $this->_data[$name] = $modelClassName::fetchObject(['id' => $this->_data[$name]]);

                        if ($toArray) {
                            $this->_data[$name] = $this->_data[$name]->toArray();
                        }
                    }

                    return $this->_data[$name];
                }

                if (class_exists('\\' . $property->getType())) {

                    $modelClassName = '\\' . $property->getType();

                    $this->_data[$name] = new $modelClassName();

                    if ($toArray) {
                        $this->_data[$name] = $this->_data[$name]->toArray();
                    }

                    return $this->_data[$name];
                }

                return null;
            }
        }

        throw new \MongoStar\Model\Exception\PropertyWasNotFound($this->getModel()->getMeta()->getCollection(), $name);
    }

    /**
     * @param string $name
     * @param $value
     *
     * @throws \MongoStar\Model\Exception\PropertyHasDifferentType
     * @throws \MongoStar\Model\Exception\PropertyWasNotFound
     */
    public function __set(string $name, $value)
    {
        foreach ($this->getModel()->getMeta()->getProperties() as $property) {

            if ($property->getName() == $name) {

                if (gettype($value) == 'object') {

                    if (($value instanceof \stdClass)) {
                        $this->_data[$name] = (array)$value;
                        return;
                    }

                    if (get_class($value) == $property->getType()) {
                        $this->_data[$name] = $value;
                        return;
                    }
                }

                else if (gettype($value) == $property->getType()) {
                    $this->_data[$name] = $value;
                    return;
                }

                else if ($value == null) {
                    $this->_data[$name] = null;
                    return;
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

                    $this->_data[$name] = $value;
                    return;
                }

                /**
                 * Checking another model relation
                 */
                if (class_exists('\\' . $property->getType())) {
                    $this->_data[$name] = $value;
                    return;
                }

                throw new \MongoStar\Model\Exception\PropertyHasDifferentType(
                    $this->getModel()->getMeta()->getCollection(),
                    $property->getName(),
                    $property->getType(),
                    gettype($value)
                );
            }
        }

        throw new \MongoStar\Model\Exception\PropertyWasNotFound($this->getModel()->getMeta()->getCollection(), $name);
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
     * @return array
     */
    public function toArray(): array
    {
        $arrayData = [];

        foreach ($this->getModel()->getMeta()->getProperties() as $property) {
            $arrayData[$property->getName()] = $this->_getProperty($property->getName(), true);
        }

        return $arrayData;
    }
}