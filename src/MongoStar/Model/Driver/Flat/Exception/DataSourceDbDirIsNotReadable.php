<?php

namespace MongoStar\Model\Driver\Flat\Exception;

/**
 * Class DataSourceDbDirIsNotReadable
 * @package MongoStar\Model\Driver\Flat\Exception
 */
class DataSourceDbDirIsNotReadable extends \Exception
{
    /**
     * DataSourceDbDirIsNotReadable constructor.
     *
     * @param string $dataSourceDir
     * @param array $userInfo
     */
    public function __construct(string $dataSourceDir, array $userInfo)
    {
        parent::__construct("DataSourceDbDirIsNotReadable: " . $dataSourceDir . ', user info is: ' . var_export($userInfo, true));
    }
}