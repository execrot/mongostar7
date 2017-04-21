<?php

namespace MongoStar\Model\Driver\Flat\Exception;

/**
 * Class DataSourceDirIsNotReadable
 * @package MongoStar\Model\Driver\Flat\Exception
 */
class DataSourceDirIsNotReadable extends \Exception
{
    /**
     * DataSourceDirIsNotReadable constructor.
     *
     * @param string $dataSourceDir
     * @param array $userInfo
     */
    public function __construct(string $dataSourceDir, array $userInfo)
    {
        parent::__construct("DataSourceDirIsNotReadable: " . $dataSourceDir . ', user info is: ' . var_export($userInfo, true));
    }
}