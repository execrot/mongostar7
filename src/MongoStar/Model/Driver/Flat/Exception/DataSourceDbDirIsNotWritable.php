<?php

namespace MongoStar\Model\Driver\Flat\Exception;

/**
 * Class DataSourceDbDirIsNotWritable
 * @package MongoStar\Model\Driver\Flat\Exception
 */
class DataSourceDbDirIsNotWritable extends \Exception
{
    /**
     * DataSourceDbDirIsNotWritable constructor.
     *
     * @param string $dataSourceDir
     * @param array $userInfo
     */
    public function __construct(string $dataSourceDir, array $userInfo)
    {
        parent::__construct("DataSourceDbDirIsNotWritable: " . $dataSourceDir . ', user info is: ' . var_export($userInfo, true));
    }
}