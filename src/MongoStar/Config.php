<?php

declare( strict_types = 1 );

namespace MongoStar;

/**
 * Class Config
 * @package MongoStar
 */
class Config
{
    /**
     * Format: [
     *      'driver'   => 'mongodb',
     *      'server'   => 'localhost:27017',
     *      'db'       => 'dbname'
     *      'username' => 'username'
     *      'password' => 'password'
     * ]
     *
     * @var array
     */
    private static $_config = null;

    /**
     * @param array $config
     */
    public static function setConfig(array $config)
    {
        self::$_config = $config;
    }

    /**
     * @return array
     */
    public static function getConfig()
    {
        return self::$_config;
    }
}