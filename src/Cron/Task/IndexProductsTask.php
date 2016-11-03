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
