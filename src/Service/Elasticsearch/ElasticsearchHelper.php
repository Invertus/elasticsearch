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
     */
    public function __construct(ElasticsearchManager $elasticsearchManager)
    {
        $this->elasticsearchManager = $elasticsearchManager;
        $this->context = Context::getContext();
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
