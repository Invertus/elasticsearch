<?php

namespace Invertus\Brad\Service\Elasticsearch\Builder;

use Core_Business_ConfigurationInterface as ConfigurationInterface;
use Invertus\Brad\Config\Setting;

/**
 * Class IndexBuilder
 *
 * @package Invertus\Brad\Service\Elasticsearch\Builder
 */
class IndexBuilder
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * IndexBuilder constructor.
     *
     * @param ConfigurationInterface $configuration
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Build index mappings, setting & etc.
     *
     * @return array
     */
    public function buildIndex()
    {
        $numberOfShards = (int) $this->configuration->get(Setting::NUMBER_OF_SHARDS_ADVANCED);
        $numberOfReplicas = (int) $this->configuration->get(Setting::NUMBER_OF_REPLICAS_ADVANCED);
        $refreshInterval = $this->configuration->get(Setting::REFRESH_INTERVAL_ADVANCED);

        return [
            'settings' => [
                'number_of_shards' => $numberOfShards,
                'number_of_replicas' => $numberOfReplicas,
                'refresh_interval' => $refreshInterval,
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
