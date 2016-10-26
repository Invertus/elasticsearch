<?php

namespace Invertus\Brad\Service\Elasticsearch;

use Category;
use Exception;
use Invertus\Brad\Service\Elasticsearch\Builder\DocumentBuilder;
use Invertus\Brad\Service\Elasticsearch\Builder\IndexBuilder;
use Product;

/**
 * Class ElasticsearchIndexer
 *
 * @package Invertus\Brad\Service\Elasticsearch
 */
class ElasticsearchIndexer
{
    /**
     * @var ElasticsearchManager
     */
    private $manager;

    /**
     * @var DocumentBuilder
     */
    private $documentBuilder;

    /**
     * @var IndexBuilder
     */
    private $indexBuilder;

    /**
     * ElasticsearchIndexer constructor.
     *
     * @param ElasticsearchManager $manager
     * @param DocumentBuilder $documentBuilder
     * @param IndexBuilder $indexBuilder
     */
    public function __construct(ElasticsearchManager $manager, DocumentBuilder $documentBuilder, IndexBuilder $indexBuilder)
    {
        $this->manager = $manager;
        $this->documentBuilder = $documentBuilder;
        $this->indexBuilder = $indexBuilder;
    }

    /**
     * Create Elasticsearch index if it does not exist for given shop
     *
     * @param int $idShop
     *
     * @return bool
     */
    public function createIndex($idShop)
    {
        if ($this->isCreatedIndex($idShop)) {
            return true;
        }

        $params = [];
        $params['index'] = $this->manager->getIndexPrefix().$idShop;
        $params['body'] = $this->indexBuilder->buildIndex();

        $client = $this->manager->getClient();

        try {
            $response = $client->indices()->create($params);
        } catch (Exception $e) {
            return false;
        }

        return $response['acknowledged'];
    }

    /**
     * Delete index
     *
     * @param int $idShop
     *
     * @return bool
     */
    public function deleteIndex($idShop)
    {
        if (!$this->isCreatedIndex($idShop)) {
            return true;
        }

        $params = [];
        $params['index'] = $this->manager->getIndexPrefix().$idShop;

        $client = $this->manager->getClient();

        try {
            $response = $client->indices()->delete($params);
        } catch (Exception $e) {
            return false;
        }

        return $response['acknowledged'];
    }

    /**
     * Check if index is created for given shop
     *
     * @param int $idShop
     *
     * @return bool
     */
    public function isCreatedIndex($idShop)
    {
        $params = [];
        $params['index'] = $this->manager->getIndexPrefix().$idShop;

        $client = $this->manager->getClient();

        try {
            $response = $client->indices()->exists($params);
        } catch (Exception $e) {
            return false;
        }

        return $response;
    }

    /**
     * Index given product
     *
     * @param Product $product
     * @param int $idShop
     *
     * @return bool
     */
    public function indexProduct(Product $product, $idShop)
    {
        $body = $this->documentBuilder->buildProductBody($product);

        $params = [
            'index' => $this->manager->getIndexPrefix().$idShop,
            'type' => 'products',
            'id' => $product->id,
            'body' => $body,
        ];

        $client = $this->manager->getClient();

        try {
            $response = $client->index($params);
        } catch (Exception $e) {
            return false;
        }

        return $response['created'];
    }

    /**
     * Check if product is already indexed
     *
     * @param Product $product
     * @param int $idShop
     *
     * @return bool
     */
    public function isIndexedProduct(Product $product, $idShop)
    {
        $params = [];
        $params['index'] = $this->manager->getIndexPrefix().$idShop;
        $params['type'] = 'products';
        $params['id'] = $product->id;

        $client = $this->manager->getClient();

        try {
            $response = $client->get($params);
        } catch (Exception $exception) {
            return false;
        }

        return $response['found'];
    }

    /**
     * Index products with bulk action
     *
     * @param array
     *
     * @return bool
     */
    public function indexBulk(array $params)
    {
        $client = $this->manager->getClient();

        try {
            $response = $client->bulk($params);
        } catch (Exception $e) {
            return false;
        }

        return !$response['errors'];
    }

    /**
     * Delete product
     *
     * @param Product $product
     * @param int $idShop
     *
     * @return bool
     */
    public function deleteProduct(Product $product, $idShop)
    {
        $params = [];
        $params['index'] = $this->manager->getIndexPrefix().$idShop;
        $params['type'] = 'products';
        $params['id'] = $product->id;

        $client = $this->manager->getClient();

        try {
            $response = $client->delete($params);
        } catch (Exception $e) {
            return false;
        }

        return $response['found'];
    }

    /**
     * Index given category
     *
     * @param Category $category
     * @param int $idShop
     *
     * @return bool
     */
    public function indexCategory(Category $category, $idShop)
    {
        $body = $this->documentBuilder->buildCategoryBody($category);

        $params = [];
        $params['index'] = $this->manager->getIndexPrefix().$idShop;
        $params['type'] = 'categories';
        $params['id'] = $category->id;
        $params['body'] = $body;

        $client = $this->manager->getClient();

        try {
            $response = $client->index($params);
        } catch (Exception $e) {
            return false;
        }

        return $response['created'];
    }
}
