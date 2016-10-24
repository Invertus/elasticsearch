<?php

namespace Invertus\Brad\Container;

use Adapter_ServiceLocator as ServiceLocator;
use Brad;
use Core_Business_ConfigurationInterface as ConfigurationInterface;
use Core_Foundation_Database_EntityManager as EntityManager;
use Elasticsearch\ClientBuilder;
use Invertus\Brad\Config\Setting;
use Invertus\Brad\Install\Installer;
use Invertus\Brad\Service\Elasticsearch\Builder\DocumentBuilder;
use Invertus\Brad\Service\Elasticsearch\Builder\IndexBuilder;
use Invertus\Brad\Service\Elasticsearch\ElasticsearchIndexer;
use Invertus\Brad\Service\Elasticsearch\ElasticsearchManager;
use Invertus\Brad\Service\Indexer;
use Pimple\Container as Pimple;

/**
 * Class Container Simple container for module dependencies
 *
 * @package Invertus\Brad\DI
 */
class Container
{
    /**
     * @var DIContainer
     */
    private $container;

    /**
     * @var Brad
     */
    private $module;

    /**
     * Container constructor.
     *
     * @param Brad $module
     */
    public function __construct(Brad $module)
    {
        $this->module = $module;

        $this->initContainer();
        $this->initDependencies();
        $this->initParameters();
    }

    /**
     * Build container
     *
     * @param Brad $module
     *
     * @return Container
     */
    public static function build(Brad $module)
    {
        $container = new Container($module);

        return $container;
    }

    /**
     * Get service by name
     *
     * @param string $serviceName
     *
     * @return object
     */
    public function get($serviceName)
    {
        return $this->container[$serviceName];
    }

    /**
     * Initialize container
     */
    private function initContainer()
    {
        $this->container = new Pimple();
    }

    /**
     * Initialize container dependencies
     */
    private function initDependencies()
    {
        $this->container['em'] = function () {
            return ServiceLocator::get(EntityManager::class);
        };

        $this->container['configuration'] = function () {
            return ServiceLocator::get(ConfigurationInterface::class);
        };

        $this->container['installer'] = function () {
            return new Installer($this->module);
        };

        $this->container['es.manager'] = function ($c) {

            $elasticsearchHost1 = $c['configuration']->get(Setting::ELASTICSEARCH_HOST_1);
            $elasticsearchIndexPrefix = $c['configuration']->get(Setting::INDEX_PREFIX);

            $hosts = [
                $elasticsearchHost1,
            ];

            $clientBuilder = ClientBuilder::create();
            $clientBuilder->setHosts($hosts);
            $client = $clientBuilder->build();

            return new ElasticsearchManager($client, $elasticsearchIndexPrefix);
        };

        $this->container['es.indexer'] = function ($c) {
            return new ElasticsearchIndexer(
                $c['es.manager'],
                $c['es.builder.document_builder'],
                $c['es.builder.index_builder']
            );
        };

        $this->container['es.builder.document_builder'] = function () {
            return new DocumentBuilder();
        };

        $this->container['es.builder.index_builder'] = function () {
            return new IndexBuilder();
        };

        $this->container['indexer'] = function ($c) {
            return new Indexer($c['es.indexer'], $c['em']);
        };
    }

    /**
     * Initialize parameters
     */
    private function initParameters()
    {
        $this->container['brad_dir'] = function () {
            return $this->module->getLocalPath();
        };

        $this->container['brad_uri'] = function () {
            return $this->module->getPathUri();
        };

        $this->container['brad_css_uri'] = function ($c) {
            return $c['brad_uri'].'views/css/';
        };
    }
}
