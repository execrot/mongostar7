<?php

declare(strict_types = 1);

namespace MongoStar\Map;

interface MapInterface
{
    /**
     * @return array|object|\MongoStar\Model
     */
    public function getData();

    /**
     * @param array|object|\MongoStar\Model $data
     */
    public function setData($data);

    /**
     * @return array|object|\MongoStar\Model
     */
    public function getRow();

    /**
     * @return array
     */
    public function getUserData() : array;

    /**
     * @param array $data
     */
    public function setUserData(array $data = []);

    /**
     * @return string
     */
    public function getContext() : string;

    /**
     * @param string $context
     */
    public function setContext(string $context);

    /**
     * @return array
     */
    public function common () : array;

    /**
     * @return array
     */
    public function toArray() : array;
}