<?php

declare(strict_types=1);

namespace MongoStar\Model\Exception;

/**
 * Class CallUndefinedMethod
 * @package MongoStar\Model\Exception
 */
class ConfigWasNotProvided extends \Exception
{
    /**
     * CallUndefinedMethod constructor.
     */
    public function __construct()
    {
        parent::__construct("ConfigWasNotProvided");
    }
}