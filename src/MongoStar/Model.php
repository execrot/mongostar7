<?php

declare( strict_types = 1 );

namespace MongoStar;

/**
 * Class Model
 * @package MongoStar
 */
class Model implements Model\ModelInterface
{
    /**
     * @var Model\Meta
     */
    private $_meta = null;

    /**
     * @var Model\Driver\DocumentInterface
     */
    private $_document = null;

    /**
     * Model constructor.
     * @throws Model\Exception\ConfigWasNotProvided
     */
    public function __construct()
    {
        if (!Config::getConfig()) {
            throw new Model\Exception\ConfigWasNotProvided();
        }

        $this->_meta = new Model\Meta($this);
    }

    /**
     * @return Model\Meta
     */
    public function getMeta(): Model\Meta
    {
        return $this->_meta;
    }

    /**
     * @return Model\Driver\DocumentInterface
     */
    public function getDocument()
    {
        if (!$this->_document) {

            $driver = Config::getConfig()['driver'];
            $documentClassName = '\MongoStar\\Model\\Driver\\' . ucfirst($driver) . '\\Document';

            $this->_document = new $documentClassName($this);
        }
        return $this->_document;
    }

    /**
     * @return Model\Driver\DriverInterface
     */
    public static function getDriver() : Model\Driver\DriverInterface
    {
        $driver = Config::getConfig()['driver'];
        $driverClassName = '\MongoStar\\Model\\Driver\\' . ucfirst($driver);

        return new $driverClassName();
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        $this->getDocument()->__set($name, $value);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->getDocument()->__get($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->getDocument()->__isset($name);
    }

    /**
     * @param array $cond
     * @param array $sort
     * @param int|null $count
     * @param int|null $offset
     *
     * @return array|\MongoStar\Model
     */
    public static function fetchAll(array $cond = [], array $sort = [], int $count = null, int $offset = null)
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * @param array $cond
     * @param array $sort
     *
     * @return null|\MongoStar\Model
     */
    public static function fetchOne(array $cond = [], array $sort = [])
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * @param array $cond
     * @param array $sort
     * @param int|null $count
     * @param int|null $offset
     *
     * @return \MongoStar\Model
     */
    public static function fetchObject(array $cond = [], array $sort = [], int $count = null, int $offset = null)
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * @param array $cond
     * @return int
     */
    public static function count(array $cond = [])
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * @param array $data
     * @return int
     */
    public static function batchInsert(array $data = [])
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * @param array $cond
     * @param array $data
     *
     * @return int
     */
    public static function update(array $cond = [], array $data = [])
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * @return array
     */
    public function getData()
    {
        return self::__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param array $data
     * @return void
     */
    public function populate(array $data)
    {
        self::__call(__FUNCTION__, func_get_args());
    }

    /**
     * @return int
     */
    public function save()
    {
        return self::__call(__FUNCTION__, func_get_args());
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        if ($this->_document) {
            return $this->_document->toArray();
        }

        $props = [];

        foreach ($this->getMeta()->getProperties() as $property) {
            $props[$property->getName()] = null;
        }

        return $props;
    }

    /**
     * @param string $methodName
     * @param array $args
     *
     * @return mixed
     * @throws Model\Exception\CallUndefinedMethod
     */
    public function __call(string $methodName, array $args)
    {
        /**
         * Checking Document calls
         */
        $document = $this->getDocument();
        $method = [$document, $methodName];

        if (is_callable($method)) {
            return call_user_func_array($method, $args);
        }

        /**
         * Checking Driver calls
         */
        $driver = self::getDriver();
        $method = [$driver, $methodName];

        if (is_callable($method)) {
            $driver->setModel($this);
            return call_user_func_array($method, $args);
        }

        throw new Model\Exception\CallUndefinedMethod(static::class, $methodName);
    }

    /**
     * @param string $methodName
     * @param array $args
     *
     * @return mixed
     *
     * @throws Model\Exception\CallUndefinedMethod
     * @throws Model\Exception\ConfigWasNotProvided
     */
    public static function __callStatic(string $methodName, array $args)
    {
        $driver = self::getDriver();

        $method = [$driver, $methodName];

        if (is_callable($method)) {

            $driver->setModel(new static());
            return call_user_func_array($method, $args);
        }

        throw new Model\Exception\CallUndefinedMethod(static::class, $methodName);
    }
}