<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver\Exception;

/**
 * Class UnsupportedCursorOperation
 */
class UnsupportedCursorOperation extends \Exception
{
    /**
     * UnsupportedCursorOperation constructor.
     * @param string|null $operation
     */
    public function __construct(string $operation = null)
    {
        parent::__construct("UnsupportedCursorOperation: " . $operation);
    }
}