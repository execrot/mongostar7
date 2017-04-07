<?php

declare( strict_types = 1 );

namespace MongoStar\Model;

/**
 * Class Paginator
 * @package MongoStar\Model
 */
class Paginator implements \Zend_Paginator_Adapter_Interface
{
    /**
     * @var ModelInterface |null
     */
    private $_model = null;

    /**
     * @var array|null
     */
    private $_cond = [];

    /**
     * @var array|null
     */
    private $_sort = [];

    /**
     * If this parameter will be specify (by calling setDataMapper) data will be mapped
     *
     * @var \MongoStar\Map\MapInterface
     */
    private $_map = null;

    /**
     * Paginator constructor.
     * @param ModelInterface $model
     * @param array $cond
     * @param array $sort
     */
    public function __construct (ModelInterface $model, array $cond = [], array $sort = [])
    {
        $this->_model = $model;

        $this->_cond = $cond;
        $this->_sort = $sort;
    }

    /**
     * Return collection of selected items
     *
     * @param int $offset
     * @param int $limit
     * @return array|ModelInterface
     */
    public function getItems ($offset, $limit)
    {
        /** @var ModelInterface $modelClassName */
        $modelClassName = get_class($this->_model);

        $data = $modelClassName::fetchAll($this->_cond, $this->_sort, $limit, $offset);

        if ($this->_map) {
            $this->_map->setData($data);
            return $this->_map->toArray();
        }

        return $data;
    }

    /**
     * @return int
     */
    public function count () {

        /** @var ModelInterface $modelClassName */
        $modelClassName = get_class($this->_model);

        return $modelClassName::count($this->_cond);
    }

    /**
     * @param \MongoStar\Map\MapInterface $map
     */
    public function setMap(\MongoStar\Map\MapInterface $map)
    {
        $this->_map = $map;
    }
}