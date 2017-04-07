<?php

declare(strict_types = 1);

namespace MongoStar\Map\Exception;

/**
 * Class MapContextWasNotFound
 * @package MongoStar\Map\Exception
 */
class MapContextWasNotFound extends \Exception
{
    /**
     * MapContextWasNotFound constructor.
     *
     * @param string $mapClassName
     * @param string $context
     */
    public function __construct(string $mapClassName, string $context)
    {
        parent::__construct("MapContextWasNotFound: " . $mapClassName . '::' . $context);
    }
}