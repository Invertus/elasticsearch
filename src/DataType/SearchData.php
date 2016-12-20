<?php

namespace Invertus\Brad\DataType;

/**
 * Class SearchData
 *
 * @package Invertus\Brad\DataType
 */
class SearchData
{
    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $orderBy;

    /**
     * @var int
     */
    private $orderWay;

    /**
     * @var string
     */
    private $searchQuery;

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage($page)
    {
        $this->page = (int) $page;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = (int) $size;
    }

    /**
     * @return string
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @param string $orderBy
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @return int
     */
    public function getOrderWay()
    {
        return $this->orderWay;
    }

    /**
     * @param int $orderWay
     */
    public function setOrderWay($orderWay)
    {
        $this->orderWay = $orderWay;
    }

    /**
     * @return int
     */
    public function getFrom()
    {
        $from = (int) ($this->size * ($this->page - 1));

        return $from;
    }

    /**
     * @return string
     */
    public function getSearchQuery()
    {
        return $this->searchQuery;
    }

    /**
     * @param string $searchQuery
     */
    public function setSearchQuery($searchQuery)
    {
        $this->searchQuery = $searchQuery;
    }
}
