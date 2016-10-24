<?php

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

    /**
     * Search settings block
     */
    const DISPLAY_SEARCH_INPUT = 'BRAD_DISPLAY_SEARCH_INPUT';
    const INSTANT_SEARCH = 'BRAD_INSTANT_SEARCH';
    const DISPLAY_DYNAMIC_SEARCH_RESULTS = 'BRAD_DISPLAY_DYNAMIC_SEARCH_RESULTS';
    const INSTANT_SEARCH_RESULTS_COUNT = 'BRAD_INSTANT_SEARCH_RESULTS_COUNT';
    const MINIMAL_SEARCH_WORD_LENGTH = 'BRAD_MINIMAL_SEARCH_WORD_LENGTH';
    const FUZZY_SEARCH = 'BRAD_FUZZY_SEARCH';

    /**
     * Get default module settings
     *
     * @return array
     */
    public static function getDefaultSettings()
    {
        return [
            self::ELASTICSEARCH_HOST_1 => '127.0.0.1:9200',

            self::DISPLAY_SEARCH_INPUT => 1,
            self::INSTANT_SEARCH => 1,
            self::DISPLAY_DYNAMIC_SEARCH_RESULTS => 1,
            self::INSTANT_SEARCH_RESULTS_COUNT => 10,
            self::MINIMAL_SEARCH_WORD_LENGTH => 3,
            self::FUZZY_SEARCH => 1,
        ];
    }
}
