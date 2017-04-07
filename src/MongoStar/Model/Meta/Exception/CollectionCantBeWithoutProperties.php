<?php

declare(strict_types = 1);

namespace MongoStar\Model\Meta\Exception;

/**
 * Class CollectionNameDoesNotExists
 * @package Model\Meta\Exception
 */
class CollectionCantBeWithoutProperties extends \Exception
{
    /**
     * CollectionNameDoesNotExists constructor.
     * @param \MongoStar\Model $model
     */
    public function __construct(\MongoStar\Model $model)
    {
        parent::__construct("CollectionCantBeWithoutProperties: " . var_export($model, true));
    }
}