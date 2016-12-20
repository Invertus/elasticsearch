<?php

namespace Invertus\Brad\Service;

use Configuration;
use Context;
use Invertus\Brad\DataType\FilterData;
use Invertus\Brad\Repository\FilterRepository;
use Invertus\Brad\Service\Elasticsearch\Builder\FilterQueryBuilder;
use Invertus\Brad\Service\Elasticsearch\ElasticsearchSearch;
use Module;

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
        /** @var \Brad $brad */
        $brad = Module::getInstanceByName('brad');
        /** @var \Core_Foundation_Database_EntityManager $em */
        $em = $brad->getContainer()->get('em');
        /** @var FilterRepository $filtersRepository */
        $filtersRepository = $em->getRepository('BradFilter');

        $filters = $filtersRepository->findAllByShopId($this->context->shop->id);
    }
}
