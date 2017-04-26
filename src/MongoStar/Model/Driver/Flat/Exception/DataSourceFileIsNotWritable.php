<?php

namespace MongoStar\Model\Driver\Flat\Exception;

/**
 * Class DataSourceFileIsNotWritable
 * @package MongoStar\Model\Driver\Flat\Exception
 */
class DataSourceFileIsNotWritable extends \Exception
{
    /**
     * DataSourceFileIsNotWritable constructor.
     *
     * @param string $dataSourceDir
     * @param array $userInfo
     */
    public function __construct(string $dataSourceDir, array $userInfo)
    {
        parent::__construct("DataSourceFileIsNotWritable: " . $dataSourceDir . ', user info is: ' . var_export($userInfo, true));
    }
}