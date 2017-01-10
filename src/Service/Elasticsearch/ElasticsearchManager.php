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

use Elasticsearch\Client;
use Exception;
use Invertus\Brad\Logger\LoggerInterface;

/**
 * Class ElasticsearchManager
 *
 * @package Invertus\Brad\Service\Elasticsearch
 */
class ElasticsearchManager
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $indexPrefix;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ElasticsearchManager constructor.
     *
     * @param Client $client
     * @param string $indexPrefix
     * @param LoggerInterface $logger
     */
    public function __construct(Client $client, $indexPrefix, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->indexPrefix = $indexPrefix;
        $this->logger = $logger;
    }

    /**
     * Get Elasticseach client instance
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get index name
     *
     * @return string
     */
    public function getIndexPrefix()
    {
        return $this->indexPrefix;
    }

    /**
     * Get version number of Elasticsearch service
     *
     * @return float
     */
    public function getVersion()
    {
        try {
            $info = $this->client->info();
        } catch (Exception $e) {
            return 0.0;
        }

        return $info['version']['number'];
    }

    /**
     * Get number of indexed products
     *
     * @param int $idShop
     *
     * @return int
     */
    public function getProductsCount($idShop)
    {
        $params = [];
        $params['index'] = $this->indexPrefix.$idShop;
        $params['type'] = 'products';

        try {
            $response = $this->client->count($params);
        } catch (Exception $e) {
            return 0;
        }

        return (int) $response['count'];
    }

    /**
     * Check if there are any alive nodes in cluster
     *
     * @return bool
     */
    public function isConnectionAvailable()
    {
        static $status;

        if (isset($status)) {
            return $status;
        }

        try {
            $status = $this->client->ping();
        } catch (Exception $e) {
            $status = false;
        }

        return $status;
    }

    /**
     * Check if index is created for a given shop
     *
     * @param int $idShop
     *
     * @return bool
     */
    public function isIndexCreated($idShop)
    {
        $params = [];
        $params['index'] = $this->indexPrefix.$idShop;

        try {
            $response = $this->client->indices()->exists($params);
        } catch (Exception $e) {
            return false;
        }

        return $response;
    }
}
