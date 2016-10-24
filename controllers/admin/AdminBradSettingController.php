<?php

use Invertus\Brad\Config\Setting;

/**
 * Class AdminBradSettingController
 */
class AdminBradSettingController extends AbstractAdminBradModuleController
{
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
                'buttons' => [
                    [
                        'title' => $this->l('Reindex all products'),
                        'name' => 'brad_reindex_all_products',
                        'icon' => 'process-icon-reset',
                        'type' => 'submit',
                    ],
                    [
                        'title' => $this->l('Reindex missing products'),
                        'name' => 'brad_reindex_missing_products',
                        'icon' => 'process-icon-refresh',
                        'type' => 'submit',
                    ],
                ],
            ],
            'search_settings' => [
                'title' => $this->l('Search settings'),
                'icon' => 'icon-search',
                'fields' => [
                    Setting::DISPLAY_SEARCH_INPUT => [
                        'title' => $this->l('Display search input'),
                        'hint' => $this->l('Search block in page top'),
                        'validation' => 'isBool',
                        'type' => 'bool',
                    ],
                    Setting::INSTANT_SEARCH => [
                        'title' => $this->l('Instant search'),
                        'hint' => $this->l('Instant search block under search input'),
                        'validation' => 'isBool',
                        'type' => 'bool',
                    ],
                    Setting::FUZZY_SEARCH => [
                        'title' => $this->l('Fuzzy search'),
                        'hint' => $this->l('Fuzzy search improves search results with misspelled words'),
                        'validation' => 'isBool',
                        'type' => 'bool',
                    ],
                    Setting::DISPLAY_DYNAMIC_SEARCH_RESULTS => [
                        'title' => $this->l('Display dynamic search results'),
                        'hint' => $this->l('Search results will appear in the page immediately as the user types in search input'),
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
     * Index products
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
