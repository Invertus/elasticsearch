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
class Filter
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
     * Filter constructor.
     *
     * @param FilterQueryBuilder $filterQueryBuilder
     * @param ElasticsearchSearch $elasticsearchSearch
     */
    public function __construct(FilterQueryBuilder $filterQueryBuilder, ElasticsearchSearch $elasticsearchSearch)
    {
        $this->filterQueryBuilder = $filterQueryBuilder;
        $this->elasticsearchSearch = $elasticsearchSearch;
    }

    /**
     * Perform filtering
     *
     * @param array $selectedFilters
     * @param bool $countOnly
     *
     * @return array
     */
    public function filterProducts(array $selectedFilters, $countOnly = false)
    {
        $context = Context::getContext();
        $data = [];
        $data['selected_filters'] = $selectedFilters;

        $productsFilterQuery = $this->filterQueryBuilder->buildFilterQuery($data);

        $products = $this->elasticsearchSearch->searchProducts($productsFilterQuery, $context->shop->id);

        return $products;
    }
}
