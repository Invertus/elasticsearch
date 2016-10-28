<?php

namespace Invertus\Brad\Service\Elasticsearch;

use Elasticsearch\Client;
use Exception;

/**
 * Class ElasticsearchManager
 *
 * @package Invertus\Brad\Service\Elasticsearch
 */
class ElasticsearchManager
{
    /** @var Client */
    private $client;

    /** @var string */
    private $indexPrefix;

    /**
     * ElasticsearchManager constructor.
     *
     * @param Client $client
     * @param string $indexPrefix
     */
    public function __construct(Client $client, $indexPrefix)
    {
        $this->client = $client;
        $this->indexPrefix = $indexPrefix;
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
     * Check if there are any alive nodes in cluster
     *
     * @return bool
     */
    public function isConnectionAvailable()
    {
        try {
            $status = $this->client->ping();
        } catch (Exception $e) {
            return false;
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
