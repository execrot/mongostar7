<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver\Flat;

/**
 * Class Cursor
 * @package MongoStar\Model\Driver\Flat
 */
abstract class Cursor extends \MongoStar\Model\Driver\CursorAbstract
{
    /**
     * Cursor constructor.
     * @param \MongoStar\Model $model
     * @param array $data
     * @throws \Exception
     */
    public function __construct(\MongoStar\Model $model, array $data)
    {
        parent::__construct($model, $data);

        throw new \Exception("NotImplemented: MongoStar\\Model\\Driver\\Flat\\Cursor");
    }
}