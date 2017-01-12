<?php

use Invertus\Brad\Config\Sort;
use Invertus\Brad\DataType\FilterData;
use Invertus\Brad\Service\Elasticsearch\Builder\FilterQueryBuilder;
use PHPUnit\Framework\TestCase;

class FilterQueryBuilderTest extends TestCase
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
        $filterData->setIdCategory(3);
        $filterData->setOrderBy(Sort::BY_NAME);
        $filterData->setOrderWay(Sort::WAY_DESC);
        $filterData->setPage(1);
        $filterData->setSize(6);

        $filterData->setSelectedFilters($selectedFilters);

        $filterQueryBuilder = new FilterQueryBuilder();
        $filtersQuery = $filterQueryBuilder->buildFilterQuery($filterData, true);
        
        $this->assertEquals($expectedQuery, $filtersQuery);
    }

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
                            'must' => [
                                [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'bool' => [
                                                    'should' => [
                                                        [
                                                            'term' => [
                                                                'attribute_group_3' => 11,
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                            [
                                                'bool' => [
                                                    'should' => [
                                                        [
                                                            'term' => [
                                                                'categories' => 3,
                                                            ],

                                                        ],
                                                        [
                                                            'term' => [
                                                                'categories' => 4,
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'bool' => [
                                        'should' => [
                                            [
                                                'term' => [
                                                    'categories' => 4,
                                                ],
                                            ],
                                            [
                                                'term' => [
                                                    'categories' => 5,
                                                ],
                                            ],
                                            [
                                                'term' => [
                                                    'categories' => 7,
                                                ],
                                            ],
                                            [
                                                'term' => [
                                                    'categories' => 8
                                                ],
                                            ],
                                            [
                                                'term' => [
                                                    'categories' => 9
                                                ],
                                            ],
                                            [
                                                'term' => [
                                                    'categories' => 10
                                                ],
                                            ],
                                            [
                                                'term' => [
                                                    'categories' => 11
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