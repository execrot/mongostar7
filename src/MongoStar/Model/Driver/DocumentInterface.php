<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver;

/**
 * Interface DocumentInterface
 * @package MongoStar\Driver
 */
interface DocumentInterface
{
    /**
     * @return \MongoStar\Model|null
     */
    public function getModel();

    /**
     * @return array
     */
    public function getData() : array;

    /**
     * Populate model with data
     *
     * @param array $data
     * @return bool
     */
    public function populate(array $data) : bool;

    /**
     * @return array
     */
    public function toArray() : array;
}