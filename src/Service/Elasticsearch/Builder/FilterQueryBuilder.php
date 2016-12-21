<?php

namespace Invertus\Brad\Service\Elasticsearch\Builder;

use Category;
use Configuration;
use Context;
use Invertus\Brad\Converter\NameConverter;
use Invertus\Brad\DataType\FilterData;
use Invertus\Brad\Repository\CategoryRepository;
use Module;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\FilterAggregation;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\BuilderInterface;
use ONGR\ElasticsearchDSL\Query\BoolQuery;
use ONGR\ElasticsearchDSL\Query\RangeQuery;
use ONGR\ElasticsearchDSL\Query\TermQuery;
use ONGR\ElasticsearchDSL\Search;

/**
 * Class FilterQueryBuilder
 *
 * @package Invertus\Brad\Service\Elasticsearch\Builder
 */
class FilterQueryBuilder extends AbstractQueryBuilder
{
    /**
     * Build filters query by given data
     *
     * @param FilterData $filterData
     * @param bool $countQuery
     *
     * @return array
     */
    public function buildFilterQuery(FilterData $filterData, $countQuery = false)
    {
        $query = $this->getProductQueryBySelectedFilters(
            $filterData->getSelectedFilters(),
            $filterData->getIdCategory()
        );

        if ($countQuery) {
            return $query->toArray();
        }

        $orderBy  = $filterData->getOrderBy();
        $orderWay = $filterData->getOrderWay();
        $sort     = $this->buildOrderQuery($orderBy, $orderWay);

        $query->addSort($sort);
        $query->setFrom($filterData->getFrom());
        $query->setSize($filterData->getSize());

        return $query->toArray();
    }

    /**
     * Build aggregations query
     *
     * @param FilterData $filterData
     * @param array $filters
     *
     * @return array
     */
    public function buildAggregationsQuery(FilterData $filterData, array $filters)
    {
        $aggregationsQuery = new Search();

        foreach ($filters as $filter) {

            $fieldName = NameConverter::getElasticsearchFieldName($filter['input_name']);

            $termsAggregation = new TermsAggregation('field', $fieldName);
            $termFilter = new TermQuery('attribute_group_3', 8);
            $filterAggregation = new FilterAggregation($fieldName, $termFilter);
            $filterAggregation->addAggregation($termsAggregation);

            $aggregationsQuery->addAggregation($filterAggregation);
        }
        //d($aggregationsQuery->toArray());
        return $aggregationsQuery->toArray();
    }

    /**
     * Get search values by selected filters
     *
     * @param array $selectedFilters
     * @param int $idCategory
     *
     * @return Search
     */
    protected function getProductQueryBySelectedFilters(array $selectedFilters, $idCategory)
    {
        $searchQuery   = new Search();
        $boolMustFilterQuery = new BoolQuery();

        $includeCategoriesIntoQuery = true;

        foreach ($selectedFilters as $name => $values) {
            if (0 === strpos($name, 'feature') || 0 === strpos($name, 'attribute_group')) {
                $boolShouldTermQuery = $this->getBoolShouldTermQuery($name, $values);
                $boolMustFilterQuery->add($boolShouldTermQuery);
            } elseif ('price' == $name) {
                $boolShouldRangeQuery = $this->getBoolShouldRangeQuery($name, $values);
                $boolMustFilterQuery->add($boolShouldRangeQuery);
            } elseif ('manufacturer' == $name) {
                $boolShouldTermQuery = $this->getBoolShouldTermQuery($name, $values);
                $boolMustFilterQuery->add($boolShouldTermQuery);
            } elseif ('weight' == $name) {
                $boolShouldTermQuery = $this->getBoolShouldRangeQuery($name, $values);
                $boolMustFilterQuery->add($boolShouldTermQuery);
            } elseif ('quantity' == $name) {
                $boolShouldTermQuery = $this->getBoolShouldTermQuery($name, $values);
                $boolMustFilterQuery->add($boolShouldTermQuery);
            } elseif ('category' == $name) {
                $boolShouldTermQuery = $this->getBoolShouldTermQuery($name, $values);
                $boolMustFilterQuery->add($boolShouldTermQuery);

                if (!empty($searchValues['categories'])) {
                    $includeCategoriesIntoQuery = false;
                }
            }
        }

        $boolShouldCategoriesQuery = $this->getQueryFromCategories($idCategory);
        $boolMustCategoriesQuery   = new BoolQuery();

        if ($boolShouldCategoriesQuery instanceof BuilderInterface) {
            $boolMustCategoriesQuery->add($boolShouldCategoriesQuery);
        }

        if (!empty($boolMustFilterQuery->getQueries())) {
            $searchQuery->addQuery($boolMustFilterQuery);
        }

        if (!$includeCategoriesIntoQuery) {
            $searchQuery->addQuery($boolMustCategoriesQuery, BoolQuery::MUST);
        }

        return $searchQuery;
    }

