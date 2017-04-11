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
     * @var array
     */
    private $_properties = [];

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
     * @param array $data
     * @return bool
     */
    public function isValid(array $data = []) : bool
    {
        foreach ($data as $name => $value) {

            foreach ($this->getProperties() as $property) {

                if ($property->getName() == $name) {

                    if (gettype($value) == 'object') {

                        if (get_class($value) != $property->getType()) {
                            return false;
                        }
                    }
                    else if (gettype($value) != $property->getType()) {
                        return false;
                    }

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param \MongoStar\Model $model
     *
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

                $this->_properties[] = $property;
            }
        }

        if (!strlen($this->_collection)) {
            throw new Meta\Exception\CollectionNameDoesNotExists($model);
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