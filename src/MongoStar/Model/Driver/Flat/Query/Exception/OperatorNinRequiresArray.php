<?php

namespace MongoStar\Model\Driver\Flat\Query\Exception;

/**
 * Class OperatorNinRequiresArray
 * @package MongoStar\Model\Driver\Flat\Query\Exception
 */
class OperatorNinRequiresArray extends \Exception
{
    /**
     * OperatorNinRequiresArray constructor.
     * @param mixed $operatorValue
     */
    public function __construct($operatorValue)
    {
        parent::__construct("OperatorNinRequiresArray: " . $operatorValue);
    }
}