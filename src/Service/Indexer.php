<?php

namespace Invertus\Brad\Service;

use Category;
use Core_Business_ConfigurationInterface as ConfigurationInterface;
use Core_Foundation_Database_EntityManager as EntityManager;
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
     * @var EntityManager $em
     */
    private $em;

    /**
     * @var int $indexedProductsCount
     */
    private $indexedProductsCount = 0;

    /**
     * @var ConfigurationInterface
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
     * @param EntityManager $em
     * @param ConfigurationInterface $configuration
     * @param DocumentBuilder $documentBuilder
     * @param Validator $validator
     */
    public function __construct(ElasticsearchIndexer $elasticserachIndexer, EntityManager $em, ConfigurationInterface $configuration, DocumentBuilder $documentBuilder, Validator $validator)
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

        if (!$this->indexCategories($idShop)) {
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

        $indexPrefix = $this->configuration->get(Setting::INDEX_PREFIX);
        $bulkRequestSize = (int) $this->configuration->get(Setting::BULK_REQUEST_SIZE_ADVANCED);

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

                $bulkParams['body'][] = $this->documentBuilder->buildProductBody($product);
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
     * Index categories
     *
     * @param int $idShop
     *
     * @return bool
     */
    private function indexCategories($idShop)
    {
        /** @var \Invertus\Brad\Repository\CategoryRepository $categoryRepository */
        $categoryRepository = $this->em->getRepository('BradCategory');
        $categoriesIds = $categoryRepository->findAllIdsByShopId($idShop);

        if (empty($categoriesIds)) {
            return true;
        }

        $idRootCategory = $this->configuration->get('PS_ROOT_CATEGORY');
        $indexPrefix = $this->configuration->get(Setting::INDEX_PREFIX);
        $bulkRequestSize = (int) $this->configuration->get(Setting::BULK_REQUEST_SIZE_ADVANCED);

        $lastCategoriesIdsKey = Arrays::getLastKey($categoriesIds);
        $bulkCategoryIds = [];

        Arrays::removeValue($categoriesIds, $idRootCategory);

        foreach ($categoriesIds as $categoryIdKey => $idCategory) {

            $bulkCategoryIds[] = $idCategory;

            if (count($bulkCategoryIds) != $bulkRequestSize && $categoryIdKey != $lastCategoriesIdsKey) {
                continue;
            }

            $categories = new PrestaShopCollection('Category');
            $categories->where('id_category', 'in', $bulkCategoryIds);

            $bulkParams = ['body' => []];

            /** @var Category $category */
            foreach ($categories as $category) {

                $bulkParams['body'][] = [
                    'index' => [
                        '_index' => $indexPrefix.$idShop,
                        '_type' => 'categories',
                        '_id' => $category->id,
                    ]
                ];

                $bulkParams['body'][] = $this->documentBuilder->buildCategoryBody($category);
            }

            if (!empty($bulkParams['body'])) {
                $success = $this->elasticsearchIndexer->indexBulk($bulkParams);
                if (!$success) {
                    return false;
                }
            }

            unset($bulkParams);
        }

        return true;
    }
}
