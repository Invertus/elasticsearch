<?php

namespace Invertus\Brad\Cron\Task;

use BradShop;
use Exception;
use Invertus\Brad\Cron\TaskInterface;
use Invertus\Brad\Service\Elasticsearch\ElasticsearchManager;
use Invertus\Brad\Service\Indexer;
use Tools;

/**
 * Class IndexProducts
 *
 * @package Invertus\Brad\Cron\Task
 */
class IndexProductsTask implements TaskInterface
{
    /**
     * @var ElasticsearchManager
     */
    private $manager;

    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * IndexProductsTask constructor.
     *
     * @param ElasticsearchManager $manager
     * @param Indexer $indexer
     */
    public function __construct(ElasticsearchManager $manager, Indexer $indexer)
    {
        $this->manager = $manager;
        $this->indexer = $indexer;
    }

    /**
     * Runs task
     *
     * @param int $idShop
     *
     * @return bool
     *
     * @throws Exception
     */
    public function runTask($idShop)
    {
        if (!$this->manager->isConnectionAvailable()) {
            throw new Exception('Cannot establish Elasticsearch connection');
        }

        if (!BradShop::isSingleShopContext()) {
            throw new Exception('Products must be indexed in single shop context');
        }

        $action = Tools::getValue('action');
        if (!in_array($action, [Indexer::INDEX_ALL_PRODUCTS, Indexer::INDEX_PRICES, Indexer::INDEX_MISSING_PRODUCTS])) {
            throw new Exception(sprintf('Action "%s" does not exist'), $action);
        }

        $hasSuccessfullyIndexed = $this->indexer->performIndexing($idShop, $action);
        if (!$hasSuccessfullyIndexed) {
            throw new Exception('Failed to index products');
        }

        $indexedProductsCount = $this->indexer->getIndexedProductsCount();
        if ($indexedProductsCount) {
            echo sprintf('Successfully indexed %s products', $indexedProductsCount);
        } else {
            echo 'No products have been indexed';
        }

        return true;
    }
}
