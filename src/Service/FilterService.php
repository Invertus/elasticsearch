<?php

namespace Invertus\Brad\Service;

use Context;
use Invertus\Brad\Service\Elasticsearch\Builder\FilterQueryBuilder;
use Invertus\Brad\Service\Elasticsearch\ElasticsearchSearch;

/**
 * Class Filter
 *
 * @package Invertus\Brad\Service
 */
class FilterService
{
    /**
     * @var FilterQueryBuilder
     */
    private $filterQueryBuilder;

    /**
     * @var ElasticsearchSearch
     */
    private $elasticsearchSearch;

    /**
     * @var Context
     */
    private $context;

    /**
     * Filter constructor.
     *
     * @param FilterQueryBuilder $filterQueryBuilder
     * @param ElasticsearchSearch $elasticsearchSearch
     */
    public function __construct(FilterQueryBuilder $filterQueryBuilder, ElasticsearchSearch $elasticsearchSearch)
    {
        $this->filterQueryBuilder = $filterQueryBuilder;
        $this->elasticsearchSearch = $elasticsearchSearch;
        $this->context = Context::getContext();
    }

    /**
     * Perform filtering
     *
     * @param array $selectedFilters
     *
     * @param $page
     * @param $size
     * @param $orderBy
     * @param $orderWay
     *
     * @return array
     */
    public function filterProducts(array $selectedFilters, $page, $size, $orderBy, $orderWay)
    {
        if (empty($selectedFilters)) {
            return [];
        }

        $from = (int) ($size * ($page - 1));

        $data = [];
        $data['selected_filters'] = $selectedFilters;
        $data['from'] = $from;
        $data['size'] = $size;
        $data['order_by'] = $orderBy;
        $data['order_way'] = $orderWay;

        $productsFilterQuery = $this->filterQueryBuilder->buildFilterQuery($data);

        $idShop  = (int) $this->context->shop->id;
        $products = $this->elasticsearchSearch->searchProducts($productsFilterQuery, $idShop);

        return $products;
    }

    /**
     * Count products by filters
     *
     * @param array $selectedFilters
     *
     * @return int
     */
    public function countProducts(array $selectedFilters)
    {
        $data = [];
        $data['selected_filters'] = $selectedFilters;

        $count = true;
        $productsFilterQuery = $this->filterQueryBuilder->buildFilterQuery($data, $count);

        $productsCount = $this->elasticsearchSearch->countProducts($productsFilterQuery, $this->context->shop->id);

        return $productsCount;
    }
}
