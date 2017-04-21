<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver\Mongodb;

/**
 * Class Document
 * @package MongoStar\Model\Driver\Mongodb
 */
class Document extends \MongoStar\Model\Driver\DocumentAbstract
{
    /**
     * @return int
     */
    public function getTimestamp() : int
    {
        $primaryValue = $this->getModel()->{$this->getModel()->getMeta()->getPrimary()};

        if (!$primaryValue) {
            return 0;
        }

        $objectId = new \MongoDB\BSON\ObjectID($primaryValue);
        return $objectId->getTimestamp();
    }
}