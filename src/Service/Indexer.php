<?php

namespace Invertus\Brad\Service;

use Category;
use Core_Business_ConfigurationInterface;
use Core_Foundation_Database_EntityManager;
use Invertus\Brad\Config\Setting;
use Invertus\Brad\Repository\ProductRepository;
use Invertus\Brad\Service\Elasticsearch\Builder\DocumentBuilder;
use Invertus\Brad\Service\Elasticsearch\ElasticsearchIndexer;
use Invertus\Brad\Util\Arrays;
use Invertus\Brad\Util\Validator;
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
     * @var Core_Business_ConfigurationInterface
     */
    private $configuration;

    /**
     * @var DocumentBuilder
     */
    private $documentBuilder;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * Indexer constructor.
     *
     * @param ElasticsearchIndexer $elasticserachIndexer
     * @param Core_Foundation_Database_EntityManager $em
     * @param Core_Business_ConfigurationInterface $configuration
     * @param DocumentBuilder $documentBuilder
     * @param Validator $validator
     */
    public function __construct(ElasticsearchIndexer $elasticserachIndexer, Core_Foundation_Database_EntityManager $em, Core_Business_ConfigurationInterface $configuration, DocumentBuilder $documentBuilder, Validator $validator)
    {
        $this->elasticsearchIndexer = $elasticserachIndexer;
        $this->em = $em;
        $this->configuration = $configuration;
        $this->documentBuilder = $documentBuilder;
        $this->validator = $validator;
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
     * @param bool $indexOnlyMissingProducts
     *
     * @return bool
     */
    public function performIndexing($idShop, $indexOnlyMissingProducts = false)
    {
        if (!$indexOnlyMissingProducts) {
            if (!$this->elasticsearchIndexer->deleteIndex($idShop)) {
                return false;
            }
        }

        if (!$this->elasticsearchIndexer->createIndex($idShop)) {
            return false;
        }

        if (!$this->indexProducts($idShop, $indexOnlyMissingProducts)) {
            return false;
        }

        return true;
    }

    /**
     * Index products
     *
     * @param int $idShop
     * @param bool $indexOnlyMissingProducts
     *
     * @return bool
     */
    private function indexProducts($idShop, $indexOnlyMissingProducts = false)
    {
        $this->indexedProductsCount = 0;

        /** @var ProductRepository $productRepository */
        $productRepository = $this->em->getRepository('BradProduct');
        $productsIds = $productRepository->findAllIdsByShopId($idShop);

        if (empty($productsIds)) {
            return true;
        }

        $countriesIds = $this->em->getRepository('BradCountry')->findAllIdsByShopId($idShop);
        $currenciesIds = $this->em->getRepository('BradCurrency')->findAllIdsByShopId($idShop);
        $groupsIds = $this->em->getRepository('BradGroup')->findAllIdsByShopId($idShop);

        $indexPrefix = $this->configuration->get(Setting::INDEX_PREFIX);
        $bulkRequestSize = (int) $this->configuration->get(Setting::BULK_REQUEST_SIZE_ADVANCED);
        $useTax = (bool) $this->configuration->get('PS_TAX');

        $lastProductsIdsKey = Arrays::getLastKey($productsIds);
        $bulkProductIds = [];

        foreach ($productsIds as $productIdKey => $idProduct) {

            $bulkProductIds[] = $idProduct;

            if (count($bulkProductIds) != $bulkRequestSize && $productIdKey != $lastProductsIdsKey) {
                continue;
            }

            $products = new PrestaShopCollection('Product');
            $products->where('id_product', 'in', $bulkProductIds);

            $bulkParams = ['body' => []];

            /** @var Product $product */
            foreach ($products as $product) {

                if ($indexOnlyMissingProducts) {
                    $isProductIndexed = $this->elasticsearchIndexer->isIndexedProduct($product, $idShop);

                    if ($isProductIndexed) {
                        continue;
                    }
                }

                if (!$this->validator->isProductValidForIndexing($product)) {
                    $this->elasticsearchIndexer->deleteProduct($product, $idShop);
                    continue;
                }

                $bulkParams['body'][] = [
                    'index' => [
                        '_index' => $indexPrefix.$idShop,
                        '_type' => 'products',
                        '_id' => $product->id,
                    ]
                ];

                $productBody = $this->documentBuilder->buildProductBody($product);
                $productPricesBody = $this->documentBuilder
                    ->buildProductPriceBody($product, $idShop, $useTax, $groupsIds, $currenciesIds, $countriesIds);

                $body = array_merge($productBody, $productPricesBody);

                $bulkParams['body'][] = $body;
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
}
