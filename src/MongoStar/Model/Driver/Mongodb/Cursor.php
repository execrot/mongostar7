<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver\Mongodb;

/**
 * Class Cursor
 * @package MongoStar\Model\Driver\Mongodb
 */
class Cursor extends \MongoStar\Model\Driver\CursorAbstract
{
    /**
     * @param array $data
     * @return array
     */
    public function processDataRow(array $data) : array
    {
        if (isset($data['_id'])) {
            $data['id'] = $data['_id']['$oid'];
            unset($data['_id']);
        }

        return $data;
    }

}