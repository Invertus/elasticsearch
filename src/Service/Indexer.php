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

namespace Invertus\Brad\Service;

use Configuration;
use Core_Foundation_Database_EntityManager;
use Invertus\Brad\Config\Setting;
use Invertus\Brad\Logger\LoggerInterface;
use Invertus\Brad\Repository\CategoryRepository;
use Invertus\Brad\Repository\ProductRepository;
use Invertus\Brad\Service\Elasticsearch\Builder\DocumentBuilder;
use Invertus\Brad\Service\Elasticsearch\ElasticsearchIndexer;
use Invertus\Brad\Util\Arrays;
use Invertus\Brad\Util\MemoryStat;
use PrestaShopCollection;
use Product;

/**
 * Class Indexer responsible for creating index and indexing products & categories
 *
 * @package Invertus\Brad\Service
 */
class Indexer
{
    /**
     * Indexing constants
     */
    const INDEX_ALL_PRODUCTS = 'all';
    const INDEX_MISSING_PRODUCTS = 'missing';
    const INDEX_PRICES = 'prices';

    /**
     * @var ElasticsearchIndexer $elasticsearchIndexer
     */
    private $elasticsearchIndexer;

    /**
     * @var Core_Foundation_Database_EntityManager $em
     */
    private $em;

    /**
     * @var int $indexedProductsCount
     */
    private $indexedProductsCount = 0;

    /**
     * @var DocumentBuilder
     */
    private $documentBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Indexer constructor.
     *
     * @param ElasticsearchIndexer $elasticserachIndexer
     * @param Core_Foundation_Database_EntityManager $em
     * @param DocumentBuilder $documentBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(ElasticsearchIndexer $elasticserachIndexer, $em, DocumentBuilder $documentBuilder, LoggerInterface $logger)
    {
        $this->elasticsearchIndexer = $elasticserachIndexer;
        $this->em = $em;
        $this->documentBuilder = $documentBuilder;
        $this->logger = $logger;
    }

    /**
     * Get indexed products count
     *
     * @return int
     */
    public function getIndexedProductsCount()
    {
        return $this->indexedProductsCount;
    }

    /**
     * Perform products & categories indexing
     *
     * @param int $idShop
     * @param string $indexingType
     *
     * @return bool
     */
    public function performIndexing($idShop, $indexingType)
    {
        $startTime = microtime(true);
        $message = sprintf('Memory usage before indexing is %s', MemoryStat::getMemoryUsage());
        $this->logger->log($message, [], LoggerInterface::INFO);

        if (self::INDEX_ALL_PRODUCTS == $indexingType) {
            if (!$this->elasticsearchIndexer->deleteIndex($idShop)) {
                return false;
            }
        }

        if (!$this->elasticsearchIndexer->createIndex($idShop)) {
            return false;
        }

        $success = $this->indexProducts($idShop, $indexingType);
        $this->indexCategories($idShop);

        if ($success) {
            $timeTook = microtime(true) - $startTime;
            $message = sprintf('Time indexing took: %s', gmdate('H:i:s', $timeTook));
            $this->logger->log($message, [], LoggerInterface::INFO);

            $message = sprintf('Memory usage after indexing is %s', MemoryStat::getMemoryUsage());
            $this->logger->log($message, [], LoggerInterface::INFO);
        }

        return (bool) $success;
    }

    /**
     * Index products
     *
     * @param int $idShop
     * @param string $indexingType
     *
     * @return bool
     */
    private function indexProducts($idShop, $indexingType)
    {
        $this->indexedProductsCount = 0;

        /** @var ProductRepository $productRepository */
        $productRepository = $this->em->getRepository('BradProduct');
        $productsIds = $productRepository->findAllIdsByShopId($idShop);

        if (empty($productsIds)) {
            return true;
        }

        $indexPrefix = Configuration::get(Setting::INDEX_PREFIX);
        $bulkRequestSize = (int) Configuration::get(Setting::BULK_REQUEST_SIZE_ADVANCED);

        $lastProductsIdsKey = Arrays::getLastKey($productsIds);

        foreach ($productsIds as $productIdKey => $idProduct) {

            $bulkProductIds[] = $idProduct;

            if (count($bulkProductIds) != $bulkRequestSize && $productIdKey != $lastProductsIdsKey) {
                continue;
            }

            $products = new PrestaShopCollection('Product');
            $products->where('id_product', 'in', $bulkProductIds);

            switch ($indexingType) {
                case self::INDEX_PRICES:
                    $bulkParams = $this->getPricesBulkParams($idShop, $products, $indexPrefix);
                    break;
                case self::INDEX_MISSING_PRODUCTS:
                    $bulkParams = $this->getProductsBulkParams($idShop, $products, true, $indexPrefix);
                    break;
                default:
                case self::INDEX_ALL_PRODUCTS:
                    $bulkParams = $this->getProductsBulkParams($idShop, $products, false, $indexPrefix);
                    break;
            }

            $numberOfProductToIndex = (int) (count($bulkParams['body']) / 2);

            if (0 < $numberOfProductToIndex) {
                $success = $this->elasticsearchIndexer->indexBulk($bulkParams);

                if ($success) {
                    $this->indexedProductsCount += $numberOfProductToIndex;
                }
            }

            unset($bulkProductIds, $bulkParams);
        }

        return true;
    }

