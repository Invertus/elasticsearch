<?php

namespace Invertus\Brad\Service;

use Context;
use Invertus\Brad\DataType\SearchData;
use Invertus\Brad\Service\Elasticsearch\Builder\SearchQueryBuilder;
use Invertus\Brad\Service\Elasticsearch\ElasticsearchSearch;

/**
 * Class SearchService
 *
 * @package Invertus\Brad\Service
 */
class SearchService
{
    /**
     * @var ElasticsearchSearch
     */
    private $elasticsearchSearch;

    /**
     * @var SearchQueryBuilder
     */
    private $searchQueryBuilder;

    /**
     * @var Context
     */
    private $context;

    /**
     * SearchService constructor.
     *
     * @param ElasticsearchSearch $elasticsearchSearch
     */
    public function __construct(ElasticsearchSearch $elasticsearchSearch)
    {
        $this->elasticsearchSearch = $elasticsearchSearch;
        $this->searchQueryBuilder = new SearchQueryBuilder();
        $this->context = Context::getContext();
    }

    /**
     * Search for products
     *
     * @param SearchData $searchData
     *
     * @return array
     */
    public function searchProducts(SearchData $searchData)
    {
        $productsQuery = $this->searchQueryBuilder->buildProductsQuery($searchData);

        $idShop = (int) $this->context->shop->id;
        $products = $this->elasticsearchSearch->searchProducts($productsQuery, $idShop);

        return $products;
    }

    /**
     * Count products by search query
     *
     * @param SearchData $searchData
     *
     * @return int
     */
    public function countProducts(SearchData $searchData)
    {
        $idShop = (int) $this->context->shop->id;
        $countQuery = true;

        $productsCountQuery = $this->searchQueryBuilder->buildProductsQuery($searchData, $countQuery);
        $productsCount = (int) $this->elasticsearchSearch->countProducts($productsCountQuery, $idShop);

        return $productsCount;
    }
}
