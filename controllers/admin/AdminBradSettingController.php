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

use Invertus\Brad\Config\Setting;
use Invertus\Brad\Service\Indexer;

/**
 * Class AdminBradSettingController
 */
class AdminBradSettingController extends AbstractAdminBradModuleController
{
    /**
     * Render options
     *
     * @return string
     */
    public function renderOptions()
    {
        /** @var \Invertus\Brad\Service\Elasticsearch\ElasticsearchManager $manager */
        $manager = $this->get('elasticsearch.manager');

        if ($manager->isConnectionAvailable()) {
            $indexedProductsCount = (int) $manager->getProductsCount($this->context->shop->id);

            /** @var \Invertus\Brad\Repository\ProductRepository $productRepository */
            $productRepository = $this->getRepository('BradProduct');
            $productsIds = $productRepository->findAllIdsByShopId($this->context->shop->id);

            $token = Tools::encrypt($this->module->name);
            $idShop = (int) $this->context->shop->id;
            $file = $this->module->getLocalPath().'brad.cron.php';

            $indexProductsCron =
                sprintf('php %s %s %s %s %s', $file, $token, 'index_products', $idShop, Indexer::INDEX_ALL_PRODUCTS);
            $indexMissingProductsCron =
                sprintf('php %s %s %s %s %s', $file, $token, 'index_products', $idShop, Indexer::INDEX_MISSING_PRODUCTS);
            $indexPrices =
                sprintf('php %s %s %s %s %s', $file, $token, 'index_products', $idShop, Indexer::INDEX_PRICES);

            $this->context->smarty->assign([
                'elasticsearch_connection_ok' => true,
                'elasticsearch_version' => $manager->getVersion(),
                'products_count' => count($productsIds),
                'indexed_products_count' => $indexedProductsCount,
                'index_all_cron' => $indexProductsCron,
                'index_missing_cron' => $indexMissingProductsCron,
                'index_prices_cron' => $indexPrices,
            ]);
        }

        $this->context->smarty->assign([
            'current_url' => $this->context->link->getAdminLink(Brad::ADMIN_BRAD_SETTING_CONTROLLER),
        ]);

        $templatesDir = $this->get('brad_templates_dir');
        $elasticsearchActions = $this->context->smarty->fetch($templatesDir.'admin/elasticsearch-actions.tpl');

        return $elasticsearchActions.parent::renderOptions();
    }

    /**
     * Index products if submited or execute default postProcess()
     *
     * @return bool
     */
    public function postProcess()
    {
        if (Tools::isSubmit('brad_reindex_all_products')) {
            $this->processIndexing(Indexer::INDEX_ALL_PRODUCTS);
            return true;
        }

        if (Tools::isSubmit('brad_reindex_missing_products')) {
            $this->processIndexing(Indexer::INDEX_MISSING_PRODUCTS);
            return true;
        }

        if (Tools::isSubmit('brad_reindex_prices')) {
            $this->processIndexing(Indexer::INDEX_PRICES);
            return true;
        }

        return parent::postProcess();
    }

    /**
     * {@inheritDoc}
     */
    protected function initOptions()
    {
        $this->fields_options = [
            'elasticsearch_settings' => [
                'title' => $this->l('Elasticsearch settings'),
                'icon' => 'icon-cogs',
                'fields' => [
                    Setting::ELASTICSEARCH_HOST_1 => [
                        'title' => $this->l('Elasticsearch server host'),
                        'hint' => $this->l('URL:PORT'),
                        'validation' => 'isUrl',
                        'type' => 'text',
                        'required' => true,
                        'class' => 'fixed-width-xxl',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
            'search_settings' => [
                'title' => $this->l('Search settings'),
                'icon' => 'icon-search',
                'fields' => [
                    Setting::ENABLE_SEARCH => [
                        'title' => $this->l('Enable search'),
                        'hint' => $this->l('Display search input page top'),
                        'validation' => 'isBool',
                        'type' => 'bool',
                    ],
                    Setting::FUZZY_SEARCH => [
                        'title' => $this->l('Fuzzy search'),
                        'hint' => $this->l('Fuzzy search improves search results with typos in serach query'),
                        'validation' => 'isBool',
                        'type' => 'bool',
                    ],
                    Setting::DISPLAY_DYNAMIC_SEARCH_RESULTS => [
                        'title' => $this->l('Display dynamic search results'),
                        'hint' => $this->l('Search results will appear in the page immediately as the user types in search input'),
                        'validation' => 'isBool',
                        'type' => 'bool',
                    ],
                    Setting::INSTANT_SEARCH => [
                        'title' => $this->l('Display instant search results'),
                        'hint' => $this->l('Instant search block under search input'),
                        'validation' => 'isBool',
                        'type' => 'bool',
                    ],
                    Setting::INSTANT_SEARCH_RESULTS_COUNT => [
                        'title' => $this->l('Instant search results count'),
                        'hint' => $this->l('Number of records in search results list under search input'),
                        'validation' => 'isUnsignedInt',
                        'type' => 'text',
                        'class' => 'fixed-width-sm',
                        'suffix' => $this->l('results'),
                    ],
                    Setting::MINIMAL_SEARCH_WORD_LENGTH => [
                        'title' => $this->l('Min. word length'),
                        'hint' => $this->l('Number of symbols typed into search input when instant search starts working'),
                        'validation' => 'isUnsignedInt',
                        'type' => 'text',
                        'class' => 'fixed-width-sm',
                        'suffix' => $this->l('symbols'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Index products & categories
     *
     * @param string $indexingType
     */
    private function processIndexing($indexingType)
    {
        /** @var \Invertus\Brad\Service\Elasticsearch\ElasticsearchManager $manager */
        $manager = $this->get('elasticsearch.manager');

        if (!$manager->isConnectionAvailable()) {
            $this->errors[] = $this->l('Cannot establish Elasticsearch connection');
            return;
        }

        if (!BradShop::isSingleShopContext()) {
            $this->errors[] = $this->l('Products must be indexed in single shop context');
            return;
        }

        /** @var Indexer $indexer */
        $indexer = $this->get('indexer');
        $hasSuccessfullyIndexed = $indexer->performIndexing($this->context->shop->id, $indexingType);

        if ($hasSuccessfullyIndexed) {
            $indexedProductsCount = $indexer->getIndexedProductsCount();
            if ($indexedProductsCount) {
                $this->confirmations[] = $this->l(sprintf('Successfully indexed %d products', $indexedProductsCount));
            } else {
                $this->informations[] = $this->l('No new products have been indexed.');
            }
            return;
        }

        $this->errors[] = $this->l('Failed to index products');
    }
}
