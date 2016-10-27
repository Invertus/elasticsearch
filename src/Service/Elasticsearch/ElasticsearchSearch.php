<?php

namespace Invertus\Brad\Service\Elasticsearch;

use Exception;

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
