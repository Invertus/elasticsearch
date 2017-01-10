<?php

use Invertus\Brad\Config\Sort;
use Invertus\Brad\Service\UrlParser;
use PHPUnit\Framework\TestCase;

class UrlParserTest extends TestCase
{
    /**
     * @param array $values
     * @param array $expectedValues
     *
     * @dataProvider getTestData
     */
    public function testGettersReturnsCorrectPages(array $values, array $expectedValues)
    {
        $_GET = $values;

        $urlParser = new UrlParser();
        $urlParser->parse($_GET);

        $this->assertEquals($urlParser->getPage(), $expectedValues['p']);
        $this->assertEquals($urlParser->getOrderBy(), $expectedValues['orderby']);
        $this->assertEquals($urlParser->getOrderWay(), $expectedValues['orderway']);
        $this->assertEquals($urlParser->getQueryString(), $expectedValues['query_string']);
        $this->assertEquals($urlParser->getSelectedFilters(), $expectedValues['selected_filters']);
    }
    
    public function getTestData()
    {
        return [
            [
                [
                    'manufacturer' => 1,
                    'price' => '16.4:114.76-138.65:145.27',
                    'attribute_group_7' => '3-7-4',
                    'quantity' => 1,
                    'weight' => '0:4.5-10:12.5-20:25.3',
                    'feature_5' => '7-8-6',
                    'category' => '2-6',
                ],
                [
                    'p' => 1,
                    'orderby' => Sort::BY_RELEVANCE,
                    'orderway' => Sort::WAY_ASC,
                    'query_string' => 'manufacturer=1&price=16.4:114.76-138.65:145.27&attribute_group_7=3-7-4&quantity=1&weight=0:4.5-10:12.5-20:25.3&feature_5=7-8-6&category=2-6',
                    'selected_filters' => [
                        'manufacturer' => ['1'],
                        'price' => [
                            [
                                'min_value' => '16.4',
                                'max_value' => '114.76',
                            ],
                            [
                                'min_value' => '138.65',
                                'max_value' => '145.27',
                            ],
                        ],
                        'attribute_group_7' => ['3', '7', '4'],
                        'quantity' => ['1'],
                        'weight' => [
                            [
                                'min_value' => '0',
                                'max_value' => '4.5',
                            ],
                            [
                                'min_value' => '10',
                                'max_value' => '12.5',
                            ],
                            [
                                'min_value' => '20',
                                'max_value' => '25.3',
                            ],
                        ],
                        'feature_5' => ['7', '8', '6'],
                        'category' => ['2', '6'],
                    ],
                ],
            ],
        ];
    }
}
