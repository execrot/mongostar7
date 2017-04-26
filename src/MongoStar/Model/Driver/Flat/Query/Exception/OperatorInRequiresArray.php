<?php

namespace MongoStar\Model\Driver\Flat\Query\Exception;

/**
 * Class OperatorInRequiresArray
 * @package MongoStar\Model\Driver\Flat\Query\Exception
 */
class OperatorInRequiresArray extends \Exception
{
    /**
     * OperatorInRequiresArray constructor.
     * @param mixed $operatorValue
     */
    public function __construct($operatorValue)
    {
        parent::__construct("OperatorInRequiresArray: " . $operatorValue);
    }
}