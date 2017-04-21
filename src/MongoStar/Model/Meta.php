<?php

declare( strict_types = 1 );

namespace MongoStar\Model;

/**
 * Class Meta
 * @package MongoStar\Model
 */
final class Meta
{
    const ID_COLLECTION = '@collection';
    const ID_PROPERTY   = '@property';
    const ID_PRIMARY   = '@primary';

    /**
     * @var array
     */
    private static $_cache = [];

    /**
     * @var string
     */
    private $_collection = null;

    /**
     * @var string
     */
    private $_primary = 'id';

    /**
     * @var Meta\Property[]
     */
    private $_properties = [];

    /**
     * @var Meta\Property[]
     */
    private $_assocProperties = [];

    /**
     * @var string
     */
    private $_modelClassName = null;

    /**
     * Meta constructor.
     * @param \MongoStar\Model $model
     */
    public function __construct(\MongoStar\Model $model)
    {
        $this->_modelClassName = get_class($model);

        if (empty(self::$_cache[$this->_modelClassName])) {
            $this->_parse($model);
        }
        else {
            $this->_collection = self::$_cache[$this->_modelClassName]['collection'];
            $this->_primary    = self::$_cache[$this->_modelClassName]['primary'];
            $this->_properties = self::$_cache[$this->_modelClassName]['properties'];
        }
    }

    /**
     * @return string
     */
    public function getCollection() : string
    {
        return $this->_collection;
    }

    /**
     * @param string $collection
     */
    public function setCollection(string $collection)
    {
        $this->_collection = $collection;
    }

    /**
     * @return string
     */
    public function getPrimary(): string
    {
        return $this->_primary;
    }

    /**
     * @param string $primary
     */
    public function setPrimary(string $primary)
    {
        $this->_primary = $primary;
    }

    /**
     * @return Meta\Property[]
     */
    public function getProperties() : array
    {
        return $this->_properties;
    }

    /**
     * @param array $fields
     */
    public function setProperties(array $fields)
    {
        $this->_properties = $fields;
    }

    /**
     * @param string $name
     *
     * @return Meta\Property
     * @throws Meta\Exception\PropertyWasNotFound
     */
    public function getPropertyWithName(string $name) : Meta\Property
    {
        if (isset($this->_assocProperties[$name])) {
            return $this->_assocProperties[$name];
        }

        throw new Meta\Exception\PropertyWasNotFound($this->getCollection(), $name);
    }

    /**
     * @param \MongoStar\Model $model
     *
     * @throws Meta\Exception\CollectionCantBeWithoutPrimary
     * @throws Meta\Exception\CollectionCantBeWithoutProperties
     * @throws Meta\Exception\CollectionNameDoesNotExists
     * @throws Meta\Exception\PropertyIsSetIncorrectly
     */
    private function _parse(\MongoStar\Model $model)
    {

        $reflection = new \ReflectionClass($model);

        $docblock = $reflection->getDocComment();

        $docblock = str_replace('*', '', $docblock);

        $docblock = array_filter(array_map(function($line) {

            $line = trim($line);

            if (strlen($line) > 0) {
                return $line;
            }
        }, explode("\n", $docblock)));

        $this->_properties = [];
        $this->_collection = null;

        foreach ($docblock as $line) {

            if (substr($line, 0, strlen(self::ID_COLLECTION)) == self::ID_COLLECTION) {
                $this->_collection  = ucfirst(strtolower(trim(str_replace(self::ID_COLLECTION, null, $line))));
                continue;
            }

            if (substr($line, 0, strlen(self::ID_PRIMARY)) == self::ID_PRIMARY) {
                $this->_primary  = trim(str_replace(self::ID_PRIMARY, null, $line));
                continue;
            }

            if (substr($line, 0, strlen(self::ID_PROPERTY)) == self::ID_PROPERTY) {

                $propertyLine = array_values(array_filter(explode(' ', $line)));

                $property = new Meta\Property();

                if (count($propertyLine) < 3) {
                    throw new Meta\Exception\PropertyIsSetIncorrectly($model, $line);
                }

                $property->setType($propertyLine[1]);
                $property->setName(str_replace('$', null, $propertyLine[2]));

                $this->_assocProperties[$property->getName()] = $property;
                $this->_properties[] = $property;
            }
        }

        if (!strlen($this->_collection)) {
            throw new Meta\Exception\CollectionNameDoesNotExists($model);
        }

        if (!strlen($this->_primary)) {
            throw new Meta\Exception\CollectionCantBeWithoutPrimary($model);
        }

        if (!count($this->_properties)) {
            throw new Meta\Exception\CollectionCantBeWithoutProperties($model);
        }

        self::$_cache[$this->_modelClassName] = [
            'collection' => $this->_collection,
            'properties' => $this->_properties,
            'primary'    => $this->_primary
        ];
    }
}