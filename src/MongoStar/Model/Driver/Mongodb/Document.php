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
                    $this->_data[$property->getName()] = $data[$property->getName()];
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

        throw new \MongoStar\Model\Exception\PropertyWasNotFound(static::class, $name);
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
        if (!$value) {
            return;
        }

        foreach ($this->getModel()->getMeta()->getProperties() as $property) {

            if ($property->getName() == $name) {

                if (gettype($value) == 'object') {

                    if (get_class($value) == $property->getType()) {
                        $this->_data[$name] = $value;
                        return;
                    }
                }

                else if (gettype($value) == $property->getType()) {
                    $this->_data[$name] = $value;
                    return;
                }

                /**
                 * Зайоб какойто, int у него блять integer
                 */
                else if (gettype($value) == 'integer' && $property->getType() == 'int') {
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

                throw new \MongoStar\Model\Exception\PropertyHasDifferentType(static::class, $property->getName(), $property->getType(), gettype($value));
            }
        }

        throw new \MongoStar\Model\Exception\PropertyWasNotFound(static::class, $name);
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