<?php

declare(strict_types = 1);

namespace MongoStar\Model\Meta\Exception;

/**
 * Class PropertyWasNotFound
 * @package Meta\Exception
 */
class PropertyWasNotFound extends \Exception
{
    /**
     * PropertyWasNotFound constructor.
     *
     * @param string $collection
     * @param string $property
     */
    public function __construct(string $collection, string $property)
    {
        parent::__construct("PropertyWasNotFound: collection: '" . $collection. "', property: " . $property);
    }
}