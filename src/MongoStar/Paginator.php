<?php

declare( strict_types = 1 );

namespace MongoStar;

/**
 * Class Paginator
 * @package MongoStar
 */
class Paginator implements \Zend_Paginator_Adapter_Interface
{
    /**
     * @var Model |null
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
     * @var Map
     */
    private $_map = null;

    /**
     * Paginator constructor.
     * @param Model $model
     * @param array|string|null $cond
     * @param array|string|null $sort
     */
    public function __construct (Model $model, $cond = null, $sort = null)
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
     * @return array|Model
     */
    public function getItems ($offset, $limit)
    {
        /** @var Model $modelClassName */
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

        /** @var Model $modelClassName */
        $modelClassName = get_class($this->_model);

        return $modelClassName::count($this->_cond);
    }

    /**
     * @param Map $map
     */
    public function setMap(Map $map)
    {
        $this->_map = $map;
    }
}