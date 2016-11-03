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

use Exception;

/**
 * Class ElasticsearchSearch
 *
 * @package Invertus\Brad\Service\Elasticsearch
 */
class ElasticsearchSearch
{
    /**
     * @var ElasticsearchManager
     */
    private $manager;

    /**
     * ElasticsearchSearch constructor.
     * 
     * @param ElasticsearchManager $manager
     */
    public function __construct(ElasticsearchManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Perform search on products type
     *
     * @param array $query
     * @param int $idShop
     *
     * @return array Array of products
     */
    public function searchProducts(array $query, $idShop)
    {
        $params = [];
        $params['index'] = $this->manager->getIndexPrefix().$idShop;
        $params['type'] = 'products';
        $params['body'] = $query;

        $client = $this->manager->getClient();

        try {
            $response = $client->search($params);
        } catch (Exception $e) {
            return [];
        }

        return $response['hits']['hits'];
    }

    /**
     * Count products by query
     *
     * @param array $query
     * @param $idShop
     *
     * @return int
     */
    public function countProducts(array $query, $idShop)
    {
        $params = [];
        $params['index'] = $this->manager->getIndexPrefix().$idShop;
        $params['type'] = 'products';
        $params['body'] = $query;

        $client = $this->manager->getClient();

        try {
            $response = $client->count($params);
        } catch (Exception $e) {
            return 0;
        }

        return (int) $response['count'];
    }
}
