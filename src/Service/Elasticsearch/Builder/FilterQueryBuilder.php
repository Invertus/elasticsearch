<?php

namespace Invertus\Brad\Service\Elasticsearch\Builder;

use Category;
use Context;
use Invertus\Brad\Repository\CategoryRepository;
use Module;

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
     * @param array $data
     * @param $countOnly
     *
     * @return array
     */
    public function buildFilterQuery(array $data, $countOnly = false)
    {
        $query = $this->getProductQueryBySelectedFilters($data['selected_filters'], $data['id_category']);

        if ($countOnly) {
            return $query;
        }

        if (isset($data['order_by']) && isset($data['order_way'])) {
            $query['sort'] = $this->buildOrderQuery($data['order_by'], $data['order_way']);
        }

        if (isset($data['from'])) {
            $query['from'] = (int) $data['from'];
        }

        if (isset($data['size'])) {
            $query['size'] = (int) $data['size'];
        }

        return $query;
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
     * @return array
     */
    protected function getProductQueryBySelectedFilters(array $selectedFilters, $idCategory)
    {
        $searchValues = [];

        foreach ($selectedFilters as $filterName => $filterValues) {
            if (0 === strpos($filterName, 'feature')) {
                foreach ($filterValues as $filterValue) {
                    $searchValues['feature'][] = [
                        'term' => [
                            $filterName => $filterValue,
                        ],
                    ];
                }
            } elseif (0 === strpos($filterName, 'attribute_group')) {
                foreach ($filterValues as $filterValue) {
                    $searchValues['attribute_group'][] = [
                        'term' => [
                            $filterName => $filterValue,
                        ],
                    ];
                }
            } elseif ('price' == $filterName) {
                if (is_array($filterValues)) {
                    foreach ($filterValues as $value) {
                        $searchValues['price'][] = [
                            'gte' => $value['min_value'],
                            'lte' => $value['max_value'],
                        ];
                    }
                }
            } elseif ('manufacturer' == $filterName) {
                foreach ($filterValues as $filterValue) {
                    $searchValues['manufacturer'][] = [
                        'term' => [
                            'id_manufacturer' => $filterValue,
                        ],
                    ];
                }
            } elseif ('weight' == $filterName) {
                if (is_array($filterValues)) {
                    foreach ($filterValues as $value) {
                        $searchValues['weight'][] = [
                            'gte' => $value['min_value'],
                            'lte' => $value['max_value'],
                        ];
                    }
                }
            } elseif ('quantity' == $filterName) {
                if (is_array($filterValues)) {
                    foreach ($filterValues as $value) {
                        if ($value) {
                            $searchValues['quantity'][] = [
                                'gt' => 0,
                            ];
                        } else {
                            $searchValues['quantity'][] = [
                                'lte' => 0,
                            ];
                        }
                    }
                }
            } elseif ('category' == $filterName) {
                if (is_array($filterValues)) {
                    foreach ($filterValues as $filterValue) {
                        $searchValues['categories'][] = [
                            'term' => [
                                'categories' => $filterValue,
                            ],
                        ];
                    }
                }
            }
        }

        $categoriesQuery = $this->getQueryFromCategories($idCategory);

        if (!empty($searchValues)) {
            $query['query']['bool']['must'] = $this->getQueryFromSearchValues($searchValues);
            $query['query']['bool']['must'][] = $categoriesQuery;
        } else {
            $query['query'] = $categoriesQuery;
        }

        return $query;
    }

    /**
     * @param array $searchValues
     *
     * @return array
     */
    protected function getQueryFromSearchValues(array $searchValues)
    {
        $context = Context::getContext();
        $query = [];

        foreach ($searchValues as $key => $values) {
            if (in_array($key, ['manufacturer'])) {
                $query[] = [
                    'bool' => [
                        'should' => $values,
                    ],
                ];
            } elseif (in_array($key, ['price', 'weight'])) {

                if ('price' == $key) {
                    $idGroup = $context->customer->id_default_group;
                    $idCurrency = $context->currency->id;
                    $idCountry = $context->country->id;
                    $fieldName = sprintf('price_group_%s_country_%s_currency_%s', $idGroup, $idCountry, $idCurrency);
                } else {
                    $fieldName = $key;
                }

                $priceQuery['bool']['should'] = [];

                foreach ($values as $value) {
                    $priceQuery['bool']['should'][] = [
                        [
                            'bool' => [
                                'must' => [
                                    [
                                        'range' => [
                                            $fieldName => $value,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ];
                }

                $query[] = $priceQuery;
            } elseif ('feature' == $key) {
                $query[] = [
                    'bool' => [
                        'should' => $values,
                    ],
                ];
            } elseif ('attribute_group' == $key) {
                $query[] = [
                    'bool' => [
                        'should' => $values,
                    ],
                ];
            } elseif ('quantity' == $key) {
                $quantityQuery['bool']['should'] = [];

                foreach ($values as $value) {
                    $quantityQuery['bool']['should'][] = [
                        [
                            'bool' => [
                                'must' => [
                                    [
                                        'range' => [
                                            'total_quantity' => $value,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ];
                }

                $query[] = $quantityQuery;
            } elseif ('categories' == $key) {
                $query[] = [
                    'bool' => [
                        'should' => $values,
                    ],
                ];
            }
        }

        return $query;
    }

    /**
     * Get subcategories query
     *
     * @param int $idCategory
     *
     * @return array
     */
    private function getQueryFromCategories($idCategory)
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
            return [];
        }

        $query = [
            'bool' => [
                'should' => [
                    'terms' => [
                        'categories' => [],
                    ],
                ],
            ],
        ];

        foreach ($subCategories as $subCategory) {
            $query['bool']['should']['terms']['categories'][] = (int) $subCategory['id_category'];
        }

        return $query;
    }
}
