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

namespace Invertus\Brad\Config;

/**
 * Class Setting
 *
 * @package Invertus\Brad\Config
 */
class Setting
{
    /**
     * Elasticsearch settings block
     */
    const INDEX_PREFIX = 'BRAD_ELASTICSEARCH_INDEX_PREFIX';
    const ELASTICSEARCH_HOST_1 = 'BRAD_ELSTICSEARCH_HOST_1';
    const NUMBER_OF_SHARDS_ADVANCED = 'BRAD_NUMBER_OF_SHARDS';
    const NUMBER_OF_REPLICAS_ADVANCED = 'BRAD_NUMBER_OF_REPLICAS';
    const REFRESH_INTERVAL_ADVANCED = 'BRAD_REFRESH_INTERVAL';

    /**
     * Search settings block
     */
    const ENABLE_SEARCH = 'BRAD_ENABLE_SEARCH';
    const INSTANT_SEARCH = 'BRAD_INSTANT_SEARCH';
    const DISPLAY_DYNAMIC_SEARCH_RESULTS = 'BRAD_DISPLAY_DYNAMIC_SEARCH_RESULTS';
    const INSTANT_SEARCH_RESULTS_COUNT = 'BRAD_INSTANT_SEARCH_RESULTS_COUNT';
    const MINIMAL_SEARCH_WORD_LENGTH = 'BRAD_MINIMAL_SEARCH_WORD_LENGTH';
    const FUZZY_SEARCH = 'BRAD_FUZZY_SEARCH';

    /**
     * Filter settings
     */
    const ENABLE_FILTERS = 'BRAD_ENABLE_FILTERS';
    const HIDE_FILTERS_WITH_NO_PRODUCTS = 'BRAD_HIDE_FILTERS_WITH_NO_PRODUCTS';
    const DISPLAY_NUMBER_OF_MATCHING_PRODUCTS = 'BRAD_DISPLAY_NUMBER_OF_MATCHING_PRODUCTS';

    /**
     * General settings
     */
    const BULK_REQUEST_SIZE_ADVANCED = 'BRAD_BULK_REQUEST_SIZE';

    /**
     * Get default module settings
     *
     * @return array
     */
    public static function getDefaultSettings()
    {
        return [
            self::ELASTICSEARCH_HOST_1 => '127.0.0.1:9200',
            self::NUMBER_OF_SHARDS_ADVANCED => 3,
            self::NUMBER_OF_REPLICAS_ADVANCED => 1,
            self::REFRESH_INTERVAL_ADVANCED => 30,

            self::ENABLE_SEARCH => 1,
            self::INSTANT_SEARCH => 1,
            self::DISPLAY_DYNAMIC_SEARCH_RESULTS => 1,
            self::INSTANT_SEARCH_RESULTS_COUNT => 10,
            self::MINIMAL_SEARCH_WORD_LENGTH => 3,
            self::FUZZY_SEARCH => 1,

            self::BULK_REQUEST_SIZE_ADVANCED => 2000,

            self::ENABLE_FILTERS => 1,
            self::HIDE_FILTERS_WITH_NO_PRODUCTS => 0,
            self::DISPLAY_NUMBER_OF_MATCHING_PRODUCTS => 1,
        ];
    }
}
