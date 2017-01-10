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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Brad
 */
class Brad extends Module
{
    /**
     * Admin controllers
     */
    const ADMIN_BRAD_MODULE_CONTROLLER = 'AdminBradModule';
    const ADMIN_BRAD_SETTING_CONTROLLER = 'AdminBradSetting';
    const ADMIN_BRAD_ADVANCED_SETTING_CONTROLLER = 'AdminBradAdvancedSetting';
    const ADMIN_BRAD_INFO_CONTROLLER = 'AdminBradInfo';
    const ADMIN_BRAD_FILTER_CONTROLLER = 'AdminBradFilter';
    const ADMIN_BRAD_FILTER_TEMPLATE_CONTROLLER = 'AdminBradFilterTemplate';

    /**
     * Front controllers
     */
    const FRONT_BRAD_SEARCH_CONTROLLER = 'search';
    const FRONT_BRAD_FILTER_CONTROLLER = 'filter';

    /**
     * @var \Invertus\Brad\Container\Container
     */
    private $container;

    /**
     * Brad constructor.
     */
    public function __construct()
    {
        $this->name = 'brad';
        $this->tab = 'front_office_features';
        $this->version = '2.0.0';
        $this->author = 'Invertus';
        $this->need_instance = 0;
        $this->controllers = [self::FRONT_BRAD_SEARCH_CONTROLLER, self::FRONT_BRAD_FILTER_CONTROLLER];

        parent::__construct();

        $this->requireAutoloader();

        $this->displayName = $this->l('BRAD');
        $this->description = $this->l('Elasticsearch® module for PrestaShop that makes search and filter significantly faster.');
        $this->ps_versions_compliancy = ['min' => '1.6.1.0', 'max' => '1.6.2.0'];

        $this->container = new \Invertus\Brad\Container\Container($this);
    }

    /**
     * Redirect user to default controller
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink(self::ADMIN_BRAD_SETTING_CONTROLLER));
    }

    /**
     * Get container
     *
     * @return \Invertus\Brad\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Module installation
     *
     * @return bool
     */
    public function install()
    {
        /** @var \Invertus\Brad\Install\Installer $installer */
        $installer = $this->container->get('installer');

        if (!parent::install() || !$installer->install()) {
            return false;
        }

        return true;
    }

    /**
     * Uninstall module
     *
     * @return bool
     */
    public function uninstall()
    {
        /** @var \Invertus\Brad\Install\Installer $installer */
        $installer = $this->container->get('installer');

        if (!$installer->uninstall() || !parent::uninstall()) {
            return false;
        }

        return true;
    }

