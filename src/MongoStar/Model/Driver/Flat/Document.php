<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver\Flat;

/**
 * Class Document
 * @package MongoStar\Model\Driver\Flat
 */
class Document extends \MongoStar\Model\Driver\DocumentAbstract
{
    public function getTimestamp(): int
    {
        $primaryValue = $this->getModel()->{$this->getModel()->getMeta()->getPrimary()};

        if (!$primaryValue) {
            return 0;
        }

        return intval(substr($primaryValue, Driver::ID_UNIQUE_LENGTH));
    }
}