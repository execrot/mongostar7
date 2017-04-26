<?php

namespace MongoStar\Model\Driver\Flat\Exception;

/**
 * Class DataSourceDirIsNotWritable
 * @package MongoStar\Model\Driver\Flat\Exception
 */
class DataSourceDirIsNotWritable extends \Exception
{
    /**
     * DataSourceDirIsNotWritable constructor.
     *
     * @param string $dataSourceDir
     * @param array $userInfo
     */
    public function __construct(string $dataSourceDir, array $userInfo)
    {
        parent::__construct("DataSourceDirIsNotWritable: " . $dataSourceDir . ', user info is: ' . var_export($userInfo, true));
    }
}