    /**
     * Get products bulk params
     *
     * @param int $idShop
     * @param PrestaShopCollection $products
     * @param bool $indexOnlyMissingProducts
     * @param string $indexPrefix
     *
     * @return array
     */
    private function getProductsBulkParams($idShop, PrestaShopCollection $products, $indexOnlyMissingProducts, $indexPrefix)
    {
        $bulkParams = ['body' => []];

        /** @var Product $product */
        foreach ($products as $product) {

            if ($indexOnlyMissingProducts) {
                $isProductIndexed = $this->elasticsearchIndexer->isIndexedProduct($product, $idShop);

                if ($isProductIndexed) {
                    continue;
                }
            }

            if (!$product->active || $product->visibility == 'none') {
                $this->elasticsearchIndexer->deleteProduct($product, $idShop);
                continue;
            }

            $bulkParams['body'][] = [
                'index' => [
                    '_index' => $indexPrefix.$idShop,
                    '_type' => 'products',
                    '_id' => $product->id,
                ],
            ];

            $productBody = $this->documentBuilder->buildProductBody($product);
            $productPricesBody = $this->documentBuilder
                ->buildProductPriceBody($product, $idShop);

            $body = array_merge($productBody, $productPricesBody);

            $bulkParams['body'][] = $body;
        }

        return $bulkParams;
    }

    /**
     * Get prices bulk params
     *
     * @param int $idShop
     * @param PrestaShopCollection $products
     * @param string $indexPrefix
     *
     * @return array
     */
    public function getPricesBulkParams($idShop, PrestaShopCollection $products, $indexPrefix)
    {
        $bulkParams = ['body' => []];

        /** @var Product $product */
        foreach ($products as $product) {

            if (!$product->active || $product->visibility == 'none') {
                $this->elasticsearchIndexer->deleteProduct($product, $idShop);
                continue;
            }

            if (!$this->elasticsearchIndexer->isIndexedProduct($product, $idShop)) {
                continue;
            }

            $bulkParams['body'][] = [
                'update' => [
                    '_index' => $indexPrefix.$idShop,
                    '_type' => 'products',
                    '_id' => $product->id,
                ],
            ];

            $productPricesBody = $this->documentBuilder->buildProductPriceBody($product, $idShop);

            $bulkParams['body'][] = ['doc' => $productPricesBody];
        }

        return $bulkParams;
    }

    /**
     * Index categories
     *
     * @param int $idShop
     *
     * @return bool
     */
    public function indexCategories($idShop)
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->em->getRepository('BradCategory');
        $categoriesIds = $categoryRepository->findAllIdsByShopId($idShop);

        if (empty($categoriesIds)) {
            return true;
        }

        $indexPrefix = Configuration::get(Setting::INDEX_PREFIX);
        $bulkRequestSize = (int) Configuration::get(Setting::BULK_REQUEST_SIZE_ADVANCED);

        $lastCategoryIdsKey = Arrays::getLastKey($categoriesIds);
        $bulkCategoriesIds = [];

        foreach ($categoriesIds as $key => $idCategory) {
            $bulkCategoriesIds[] = $idCategory;

            if (count($categoriesIds) != $bulkRequestSize && $lastCategoryIdsKey != $key) {
                continue;
            }

            /** @var \Category[] $categories */
            $categories = new PrestaShopCollection('Category');
            $categories->where('id_category', 'in', $bulkCategoriesIds);
            $bulkParams = [];

            foreach ($categories as $category) {

                $bulkParams['body'][] = [
                    'index' => [
                        '_index' => $indexPrefix.$idShop,
                        '_type' => 'categories',
                        '_id' => $category->id,
                    ],
                ];

                $categoryBody = $this->documentBuilder->buildCategoryBody($category);

                $bulkParams['body'][] = $categoryBody;

                if (!$this->elasticsearchIndexer->indexBulk($bulkParams)) {
                    $this->logger->log('Failed to index categories', [], LoggerInterface::ERROR);
                }
            }

            $bulkCategoriesIds = [];
            unset($bulkParams);
        }

        return true;
    }
}
