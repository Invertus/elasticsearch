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

use Core_Business_ConfigurationInterface;
use Core_Foundation_Database_EntityManager;
use Invertus\Brad\Config\Setting;
use Language;

/**
 * Class IndexBuilder
 *
 * @package Invertus\Brad\Service\Elasticsearch\Builder
 */
class IndexBuilder
{
    /**
     * @var Core_Business_ConfigurationInterface
     */
    private $configuration;

    /**
     * @var Core_Foundation_Database_EntityManager
     */
    private $em;

    /**
     * IndexBuilder constructor.
     *
     * @param Core_Business_ConfigurationInterface $configuration
     * @param Core_Foundation_Database_EntityManager $em
     */
    public function __construct(Core_Business_ConfigurationInterface $configuration, Core_Foundation_Database_EntityManager $em)
    {
        $this->configuration = $configuration;
        $this->em = $em;
    }

    /**
     * Build index mappings, setting & etc.
     *
     * @param int $idShop
     *
     * @return array
     */
    public function buildIndex($idShop)
    {
        $numberOfShards = (int) $this->configuration->get(Setting::NUMBER_OF_SHARDS_ADVANCED);
        $numberOfReplicas = (int) $this->configuration->get(Setting::NUMBER_OF_REPLICAS_ADVANCED);
        $refreshInterval = (int) $this->configuration->get(Setting::REFRESH_INTERVAL_ADVANCED);

        $indexSettings = [
            'settings' => [
                'number_of_shards' => $numberOfShards,
                'number_of_replicas' => $numberOfReplicas,
                'refresh_interval' => $refreshInterval.'s',
            ],
            'mappings' => [
                'products' => [
                    'properties' => [
                        'weight' => [
                            'type' => 'double',
                        ],
                        'reference' => [
                            'type' => 'string',
                            'fields' => [
                                'raw' => [
                                    'type' => 'string',
                                    'index' => 'not_analyzed',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $indexSettings['mappings']['products']['properties'] = array_merge(
            $indexSettings['mappings']['products']['properties'],
            $this->buildIndexPriceMappings($idShop),
            $this->buildIndexNameMapping($idShop)
        );

        return $indexSettings;
    }

    /**
     * Build mapping for prices
     *
     * @param int $idShop
     *
     * @return array
     */
    private function buildIndexPriceMappings($idShop)
    {
        $mapping = [];

        $countriesIds = $this->em->getRepository('BradCountry')->findAllIdsByShopId($idShop);
        $currenciesIds = $this->em->getRepository('BradCurrency')->findAllIdsByShopId($idShop);
        $groupsIds = $this->em->getRepository('BradGroup')->findAllIdsByShopId($idShop);

        foreach ($groupsIds as $idGroup) {
            foreach ($countriesIds as $idCountry) {
                foreach ($currenciesIds as $idCurrency) {
                    $mapping['price_group_'.$idGroup.'_country_'.$idCountry.'_currency_'.$idCurrency] = [
                        'type' => 'double',
                    ];
                }
            }
        }

        return $mapping;
    }

    /**
     * Build mapping for product name
     *
     * @param int $idShop
     *
     * @return array
     */
    private function buildIndexNameMapping($idShop)
    {
        $mapping = [];
        $langIds = Language::getIDs(true, $idShop);

        foreach ($langIds as $idLang) {
            $mapping['name_lang_'.$idLang] = [
                'type' => 'string',
                'fields' => [
                    'raw' => [
                        'type' => 'string',
                        'index' => 'not_analyzed',
                    ],
                ],
            ];
        }

        return $mapping;
    }
}
