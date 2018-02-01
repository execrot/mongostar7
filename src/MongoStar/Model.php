<?php

declare( strict_types = 1 );

namespace MongoStar;

/**
 * Class Model
 * @package MongoStar
 */
class Model implements Model\ModelInterface, \ArrayAccess
{
    /**
     * @var Model\Meta
     */
    private $_meta = null;

    /**
     * @var Model\Driver\DocumentAbstract
     */
    private $_document = null;

    /**
     * @var Model\Driver\DocumentAbstract
     */
    private static $_driverClassName = null;

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
     * @return Model\Driver\DocumentAbstract
     */
    public function getDocument() : Model\Driver\DocumentAbstract
    {
        if (!$this->_document) {

            $driver = Config::getConfig()['driver'];
            $documentClassName = '\MongoStar\\Model\\Driver\\' . ucfirst($driver) . '\\Document';

            $this->_document = new $documentClassName($this);
        }
        return $this->_document;
    }

    /**
     * @return Model\Driver\DriverAbstract
     *
     * @throws Model\Exception\ConfigWasNotProvided
     * @throws Model\Exception\DriverClassDoesNotExists
     * @throws Model\Exception\DriverClassDoesNotExtendsFromDriverAbstract
     */
    public static function getDriver() : Model\Driver\DriverAbstract
    {
        if (!Config::getConfig()) {
            throw new Model\Exception\ConfigWasNotProvided();
        }

        if (!empty(self::$_driverClassName)) {
            $driverClassName = self::$_driverClassName;
        }
        else {
            $driver = Config::getConfig()['driver'];
            $driverClassName = '\MongoStar\\Model\\Driver\\' . ucfirst($driver) . '\\Driver';
        }

        if (!class_exists($driverClassName)) {
            throw new Model\Exception\DriverClassDoesNotExists($driverClassName);
        }

        if (!is_subclass_of($driverClassName, '\\MongoStar\\Model\\Driver\\DriverAbstract')) {
            throw new Model\Exception\DriverClassDoesNotExtendsFromDriverAbstract($driverClassName);
        }

        return new $driverClassName(\MongoStar\Config::getConfig());
    }

    /**
     * @param Model\Driver\DriverAbstract $class
     */
    public static function setDriver(Model\Driver\DriverAbstract $class)
    {
        self::$_driverClassName = get_class($class);
    }

    /**
     * @return string
     */
    public function getModelClassName() : string
    {
        return get_class($this);
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
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->getDocument()->offsetExists($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getDocument()->offsetGet($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->getDocument()->offsetSet($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $this->getDocument()->offsetUnset($offset);
    }

    /**
     * @param array|string|null $cond
     * @param array|string|null $sort
     *
     * @param int|null $count
     * @param int|null $offset
     *
     * @return array|\MongoStar\Model
     */
    public static function fetchAll($cond = null, $sort = null, int $count = null, int $offset = null)
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * @param array|string|null $cond
     * @param array|string|null $sort
     *
     * @return null|\MongoStar\Model
     */
    public static function fetchOne($cond = null, $sort = null)
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * @param array|string|null $cond
     * @param array|string|null $sort
     *
     * @return \MongoStar\Model
     */
    public static function fetchObject($cond = null, $sort = null)
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * @param array|string|null $cond
     * @return int
     */
    public static function count($cond = null)
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
     * @param array|string|null $cond
     * @param array $data
     *
     * @return int
     */
    public static function update($cond = null, array $data = [])
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
     * @return int
     */
    public function getTimestamp() : int
    {
        return self::__call(__FUNCTION__, func_get_args());
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return $this->getDocument()->toArray();
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

        throw new Model\Exception\CallUndefinedMethod($this->getMeta()->getCollection(), $methodName);
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