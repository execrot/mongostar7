<?php

namespace MongoStar\Model\Driver\Flat\Query\Exception;

/**
 * Class OperatorAllRequiresArray
 * @package MongoStar\Model\Driver\Flat\Query\Exception
 */
class OperatorAllRequiresArray extends \Exception
{
    /**
     * OperatorAllRequiresArray constructor.
     * @param mixed $operatorValue
     */
    public function __construct($operatorValue)
    {
        parent::__construct("OperatorAllRequiresArray: " . $operatorValue);
    }
}