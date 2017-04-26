<?php

namespace MongoStar\Model\Driver\Exception;

/**
 * Class PropertyWasNotFound
 * @package MongoStar\Model\Exception
 */
class PropertyWasNotFound extends \Exception
{
    /**
     * PropertyWasNotFound constructor.
     *
     * @param string $className
     * @param string $propertyName
     */
    public function __construct(string $className, string $propertyName)
    {
        parent::__construct("PropertyWasNotFound: " . $className . '::' . $propertyName);
    }
}