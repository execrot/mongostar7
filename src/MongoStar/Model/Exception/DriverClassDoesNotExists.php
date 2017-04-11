<?php

namespace MongoStar\Model\Exception;

/**
 * Class DriverClassDoesNotExists
 * @package MongoStar\Model\Exception
 */
class DriverClassDoesNotExists extends \Exception
{
    /**
     * DriverClassDoesNotExists constructor.
     *
     * @param string $className
     */
    public function __construct(string $className)
    {
        parent::__construct("DriverClassDoesNotExists: " . $className);
    }
}