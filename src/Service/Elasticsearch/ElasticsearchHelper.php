<?php

namespace Invertus\Brad\Service\Elasticsearch;
use Context;
use Exception;

/**
 * Class ElasticsearchHelper
 *
 * @package Invertus\Brad\Service\Elasticsearch
 */
class ElasticsearchHelper
{
    /**
     * Used to get either max or min value of all indexed products
     */
    const AGGS_MAX = 'max';
    const AGGS_MIN = 'min';

    /**
     * @var ElasticsearchManager
     */
    private $elasticsearchManager;

    /**
     * @var Context
     */
    private $context;

    /**
     * ElasticsearchHelper constructor.
     *
     * @param ElasticsearchManager $elasticsearchManager
     * @param Context $context
     */
    public function __construct(ElasticsearchManager $elasticsearchManager, Context $context)
    {
        $this->elasticsearchManager = $elasticsearchManager;
        $this->context = $context;
    }

    /**
     * Get max or min product price
     *
     * @param string $type
     *
     * @return float|null
     */
    public function getAggregatedProductPrice($type)
    {
        if (!in_array($type, [self::AGGS_MAX, self::AGGS_MIN])) {
            return null;
        }

        $idShop = (int) $this->context->shop->id;
        $idGroup = (int) $this->context->customer->id_default_group;
        $idCountry = (int) $this->context->country->id;
        $idCurrency = (int) $this->context->currency->id;

        $params = [];
        $params['index'] = $this->elasticsearchManager->getIndexPrefix().$idShop;
        $params['type'] = 'products';
        $params['body'] = [
            'aggs' => [
                'calculated_price' => [
                    $type => [
                        'field' => sprintf('price_group_%s_country_%s_currency_%s', $idGroup, $idCountry, $idCurrency),
                    ],
                ],
            ],
        ];

        try {
            $response = $this->elasticsearchManager->getClient()->search($params);
        } catch (Exception $e) {
            return null;
        }

        return $response['aggregations']['calculated_price']['value'];
    }

    /**
     * Get min or max product weight
     *
     * @param string $type
     *
     * @return float|null
     */
    public function getAggregatedProductWeight($type)
    {
        if (!in_array($type, [self::AGGS_MAX, self::AGGS_MIN])) {
            return null;
        }

        $idShop = (int) $this->context->shop->id;

        $params = [];
        $params['index'] = $this->elasticsearchManager->getIndexPrefix().$idShop;
        $params['type'] = 'products';
        $params['body'] = [
            'aggs' => [
                'calculated_weight' => [
                    $type => [
                        'field' => 'weight',
                    ],
                ],
            ],
        ];

        try {
            $response = $this->elasticsearchManager->getClient()->search($params);
        } catch (Exception $e) {
            return null;
        }

        return $response['aggregations']['calculated_weight']['value'];
    }
}
