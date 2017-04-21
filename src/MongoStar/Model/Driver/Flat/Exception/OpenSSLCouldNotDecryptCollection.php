<?php

namespace MongoStar\Model\Driver\Flat\Exception;

/**
 * Class OpenSSLCouldNotDecryptCollection
 * @package MongoStar\Model\Driver\Flat\Exception
 */
class OpenSSLCouldNotDecryptCollection extends \Exception
{
    /**
     * OpenSSLCouldNotDecryptCollection constructor.
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        parent::__construct("OpenSSLCouldNotDecryptCollection: " . var_export($settings, true));
    }
}