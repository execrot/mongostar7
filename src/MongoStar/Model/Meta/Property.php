<?php

declare(strict_types = 1);

namespace MongoStar\Model\Meta;

/**
 * Class Property
 * @package MongoStar\Model\Meta
 */
class Property
{
    /**
     * @var string
     */
    private $_type = null;

    /**
     * @var string
     */
    private $_name = null;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->_type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->_type = $type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->_name = $name;
    }
}