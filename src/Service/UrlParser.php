<?php

namespace Invertus\Brad\Service;

use Configuration;
use Context;
use Invertus\Brad\Config\Sort;
use Tools;

/**
 * Class UrlParser
 *
 * @package Invertus\Brad\Service
 */
class UrlParser
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var array After parsing url selected filters are stored here
     */
    private $selectedFilters = [];

    /**
     * @var string Selected filters query string
     */
    private $queryString = '';

    /**
     * UrlParser constructor.
     */
    public function __construct()
    {
        $this->context = Context::getContext();
    }

    /**
     * Parse url to get selected filters
     *
     * @param array $query
     */
    public function parse(array $query)
    {
        $extraParams = [];

        foreach ($query as $filterName => $filterValue) {
            if (!$this->checkIfFilter($filterName)) {
                if ($this->checkIfExtraParam($filterName)) {
                    $extraParams[$filterName] = $filterValue;
                }
                continue;
            }

            $this->addQueryStringParam($filterName, $filterValue);
            $values = explode('-', $filterValue);

            foreach ($values as $value) {
                $this->selectedFilters[$filterName][] = $this->parseValue($value);
            }
        }

        foreach ($extraParams as $name => $value) {
            $this->addQueryStringParam($name, $value);
        }
    }

    /**
     * @return array
     */
    public function getSelectedFilters()
    {
        return $this->selectedFilters;
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * Get selected page
     *
     * return int
     */
    public function getPage()
    {
        $page = (int) Tools::getValue('p', 1);

        if (0 >= $page) {
            $page = 1;
        }

        return $page;
    }

    /**
     * Get selected size
     *
     * @return int
     */
    public function getSize()
    {
        $size = (int) Tools::getValue('n');

        if (0 >= $size) {
            $size = isset($this->context->cookie->nb_item_per_page) ?
                (int) $this->context->cookie->nb_item_per_page :
                (int) Configuration::get('PS_PRODUCTS_PER_PAGE');
        }

        return $size;
    }

    /**
     * Get order by
     *
     * @return string
     */
    public function getOrderBy()
    {
        $orderBy = Tools::getValue('orderby');

        $availableOrderBy = [Sort::BY_NAME, Sort::BY_PRICE, Sort::BY_QUANTITY, Sort::BY_REFERENCE];

        if (!in_array($orderBy, $availableOrderBy)) {
            $orderBy = Sort::BY_RELEVANCE;
        }

        return $orderBy;
    }

    /**
     * Get order way
     *
     * @return string
     */
    public function getOrderWay()
    {
        $orderWay = Tools::getValue('orderway');

        $ways = [Sort::WAY_ASC, Sort::WAY_DESC];

        if (!in_array($orderWay, $ways)) {
            $orderWay = Sort::WAY_DESC;
        }

        return $orderWay;
    }

    /**
     * Get search query
     *
     * @return string
     */
    public function getSearchQuery()
    {
        $searchQuery = Tools::getValue('search_query', '');

        return $searchQuery;
    }

    /**
     * Get available filters
     *
     * @param string $filterName
     *
     * @return bool
     */
    protected function checkIfFilter($filterName)
    {
        $staticFilterNames = [
            'category',
            'price',
            'quantity',
            'manufacturer',
            'weight',
        ];

        if (in_array($filterName, $staticFilterNames) ||
            0 === strpos($filterName, 'feature_') ||
            0 === strpos($filterName, 'attribute_group_')
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $key
     * @param string $value
     */
    protected function addQueryStringParam($key, $value)
    {
        if (empty($this->queryString)) {
            $this->queryString = sprintf('%s=%s', $key, $value);
            return;
        }

        $this->queryString .= sprintf('&%s=%s', $key, $value);
    }

    /**
     * Check if it is extra param
     *
     * @param string $filterName
     *
     * @return bool
     */
    protected function checkIfExtraParam($filterName)
    {
        $extraParams = ['orderby', 'orderway', 'p', 'n'];

        return in_array($filterName, $extraParams);
    }

    /**
     * Parse given value
     *
     * @param $value
     *
     * @return string|array
     */
    protected function parseValue($value)
    {
        if (false !== strpos($value, ':')) {
            list($minValue, $maxValue) = explode(':', $value);

            return [
                'min_value' => $minValue,
                'max_value' => $maxValue,
            ];
        }

        return $value;
    }
}
