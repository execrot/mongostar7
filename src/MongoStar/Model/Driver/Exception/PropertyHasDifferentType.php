<?php

namespace MongoStar\Model\Driver\Exception;

/**
 * Class PropertyHasDifferentType
 * @package MongoStar\Model\Exception
 */
class PropertyHasDifferentType extends \Exception
{
    /**
     * PropertyHasDifferentType constructor.
     *
     * @param string $className
     * @param string $propertyName
     * @param string $validPropertyType
     * @param string $invalidPropertyType
     */
    public function __construct(string $className, string $propertyName, string $validPropertyType, string $invalidPropertyType)
    {
        parent::__construct("PropertyHasDifferentType: " . $className . '::' . $propertyName . ' trying to set value with type ' . $invalidPropertyType . ' instead of valid type ' . $validPropertyType);
    }
}