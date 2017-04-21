<?php

declare(strict_types = 1);

namespace MongoStar\Model\Meta\Exception;

/**
 * Class CollectionCantBeWithoutPrimary
 * @package Model\Meta\Exception
 */
class CollectionCantBeWithoutPrimary extends \Exception
{
    /**
     * CollectionCantBeWithoutPrimary constructor.
     * @param \MongoStar\Model $model
     */
    public function __construct(\MongoStar\Model $model)
    {
        parent::__construct("CollectionCantBeWithoutPrimary: " . var_export($model, true));
    }
}