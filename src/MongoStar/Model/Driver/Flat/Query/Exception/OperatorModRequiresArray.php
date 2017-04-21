<?php

namespace MongoStar\Model\Driver\Flat\Query\Exception;

/**
 * Class OperatorModRequiresArray
 * @package MongoStar\Model\Driver\Flat\Query\Exception
 */
class OperatorModRequiresArray extends \Exception
{
    /**
     * OperatorModRequiresArray constructor.
     * @param mixed $operatorValue
     */
    public function __construct($operatorValue)
    {
        parent::__construct("OperatorModRequiresArray: " . $operatorValue);
    }
}