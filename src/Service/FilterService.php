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

use Configuration;
use Context;
use Invertus\Brad\Config\Setting;
use Invertus\Brad\Converter\NameConverter;
use Invertus\Brad\DataType\FilterData;
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
     * @param FilterData $filterData
     *
     * @return array
     */
    public function filterProducts(FilterData $filterData)
    {
        $productsFilterQuery = $this->filterQueryBuilder->buildFilterQuery($filterData);

        $idShop  = (int) $this->context->shop->id;
        $products = $this->elasticsearchSearch->searchProducts($productsFilterQuery, $idShop);

        return $products;
    }

    /**
     * Count products by filters
     *
     * @param FilterData $filterData
     *
     * @return int
     */
    public function countProducts(FilterData $filterData)
    {
        $countQuery = true;
        $productsFilterQuery = $this->filterQueryBuilder->buildFilterQuery($filterData, $countQuery);

        $productsCount = $this->elasticsearchSearch->countProducts($productsFilterQuery, $this->context->shop->id);

        return $productsCount;
    }

    /**
     * Get products aggregations
     *
     * @param FilterData $filterData
     *
     * @return array
     */
    public function aggregateProducts(FilterData $filterData)
    {
        $aggregateProducts = (bool) Configuration::get(Setting::DISPLAY_NUMBER_OF_MATCHING_PRODUCTS);
        if (!$aggregateProducts) {
            return [];
        }

        $aggregationsQuery = $this->filterQueryBuilder->buildAggregationsQuery($filterData);

        if (empty($aggregationsQuery)) {
            return [];
        }

        $idShop = $this->context->shop->id;
        $aggregations = $this->elasticsearchSearch->searchProducts($aggregationsQuery, $idShop, true);

        $productsAggregations = [];
        foreach ($aggregations as $fieldName => $aggregation) {
            $inputName = NameConverter::getInputNameFromElasticsearchFieldName($fieldName);

            if (empty($inputName)) {
                continue;
            }

            $productsAggregations[$inputName]['total_count'] = 0;

            if (empty($aggregation[$fieldName]['buckets'])) {
                continue;
            }

            foreach ($aggregation[$fieldName]['buckets'] as $name => $bucket) {
                $inputValue = isset($bucket['key']) ? $bucket['key'] : $name;
                $docCount = (int) $bucket['doc_count'];
                $productsAggregations[$inputName][$inputValue] = $docCount;
                $productsAggregations[$inputName]['total_count'] += $docCount;
            }
        }

        return $productsAggregations;
    }
}
