<?php

namespace Invertus\Brad\Service\Elasticsearch\Builder;

/**
 * Class IndexBuilder
 *
 * @package Invertus\Brad\Service\Elasticsearch\Builder
 */
class IndexBuilder
{
    /**
     * Build index mappings, setting & etc.
     *
     * @return array
     */
    public function buildIndex()
    {
        return [
            'settings' => [
                'number_of_shards' => 3,
                'number_of_replicas' => 1,
            ],
            'mappings' => [
                'products' => [
                    'properties' => [
                        'weight' => [
                            'type' => 'double',
                        ],
                        'price' => [
                            'type' => 'double',
                        ],
                    ],
                ],
                'categories' => [
                    'properties' => [
                        'nleft' => [
                            'type' => 'long',
                        ],
                        'nright' => [
                            'type' => 'long',
                        ],
                    ],
                ],
            ],
        ];
    }
}
