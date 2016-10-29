<?php

use Invertus\Brad\Config\Setting;

/**
 * Class AdminBradSettingController
 */
class AdminBradSettingController extends AbstractAdminBradModuleController
{
    public function renderOptions()
    {
        if ($this->module->isElasticsearchConnectionAvailable()) {

            /** @var \Invertus\Brad\Service\Elasticsearch\ElasticsearchManager $manager */
            $manager = $this->get('elasticsearch.manager');
            $indexedProductsCount = (int) $manager->getProductsCount($this->context->shop->id);

            /** @var \Invertus\Brad\Repository\ProductRepository $productRepository */
            $productRepository = $this->getRepository('BradProduct');
            $productsIds = $productRepository->findAllIdsByShopId($this->context->shop->id);

            $this->context->smarty->assign([
                'elasticsearch_connection_ok' => true,
                'elasticsearch_version' => $manager->getVersion(),
                'products_count' => count($productsIds),
                'indexed_products_count' => $indexedProductsCount,
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
            $this->processIndexing();
            return true;
        }

        if (Tools::isSubmit('brad_reindex_missing_products')) {
            $indexOnlyMissingProducts = true;
            $this->processIndexing($indexOnlyMissingProducts);
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
     * @param bool $indexOnlyMissingProducts
     */
    private function processIndexing($indexOnlyMissingProducts = false)
    {
        if (!$this->module->isElasticsearchConnectionAvailable()) {
            $this->errors[] = $this->l('Cannot establish Elasticsearch connection');
            return;
        }

        if (!BradShop::isSingleShopContext()) {
            $this->errors[] = $this->l('Products must be indexed in single shop context');
            return;
        }

        /** @var \Invertus\Brad\Service\Indexer $indexer */
        $indexer = $this->get('indexer');
        $hasSuccessfullyIndexed = $indexer->performIndexing($this->context->shop->id, $indexOnlyMissingProducts);

        if ($hasSuccessfullyIndexed) {
            $indexedProdductsCount = $indexer->getIndexedProductsCount();
            $this->confirmations[] = $this->l(sprintf('Successfully indexed %d products', $indexedProdductsCount));
            return;
        }

        $this->errors[] = $this->l('Failed to index products');
    }
}
