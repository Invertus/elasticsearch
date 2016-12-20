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

namespace Invertus\Brad\Service\Elasticsearch\Builder;

use Configuration;
use Context;
use Invertus\Brad\Config\Setting;
use Invertus\Brad\DataType\SearchData;

/**
 * Class SearchQueryBuilder
 *
 * @package Invertus\Brad\Service\Elasticsearch\Builder
 */
class SearchQueryBuilder extends AbstractQueryBuilder
{
    /**
     * Build search query
     *
     * @param SearchData $searchData
     * @param bool $countQuery
     *
     * @return array
     */
    public function buildProductsQuery(SearchData $searchData, $countQuery = false)
    {
        $context = Context::getContext();

        $idLang = (int) $context->language->id;
        $isFuzzySeearchEnabled = (bool) Configuration::get(Setting::FUZZY_SEARCH);
        $searchQueryString = $searchData->getSearchQuery();

        $searchQuery = [
            'query' => [
                'bool' => [
                    'should' => [
                        [
                            'match_phrase_prefix' => [
                                'name_lang_'.$idLang => [
                                    'query' => $searchQueryString,
                                    'boost' => 3,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'description_lang_'.$idLang => [
                                    'query' => $searchQueryString,
                                    'boost' => 1.5,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'short_description_lang_'.$idLang => [
                                    'query' => $searchQueryString,
                                    'boost' => 1.5,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'manufacturer_name' => [
                                    'query' => $searchQueryString,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'reference' => [
                                    'query' => $searchQueryString,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'category_name' => [
                                    'query' => $searchQueryString,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'feature_value_keywords_lang_'.$idLang => [
                                    'query' => $searchQueryString,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'attribute_keywords_lang_'.$idLang => [
                                    'query' => $searchQueryString,
                                ],
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
                    'query' => $searchQueryString,
                    'fuzziness' => 'AUTO',
                    'prefix_length' => 2,
                    'max_expansions' => 50,
                ],
            ];
        }

        if ($countQuery) {
            return $searchQuery;
        }

        $orderBy  = $searchData->getOrderBy();
        $orderWay = $searchData->getOrderWay();

        $searchQuery['from'] = (int) $searchData->getFrom();
        $searchQuery['size'] = (int) $searchData->getSize();
        $searchQuery['sort'] = $this->buildOrderQuery($orderBy, $orderWay)->toArray();

        return $searchQuery;
    }
}
