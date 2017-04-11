<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver\Flat;

/**
 * Class Driver
 * @package MongoStar\Model\Driver\Flat
 */
abstract class Driver extends \MongoStar\Model\Driver\DriverAbstract
{
    /**
     * Driver constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        throw new \Exception("NotImplemented: MongoStar\\Model\\Driver\\Flat\\Driver");
    }
}