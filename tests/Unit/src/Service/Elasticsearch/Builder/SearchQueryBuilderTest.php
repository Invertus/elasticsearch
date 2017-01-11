<?php

use Invertus\Brad\Config\Setting;
use Invertus\Brad\DataType\SearchData;
use Invertus\Brad\Service\Elasticsearch\Builder\SearchQueryBuilder;
use PHPUnit\Framework\TestCase;

class SearchQueryBuilderTest extends TestCase
{
    public function testSearchQueryReturnsCorrectSearchQuery()
    {
        $searchData = new SearchData();
        $searchData->setSearchQuery('dress');

        $searchQueryBuilder = new SearchQueryBuilder();
        $query = $searchQueryBuilder->buildProductsQuery($searchData, true);

        $context = Context::getContext();
        $idLang = (int) $context->language->id;

        $isFuzzySeearchEnabled = (bool) Configuration::get(Setting::FUZZY_SEARCH);

        $expectedQuery = [
            'query' => [
                'bool' => [
                    'should' => [
                        [
                            'match_phrase_prefix' => [
                                'name_lang_'.$idLang => [
                                    'query' => $searchData->getSearchQuery(),
                                    'boost' => 3,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'description_lang_'.$idLang => [
                                    'query' => $searchData->getSearchQuery(),
                                    'boost' => 1.5,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'short_description_lang_'.$idLang => [
                                    'query' => $searchData->getSearchQuery(),
                                    'boost' => 1.5,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'manufacturer_name' => [
                                    'query' => $searchData->getSearchQuery(),
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'reference' => [
                                    'query' => $searchData->getSearchQuery(),
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'category_name' => [
                                    'query' => $searchData->getSearchQuery(),
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'feature_value_keywords_lang_'.$idLang => [
                                    'query' => $searchData->getSearchQuery(),
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'attribute_keywords_lang_'.$idLang => [
                                    'query' => $searchData->getSearchQuery(),
                                ],
                            ],
                        ],
                        [
                            'multi_match' => [
                                'fields' => ['name_lang_'.$idLang, 'category_name'],
                                'query' => $searchData->getSearchQuery(),
                                'fuzziness' => 'AUTO',
                                'prefix_length' => 2,
                                'max_expansions' => 50,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if ($isFuzzySeearchEnabled) {
            $searchQuery['query']['bool']['should'][] = [
                'multi_match' => [
                    'fields' => ['name_lang_'.$idLang, 'category_name'],
                    'query' => $searchData->getSearchQuery(),
                    'fuzziness' => 'AUTO',
                    'prefix_length' => 2,
                    'max_expansions' => 50,
                ],
            ];
        }

        $this->assertEquals($expectedQuery, $query);
    }
}
