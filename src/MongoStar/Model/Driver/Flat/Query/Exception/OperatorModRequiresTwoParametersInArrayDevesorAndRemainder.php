<?php

namespace MongoStar\Model\Driver\Flat\Query\Exception;

/**
 * Class OperatorModRequiresTwoParametersInArrayDevesorAndRemainder
 * @package MongoStar\Model\Driver\Flat\Query\Exception
 */
class OperatorModRequiresTwoParametersInArrayDevesorAndRemainder extends \Exception
{
    /**
     * OperatorModRequiresTwoParametersInArrayDevesorAndRemainder constructor.
     */
    public function __construct()
    {
        parent::__construct("OperatorModRequiresTwoParametersInArrayDevesorAndRemainder: ");
    }
}