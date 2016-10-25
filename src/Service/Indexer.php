<?php

namespace Invertus\Brad\Service;

use Category;
use Core_Business_ConfigurationInterface as ConfigurationInterface;
use Core_Foundation_Database_EntityManager as EntityManager;
use Invertus\Brad\Repository\ProductRepository;
use Invertus\Brad\Service\Elasticsearch\ElasticsearchIndexer;
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
     * Indexer constructor.
     *
     * @param ElasticsearchIndexer $elasticserachIndexer
     * @param EntityManager $em
     * @param ConfigurationInterface $configuration
     */
    public function __construct(ElasticsearchIndexer $elasticserachIndexer, EntityManager $em, ConfigurationInterface $configuration)
    {
        $this->elasticsearchIndexer = $elasticserachIndexer;
        $this->em = $em;
        $this->configuration = $configuration;
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
        if (!$this->createIndexIfNotExists($idShop)) {
            return false;
        }

        $this->indexedProductsCount = 0;

        /** @var ProductRepository $productRepository */
        $productRepository = $this->em->getRepository('BradProduct');
        $productsIds = $productRepository->findAllIdsByShopId($idShop);

        if (empty($productsIds)) {
            return true;
        }

        $lastProductsIdsKey = array_pop(array_keys($productsIds));
        $bulkProductIds = [];

        foreach ($productsIds as $key => $idProduct) {

            $bulkProductIds[] = $idProduct;

            //@todo: change it with config value
            if (2000 > count($bulkProductIds) && $key !== $lastProductsIdsKey) {
                continue;
            }

            $products = new PrestaShopCollection('Product');
            $products->where('id_product', 'in', $bulkProductIds);

            /** @var Product $product */
            foreach ($products as $product) {

                if ($indexOnlyMissingProducts) {
                    $isProductIndexed = $this->elasticsearchIndexer->isIndexedProduct($product, $idShop);

                    if ($isProductIndexed) {
                        continue;
                    }
                }

                if (!$this->isValidProduct($product)) {
                    $this->elasticsearchIndexer->deleteProduct($product, $idShop);
                    continue;
                }

                $indexed = $this->elasticsearchIndexer->indexProduct($product, $idShop);

                if ($indexed) {
                    $this->indexedProductsCount++;
                }
            }

            unset($bulkProductIds);
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
        if (!$this->createIndexIfNotExists($idShop)) {
            return false;
        }

        $idRootCategory = $this->configuration->get('PS_ROOT_CATEGORY');

        /** @var \Invertus\Brad\Repository\CategoryRepository $categoryRepository */
        $categoryRepository = $this->em->getRepository('BradCategory');
        $categoriesIds = $categoryRepository->findAllIdsByShopId($idShop);

        if (empty($categoriesIds)) {
            return true;
        }

        foreach ($categoriesIds as $idCategory) {

            if ($idRootCategory == $idCategory) {
                continue;
            }

            $category = new Category($idCategory, null, $idShop);
            $this->elasticsearchIndexer->indexCategory($category, $idShop);
        }

        return true;
    }

    /**
     * Create index for given shop if not exists
     *
     * @param int $idShop
     * @return bool TRUE if index exists or has been created or FALSE otherwise
     */
    private function createIndexIfNotExists($idShop)
    {
        if (!$this->elasticsearchIndexer->isCreatedIndex($idShop)) {
            if (!$this->elasticsearchIndexer->createIndex($idShop)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if product is valid for indexing
     *
     * @param Product $product
     *
     * @return bool
     */
    private function isValidProduct(Product $product)
    {
        if (!$product->active || !in_array($product->visibility, ['both', 'search'])) {
            return false;
        }

        return true;
    }
}
