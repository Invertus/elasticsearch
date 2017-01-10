<?php

use Invertus\Brad\Config\Sort;
use Invertus\Brad\DataType\FilterData;
use Invertus\Brad\Service\Elasticsearch\Builder\FilterQueryBuilder;

class FilterQueryBuilderTest extends PrestaShopPHPUnit
{
    /**
     * @param array $selectedFilters
     * @param array $expectedQuery
     *
     * @dataProvider getSelectedFiltersAndQueries
     */
    public function testBuildFilterQueryReturnsCorrectElasticsearchQuery($selectedFilters, $expectedQuery)
    {
        $filterData = new FilterData();
        $filterData->setIdCategory(2);
        $filterData->setOrderBy(Sort::BY_NAME);
        $filterData->setOrderWay(Sort::WAY_DESC);
        $filterData->setPage(1);
        $filterData->setSize(6);

        $filterData->setSelectedFilters($selectedFilters);

        $filterQueryBuilder = new FilterQueryBuilder();
        $filtersQuery = $filterQueryBuilder->buildFilterQuery($filterData, true);

        $this->assertEquals($expectedQuery, $filtersQuery);
    }

    /**
     * @return array
     */
    public function getSelectedFiltersAndQueries()
    {
        return [
            [
                [
                    'attribute_group_3' => [11],
                    'category' => [3, 4],
                ],
                [
                    'query' => [
                        'bool' => [
                            'must' =>
                                [
                                    [
                                        'bool' =>
                                            [
                                                'should' =>
                                                    [
                                                        [
                                                            'term' =>
                                                                [
                                                                    'attribute_group_3' => 11,
                                                                ],
                                                        ],
                                                    ],
                                            ],
                                    ],
                                    [
                                        'bool' =>
                                            [
                                                'should' =>
                                                    [
                                                        [
                                                            'term' =>
                                                                [
                                                                    'categories' => 3,
                                                                ],
                                                        ],
                                                        [
                                                            'term' =>
                                                                [
                                                                    'categories' => 4,
                                                                ],
                                                        ],
                                                    ],
                                            ],
                                    ],
                                ],
                        ],
                    ],
                ],
            ],
            [
                [
                    'manufacturer' => ['1'],
                    'attribute_group_3' => ['7'],
                    'quantity' => ['1'],
                    'weight' => [
                        [
                            'min_value' => '0',
                            'max_value' => '4.5',
                        ],
                    ],
                    'feature_5' => ['3'],
                    'category' => ['10'],
                ],
                [
                    'query' => [
                        'bool' => [
                            'must' =>
                                [
                                    ['bool' =>
                                        [
                                            'should' => [
                                                [
                                                    'term' => [
                                                        'id_manufacturer' => 1,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    ['bool' =>
                                        [
                                            'should' => [
                                                [
                                                    'term' => [
                                                        'attribute_group_3' => 7,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    ['bool' =>
                                        [
                                            'should' => [
                                                [
                                                    'term' => [
                                                        'in_stock_when_global_oos_deny_orders' => 1,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    ['bool' =>
                                        [
                                            'should' => [
                                                [
                                                    'range' => [
                                                        'weight' => [
                                                            'gte' => 0,
                                                            'lt' => 4.51,
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    ['bool' =>
                                        [
                                            'should' => [
                                                [
                                                    'term' => [
                                                        'feature_5' => 3,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    ['bool' =>
                                        [
                                            'should' => [
                                                [
                                                    'term' => [
                                                        'categories' => 10,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                        ],
                    ],
                ],
            ],
        ];
    }
}