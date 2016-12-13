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
     * @param string $query
     * @param int|null $from
     * @param int|null $size
     * @param string|null $orderBy
     * @param string|null $orderWay
     *
     * @return array
     */
    public function buildProductsQuery($query, $from = null, $size = null, $orderBy = null, $orderWay = null)
    {
        $context = Context::getContext();

        $idLang = (int) $context->language->id;
        $isFuzzySeearchEnabled = (bool) Configuration::get(Setting::FUZZY_SEARCH);

        $elasticsearchQuery = [
            'query' => [
                'bool' => [
                    'should' => [
                        [
                            'match_phrase_prefix' => [
                                'name_lang_'.$idLang => [
                                    'query' => $query,
                                    'boost' => 3,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'description_lang_'.$idLang => [
                                    'query' => $query,
                                    'boost' => 1.5,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'short_description_lang_'.$idLang => [
                                    'query' => $query,
                                    'boost' => 1.5,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'manufacturer_name' => [
                                    'query' => $query,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'reference' => [
                                    'query' => $query,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'category_name' => [
                                    'query' => $query,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'feature_value_keywords_lang_'.$idLang => [
                                    'query' => $query,
                                ],
                            ],
                        ],
                        [
                            'match_phrase_prefix' => [
                                'attribute_keywords_lang_'.$idLang => [
                                    'query' => $query,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if ($isFuzzySeearchEnabled) {
            $elasticsearchQuery['query']['bool']['should'][] = [
                'multi_match' => [
                    'fields' => ['name_lang_'.$idLang, 'category_name'],
                    'query' => $query,
                    'fuzziness' => 'AUTO',
                    'prefix_length' => 2,
                    'max_expansions' => 50,
                ],
            ];
        }

        if (null !== $from) {
            $elasticsearchQuery['from'] = (int) $from;
        }

        if (null !== $size) {
            $elasticsearchQuery['size'] = (int) $size;
        }

        if (null !== $orderBy && null != $orderWay) {
            $elasticsearchQuery['sort'] = $this->buildOrderQuery($orderBy, $orderWay);
        }

        return $elasticsearchQuery;
    }
}
