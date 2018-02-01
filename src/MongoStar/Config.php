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