    /**
     * Get subcategories query
     *
     * @param int $idCategory
     *
     * @return BoolQuery|null
     */
    protected function getQueryFromCategories($idCategory)
    {
        $context = Context::getContext();
        $idLang = $context->language->id;
        $idShop = $context->shop->id;

        /** @var \Brad $brad */
        $brad = Module::getInstanceByName('brad');
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $brad->getContainer()->get('em')->getRepository('BradCategory');

        $category = new Category($idCategory);

        $subCategories = $categoryRepository->findChildCategoriesNamesAndIds($category, $idLang, $idShop);

        if (empty($subCategories)) {
            return null;
        }

        $values = array_map(function($subCategory) {
            return (int) $subCategory['id_category'];
        }, $subCategories);

        $fieldName = 'categories';
        $boolShouldTermQuery = $this->getBoolShouldTermQuery($fieldName, $values);

        return $boolShouldTermQuery;
    }

    /**
     * Get bool should query with terms query inside
     *
     * @param string $filterName
     * @param array $values
     *
     * @return BoolQuery
     */
    protected function getBoolShouldTermQuery($filterName, array $values)
    {
        $fieldName = NameConverter::getElasticsearchFieldName($filterName);

        $boolShouldQuery = new BoolQuery();

        foreach ($values as $value) {
            $termQuery = new TermQuery($fieldName, $value);
            $boolShouldQuery->add($termQuery, BoolQuery::SHOULD);
        }

        return $boolShouldQuery;
    }

    /**
     * Get bool should query with ranges inside
     *
     * @param string $filterName
     * @param array $values
     *
     * @return BoolQuery
     */
    protected function getBoolShouldRangeQuery($filterName, array $values)
    {
        $fieldName = $fieldName = NameConverter::getElasticsearchFieldName($filterName);

        $boolShouldQuery = new BoolQuery();

        foreach ($values as $value) {
            if (empty($value)) {
                continue;
            }

            $params = [
                'gt'  => $value['min_value'],
                'lte' => $value['max_value'],
            ];

            $rangeQuery = new RangeQuery($fieldName, $params);
            $boolShouldQuery->add($rangeQuery, BoolQuery::SHOULD);
        }

        return $boolShouldQuery;
    }

    /**
     * Get field name in elasticsearch by filter name
     *
     * @param string $filterName
     *
     * @return string
     */
    protected function getFieldName($filterName)
    {
        $context = Context::getContext();
        $fieldName = '';

        if ('quantity' == $filterName) {
            $orderOutOfStock = (bool) Configuration::get('PS_ORDER_OUT_OF_STOCK');
            switch ($orderOutOfStock) {
                case true:
                    $fieldName = 'in_stock_when_global_oos_allow_orders';
                    break;
                case false:
                    $fieldName = 'in_stock_when_global_oos_deny_orders';
                    break;
            }
        } elseif ('price' == $filterName) {
            $idGroup    = $context->customer->id_default_group;
            $idCurrency = $context->currency->id;
            $idCountry  = $context->country->id;
            $fieldName  = sprintf('price_group_%s_country_%s_currency_%s', $idGroup, $idCountry, $idCurrency);
        } elseif ('manufacturer' == $filterName) {
            $fieldName = 'id_manufacturer';
        } elseif ('weight' == $filterName ||
            0 === strpos($filterName, 'feature') ||
            0 === strpos($filterName, 'attribute_group')
        ) {
            $fieldName = $filterName;
        } elseif ('category' == $filterName) {
            $fieldName = 'categories';
        }

        return $fieldName;
    }
}
