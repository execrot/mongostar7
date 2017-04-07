<?php

declare(strict_types = 1);

namespace MongoStar\Model\Meta\Exception;

/**
 * Class PropertyIsSetIncorrectly
 * @package Meta\Exception
 */
class PropertyIsSetIncorrectly extends \Exception
{
    /**
     * PropertyIsSetIncorrectly constructor.
     * @param \MongoStar\Model $model
     * @param string $line
     */
    public function __construct(\MongoStar\Model $model, string $line)
    {
        parent::__construct("PropertyIsSetIncorrectly: line: '" . $line . "', model: " . var_export($model, true));
    }
}