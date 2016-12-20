<?php

namespace Invertus\Brad\Service\Elasticsearch\Builder;

use Category;
use Context;
use Invertus\Brad\DataType\FilterData;
use Invertus\Brad\Repository\CategoryRepository;
use Module;
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
     * @param array $data
     */
    public function buildAggregationsQuery(array $data)
    {

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
        $context       = Context::getContext();
        $searchQuery   = new Search();
        $boolMustQuery = new BoolQuery();

        $includeCategoriesIntoQuery = true;

        foreach ($selectedFilters as $name => $values) {
            if (0 === strpos($name, 'feature') || 0 === strpos($name, 'attribute_group')) {
                $boolShouldTermQuery = $this->getBoolShouldTermQuery($name, $values);
                $boolMustQuery->add($boolShouldTermQuery);
            } elseif ('price' == $name) {
                $idGroup    = $context->customer->id_default_group;
                $idCurrency = $context->currency->id;
                $idCountry  = $context->country->id;
                $fieldName  = sprintf('price_group_%s_country_%s_currency_%s', $idGroup, $idCountry, $idCurrency);
                $boolShouldRangeQuery = $this->getBoolShouldRangeQuery($fieldName, $values);
                $boolMustQuery->add($boolShouldRangeQuery);
            } elseif ('manufacturer' == $name) {
                $fieldName = 'id_manufacturer';
                $boolShouldTermQuery = $this->getBoolShouldTermQuery($fieldName, $values);
                $boolMustQuery->add($boolShouldTermQuery);
            } elseif ('weight' == $name) {
                $boolShouldTermQuery = $this->getBoolShouldTermQuery($name, $values);
                $boolMustQuery->add($boolShouldTermQuery);
            } elseif ('quantity' == $name) {
                //@todo: index new field with stock
            } elseif ('category' == $name) {
                $fieldName = 'categories';
                $boolShouldTermQuery = $this->getBoolShouldTermQuery($fieldName, $values);
                $boolMustQuery->add($boolShouldTermQuery);

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

        if (!empty($boolMustQuery->getQueries())) {
            $searchQuery->addQuery($boolMustQuery);
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
        /** @var \Core_Foundation_Database_EntityManager $em */
        $em = $brad->getContainer()->get('em');
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $em->getRepository('BradCategory');

        $category = new Category($idCategory);

        $subCategories = $categoryRepository->findChildCategories($category, $idLang, $idShop);

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
     * @param string $fieldName
     * @param array $values
     *
     * @return BoolQuery
     */
    protected function getBoolShouldTermQuery($fieldName, array $values)
    {
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
     * @param string $fieldName
     * @param array $values
     *
     * @return BoolQuery
     */
    protected function getBoolShouldRangeQuery($fieldName, array $values)
    {
        $boolShouldQuery = new BoolQuery();

        foreach ($values as $value) {
            $params = [
                'gt'  => $value['min_value'],
                'lte' => $value['max_value'],
            ];

            $rangeQuery = new RangeQuery($fieldName, $params);
            $boolShouldQuery->add($rangeQuery, BoolQuery::SHOULD);
        }

        return $boolShouldQuery;
    }
}
