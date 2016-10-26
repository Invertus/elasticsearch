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
     * Add autoloader for all controllers
     */
    public function hookModuleRoutes()
    {
        $this->requireAutoloader();
    }

    /**
     * Add JS & CSS to BackOffice header
     */
    public function hookDisplayBackOfficeHeader()
    {
        $cssUri = $this->container->get('brad_css_uri');

        $this->context->controller->addCSS($cssUri.'back/global.css');
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
