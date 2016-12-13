<?php

namespace Invertus\Brad\Service;

use Context;
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

    public function __construct(ElasticsearchSearch $elasticsearchSearch)
    {
        $this->elasticsearchSearch = $elasticsearchSearch;
        $this->searchQueryBuilder = new SearchQueryBuilder();
        $this->context = Context::getContext();
    }

    /**
     * Search for products
     *
     * @param string $searchQuery
     * @param int $page
     * @param int $size
     * @param string $orderBy
     * @param string $orderWay
     *
     * @return array
     */
    public function searchProducts($searchQuery, $page, $size, $orderBy, $orderWay)
    {
        $from = (int) ($size * ($page - 1));

        $productsQuery = $this->searchQueryBuilder->buildProductsQuery($searchQuery, $from, $size, $orderBy, $orderWay);

        $idShop = (int) $this->context->shop->id;
        $products = $this->elasticsearchSearch->searchProducts($productsQuery, $idShop);

        return $products;
    }

    /**
     * Count products by search query
     *
     * @param string $searchQuery
     *
     * @return int
     */
    public function countProducts($searchQuery)
    {
        $idShop = (int) $this->context->shop->id;

        $productsCountQuery = $this->searchQueryBuilder->buildProductsQuery($searchQuery);
        $productsCount = (int) $this->elasticsearchSearch->countProducts($productsCountQuery, $idShop);

        return $productsCount;
    }
}
