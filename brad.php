<?php

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

    /**
     * Front controllers
     */
    const FRONT_BRAD_SEARCH_CONTROLLER = 'search';

    /** @var \Invertus\Brad\Container\Container */
    private $container;

    /**
     * Brad constructor.
     */
    public function __construct()
    {
        $this->requireAutoloader();

        $this->name = 'brad';
        $this->tab = 'front_office_features';
        $this->version = '2.0.0-dev';
        $this->author = 'Invertus';
        $this->need_instance = 0;
        $this->controllers = [self::FRONT_BRAD_SEARCH_CONTROLLER];

        parent::__construct();

        $this->displayName = $this->l('BRAD');
        $this->description = $this->l('Elasticsearch® module for PrestaShop that makes search and filter significantly faster');
        $this->ps_versions_compliancy = ['min' => '1.6.1.0', 'max' => '1.6.2.0'];

        $this->container = \Invertus\Brad\Container\Container::build($this);
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
     * Get context
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
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

        $this->context->controller->addCSS($cssUri.'back/global.css');
    }

    /**
     * Add assets to FrontOffice header
     */
    public function hookDisplayHeader()
    {
        /** @var Core_Business_ConfigurationInterface $configuration */
        $configuration = $this->container->get('configuration');

        $isSearchEnalbed = (bool) $configuration->get(\Invertus\Brad\Config\Setting::ENABLE_SEARCH);
        if (!$isSearchEnalbed) {
            return;
        }

        $bradMinWordLength = (int) $configuration->get(\Invertus\Brad\Config\Setting::MINIMAL_SEARCH_WORD_LENGTH);
        $bradInstantSearchResultsCount = (int) $configuration->get(\Invertus\Brad\Config\Setting::INSTANT_SEARCH_RESULTS_COUNT);
        $bradSearchUrl = $this->context->link->getModuleLink($this->name, self::FRONT_BRAD_SEARCH_CONTROLLER);

        Media::addJsDef([
            '$globalBradMinWordLength' => $bradMinWordLength,
            '$globalBradInstantSearchResultsCount' => $bradInstantSearchResultsCount,
            '$globalBradSearchUrl' => $bradSearchUrl,
        ]);

        $this->context->controller->addCSS([
            $this->container->get('brad_css_uri').'front/global.css',
        ]);

        $this->context->controller->addJS([
            $this->container->get('brad_js_uri').'front/search.js',
        ]);
    }

    /**
     * Display search box if enabled
     *
     * @return string
     */
    public function hookDisplayTop()
    {
        /** @var Core_Business_ConfigurationInterface $configuration */
        $configuration = $this->container->get('configuration');

        $isSearchEnabled = (bool) $configuration->get(\Invertus\Brad\Config\Setting::ENABLE_SEARCH);
        if (!$isSearchEnabled) {
            return '';
        }

        $isFriendlyUrlEnabled = (bool) $configuration->get('PS_REWRITING_SETTINGS');
        $bradSearchUrl = $this->context->link->getModuleLink($this->name, self::FRONT_BRAD_SEARCH_CONTROLLER);

        $this->context->smarty->assign([
            'brad_search_url' => $bradSearchUrl,
            'is_friendly_url_enabled' => $isFriendlyUrlEnabled,
            'search_query' => Tools::getValue('search_query', ''),
        ]);

        return $this->context->smarty->fetch($this->container->get('brad_templates_dir').'hook/displayTop.tpl');
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
        /** @var \Invertus\Brad\Util\Validator $validator */
        $validator = $this->container->get('util.validator');

        if (!$validator->isProductValidForIndexing($product)) {
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
        /** @var \Invertus\Brad\Util\Validator $validator */
        $validator = $this->container->get('util.validator');

        if (!$elasticsearchIndexer->createIndex($this->context->shop->id)) {
            return;
        }

        if (!$validator->isProductValidForIndexing($product)) {
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
     * @return bool
     */
    public function isElasticsearchConnectionAvailable()
    {
        /** @var \Invertus\Brad\Service\Elasticsearch\ElasticsearchManager $manager */
        $manager = $this->container->get('elasticsearch.manager');
        if (!$manager->isConnectionAvailable()) {
            return false;
        }

        return true;
    }

    /**
     * Require autoloader
     */
    private function requireAutoloader()
    {
        require_once $this->getLocalPath().'vendor/autoload.php';
    }
}
