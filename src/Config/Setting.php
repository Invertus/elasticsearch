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
        ];
    }
}
