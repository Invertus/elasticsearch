<?php
/**
 * Copyright (c) 2016-2017 Invertus, JSC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

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