    /**
     * Run given task
     *
     * @param string $task
     * @param int $idShop
     */
    public function runTask($task, $idShop)
    {
        /** @var \Invertus\Brad\Cron\TaskRunner $taskRunner */
        $taskRunner = $this->container->get('task_runner');

        try {
            $taskRunner->run($task, $idShop);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Add assets to BackOffice header
     */
    public function hookDisplayBackOfficeHeader()
    {
        $cssUri = $this->container->get('brad_css_uri');

        $this->context->controller->addCSS($cssUri.'admin/global.css');
    }

    /**
     * Add assets to FrontOffice header
     */
    public function hookDisplayHeader()
    {
        if (!$this->isElasticsearchConnectionAvailable(true)) {
            return;
        }

        $isFiltersEnalbed = (bool) Configuration::get(\Invertus\Brad\Config\Setting::ENABLE_FILTERS);

        if ($isFiltersEnalbed && $this->isFilterAvailableInController()) {
            $jsUri = $this->container->get('brad_js_uri');
            $this->context->controller->addJqueryUI('ui.slider');
            $this->context->controller->addJS($jsUri.'front/filter.js');

            $bradFilterUrl = $this->context->link->getModuleLink($this->name, self::FRONT_BRAD_FILTER_CONTROLLER);

            Media::addJsDef([
                '$globalBradFilterUrl' => $bradFilterUrl,
                '$globalBaseUrl'       => $this->context->link->getCategoryLink(Tools::getValue('id_category')),
                '$globalIdCategory'    => (int) Tools::getValue('id_category'),
            ]);
        }

        $isSearchEnalbed = (bool) Configuration::get(\Invertus\Brad\Config\Setting::ENABLE_SEARCH);
        if ($isSearchEnalbed) {
            $bradMinWordLength = (int) Configuration::get(\Invertus\Brad\Config\Setting::MINIMAL_SEARCH_WORD_LENGTH);
            $bradInstantSearchResultsCount = (int) Configuration::get(\Invertus\Brad\Config\Setting::INSTANT_SEARCH_RESULTS_COUNT);
            $bradSearchUrl = $this->context->link->getModuleLink($this->name, self::FRONT_BRAD_SEARCH_CONTROLLER);

            Media::addJsDef([
                '$globalBradMinWordLength' => $bradMinWordLength,
                '$globalBradInstantSearchResultsCount' => $bradInstantSearchResultsCount,
                '$globalBradSearchUrl' => $bradSearchUrl,
            ]);

            $this->context->controller->addJS([
                $this->container->get('brad_js_uri').'front/search.js',
            ]);
        }

        if ($isFiltersEnalbed || $isSearchEnalbed) {
            $this->context->controller->addCSS([
                $this->container->get('brad_css_uri').'front/global.css',
            ]);
        }
    }

    /**
     * Display search box if search is enabled and Elasticsearch service is accessible
     *
     * @return string
     */
    public function hookDisplayTop()
    {
        if (!$this->isElasticsearchConnectionAvailable()) {
            return '';
        }

        $isSearchEnabled = (bool) Configuration::get(\Invertus\Brad\Config\Setting::ENABLE_SEARCH);
        if (!$isSearchEnabled) {
            return '';
        }

        $isFriendlyUrlEnabled = (bool) Configuration::get('PS_REWRITING_SETTINGS');
        $bradSearchUrl = $this->context->link->getModuleLink($this->name, self::FRONT_BRAD_SEARCH_CONTROLLER);

        $this->context->smarty->assign([
            'brad_search_url'         => $bradSearchUrl,
            'is_friendly_url_enabled' => $isFriendlyUrlEnabled,
            'search_query'            => Tools::getValue('search_query', ''),
        ]);

        return $this->context->smarty->fetch($this->container->get('brad_templates_dir').'hook/displayTop.tpl');
    }

    /**
     * Display filters and handle filtering
     */
    public function hookDisplayLeftColumn()
    {
        if (!$this->isElasticsearchConnectionAvailable(true)) {
            return '';
        }

        $isFiltersEnalbed = (bool) Configuration::get(\Invertus\Brad\Config\Setting::ENABLE_FILTERS);

        if (!$this->isFilterAvailableInController() || !$isFiltersEnalbed) {
            return '';
        }

        $urlParser = new \Invertus\Brad\Service\UrlParser();
        $urlParser->parse($_GET);
        $selectedFilters = $urlParser->getSelectedFilters();

        $orderWay   = $urlParser->getOrderWay();
        $orderBy    = $urlParser->getOrderBy();
        $page       = $urlParser->getPage();
        $n          = $urlParser->getSize();
        $idCategory = $urlParser->getIdCategory();

        $filterData = new \Invertus\Brad\DataType\FilterData();
        $filterData->setSize($n);
        $filterData->setPage($page);
        $filterData->setOrderWay($orderWay);
        $filterData->setOrderBy($orderBy);
        $filterData->setIdCategory($idCategory);
        $filterData->setSelectedFilters($selectedFilters);
        $filterData->initFilters();

        /** @var \Invertus\Brad\Service\FilterService $filterService */
        $filterService        = $this->getContainer()->get('filter_service');
        $productsAggregations = $filterService->aggregateProducts($filterData);

        /** @var \Invertus\Brad\Template\Templating $templating */
        $templating      = $this->container->get('templating');
        $filtersTemplate = $templating->renderFiltersBlockTemplate($filterData, $productsAggregations);

        $loader = $this->context->smarty->fetch($this->getLocalPath().'views/templates/front/loader.tpl');

        return $filtersTemplate.$loader;
    }

    /**
     * Index product when added
     *
     * @param array $params
     */
    public function hookActionObjectProductAddAfter($params)
    {
        $product = $params['object'];

        if (!$product instanceof Product) {
            return;
        }

        if (!$this->isElasticsearchConnectionAvailable()) {
            return;
        }

        /** @var \Invertus\Brad\Service\Elasticsearch\ElasticsearchIndexer $elasticsearchIndexer */
        $elasticsearchIndexer = $this->container->get('elasticsearch.indexer');

        if (!$product->active || $product->visibility == 'none') {
            return;
        }

        if (!$elasticsearchIndexer->createIndex($this->context->shop->id)) {
            return;
        }

        $elasticsearchIndexer->indexProduct($product, $this->context->shop->id);
    }

    /**
     * Index product when updated
     *
     * @param array $params
     */
    public function hookActionObjectProductUpdateAfter($params)
    {
        $product = $params['object'];

        if (!$product instanceof Product) {
            return;
        }

        if (!$this->isElasticsearchConnectionAvailable()) {
            return;
        }

        /** @var \Invertus\Brad\Service\Elasticsearch\ElasticsearchIndexer $elasticsearchIndexer */
        $elasticsearchIndexer = $this->container->get('elasticsearch.indexer');

        if (!$elasticsearchIndexer->createIndex($this->context->shop->id)) {
            return;
        }

        if (!$product->active || $product->visibility == 'none') {
            $elasticsearchIndexer->deleteProduct($product, $this->context->shop->id);
            return;
        }

        $elasticsearchIndexer->indexProduct($product, $this->context->shop->id);
    }

    /**
     * Delete product from Elasticsearch when product is deleted
     *
     * @param array $params
     */
    public function hookActionObjectProductDeleteAfter($params)
    {
        $product = $params['object'];

        if (!$product instanceof Product) {
            return;
        }

        if (!$this->isElasticsearchConnectionAvailable()) {
            return;
        }

        /** @var \Invertus\Brad\Service\Elasticsearch\ElasticsearchIndexer $elasticsearchIndexer */
        $elasticsearchIndexer = $this->container->get('elasticsearch.indexer');

        if (!$elasticsearchIndexer->createIndex($this->context->shop->id)) {
            return;
        }

        $elasticsearchIndexer->deleteProduct($product, $this->context->shop->id);
    }

    /**
     * Check if elsticseach connection is available
     *
     * @param bool $checkIndex Check if index is created
     *
     * @return bool
     */
    public function isElasticsearchConnectionAvailable($checkIndex = false)
    {
        static $indexStatus;

        /** @var \Invertus\Brad\Service\Elasticsearch\ElasticsearchManager $manager */
        $manager = $this->container->get('elasticsearch.manager');

        $isConnAvailable = $manager->isConnectionAvailable();
        if (!$isConnAvailable) {
            return false;
        }

        if ($checkIndex) {
            if (isset($indexStatus)) {
                $isIndexCreated = $indexStatus;
            } else {
                $isIndexCreated = $manager->isIndexCreated($this->context->shop->id);
                $indexStatus = $isIndexCreated;
            }

            if (!$isIndexCreated) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if filtering is available in controller
     */
    private function isFilterAvailableInController()
    {
        $availableControllers = ['category'];
        $currentController = Tools::getValue('controller');

        return in_array($currentController, $availableControllers);
    }

    /**
     * Require autoloader
     */
    private function requireAutoloader()
    {
        require_once $this->getLocalPath().'vendor/autoload.php';
    }
}
