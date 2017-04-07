<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver;

/**
 * Interface CursorInterface
 * @package MongoStar\Driver
 */
interface CursorInterface
{
    /**
     * @return array
     */
    public function toArray() : array;
}