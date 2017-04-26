<?php

namespace MongoStar\Model\Driver\Flat\Exception;

/**
 * Class OpenSSLFunctionDoesNotExists
 * @package MongoStar\Model\Driver\Flat\Exception
 */
class OpenSSLFunctionDoesNotExists extends \Exception
{
    /**
     * OpenSSLFunctionDoesNotExists constructor.
     *
     * @param string $opensslFunction
     */
    public function __construct(string $opensslFunction)
    {
        parent::__construct("OpenSSLFunctionDoesNotExists: " . $opensslFunction);
    }
}