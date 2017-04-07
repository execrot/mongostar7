<?php

declare(strict_types = 1);

namespace MongoStar\Model\Meta\Exception;

/**
 * Class CollectionNameDoesNotExists
 * @package Model\Meta\Exception
 */
class CollectionNameDoesNotExists extends \Exception
{
    /**
     * CollectionNameDoesNotExists constructor.
     * @param \MongoStar\Model $model
     */
    public function __construct(\MongoStar\Model $model)
    {
        parent::__construct("CollectionNameDoesNotExists: " . var_export($model, true));
    }
}