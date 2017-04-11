<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver\Flat;

/**
 * Class Document
 * @package MongoStar\Model\Driver\Flat
 */
abstract class Document extends \MongoStar\Model\Driver\DocumentAbstract
{
    /**
     * Document constructor.
     * @param \MongoStar\Model $model
     * @throws \Exception
     */
    public function __construct(\MongoStar\Model $model)
    {
        parent::__construct($model);
        throw new \Exception("NotImplemented: MongoStar\\Model\\Driver\\Flat\\Document");
    }
}