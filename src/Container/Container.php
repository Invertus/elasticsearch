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

namespace Invertus\Brad\Container;

use Adapter_ServiceLocator;
use Brad;
use Elasticsearch\ClientBuilder;
use Invertus\Brad\Config\Setting;
use Invertus\Brad\Cron\Task\IndexProductsTask;
use Invertus\Brad\Cron\TaskRunner;
use Invertus\Brad\Install\Installer;
use Invertus\Brad\Service\Elasticsearch\Builder\DocumentBuilder;
use Invertus\Brad\Service\Elasticsearch\Builder\IndexBuilder;
use Invertus\Brad\Service\Elasticsearch\Builder\SearchQueryBuilder;
use Invertus\Brad\Service\Elasticsearch\ElasticsearchIndexer;
use Invertus\Brad\Service\Elasticsearch\ElasticsearchManager;
use Invertus\Brad\Service\Elasticsearch\ElasticsearchSearch;
use Invertus\Brad\Service\Indexer;
use Invertus\Brad\Util\Validator;
use Pimple\Container as Pimple;

/**
 * Class Container Simple container for module dependencies
 *
 * @package Invertus\Brad\Container
 */
class Container
{
    /**
     * @var Pimple
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
        $this->container['container'] = function () {
            return $this;
        };

        $this->container['em'] = function () {
            return Adapter_ServiceLocator::get('Core_Foundation_Database_EntityManager');
        };

        $this->container['configuration'] = function () {
            return Adapter_ServiceLocator::get('Core_Business_ConfigurationInterface');
        };

        $this->container['context'] = function () {
            return $this->module->getContext();
        };

        $this->container['installer'] = function () {
            return new Installer($this->module);
        };

        $this->container['elasticsearch.client'] = function ($c) {
            $elasticsearchHost1 = $c['configuration']->get(Setting::ELASTICSEARCH_HOST_1);

            if (false === strpos($elasticsearchHost1, 'http://') &&
                false === strpos($elasticsearchHost1, 'https://')
            ) {
                $elasticsearchHost1 = 'http://'.$elasticsearchHost1;
            }

            $hosts = [$elasticsearchHost1];

            $clientBuilder = ClientBuilder::create();
            $clientBuilder->setHosts($hosts);
            $client = $clientBuilder->build();

            return $client;
        };

        $this->container['elasticsearch.manager'] = function ($c) {
            $elasticsearchIndexPrefix = $c['configuration']->get(Setting::INDEX_PREFIX);

            return new ElasticsearchManager($c['elasticsearch.client'], $elasticsearchIndexPrefix);
        };

        $this->container['util.validator'] = function () {
            return new Validator();
        };

        $this->container['elasticsearch.indexer'] = function ($c) {
            return new ElasticsearchIndexer(
                $c['elasticsearch.manager'],
                $c['elasticsearch.builder.document_builder'],
                $c['elasticsearch.builder.index_builder']
            );
        };

        $this->container['elasticsearch.builder.document_builder'] = function ($c) {
            return new DocumentBuilder($c['context']->link, $c['context']->shop, $c['em'], $c['configuration']);
        };

        $this->container['elasticsearch.builder.index_builder'] = function ($c) {
            return new IndexBuilder($c['configuration'], $c['em']);
        };

        $this->container['indexer'] = function ($c) {
            return new Indexer(
                $c['elasticsearch.indexer'],
                $c['em'],
                $c['configuration'],
                $c['elasticsearch.builder.document_builder'],
                $c['util.validator']
            );
        };

        $this->container['elasticsearch.builder.search_query_builder'] = function ($c) {
            return new SearchQueryBuilder($c['configuration'], $c['context']);
        };

        $this->container['elasticsearch.search'] = function ($c) {
            return new ElasticsearchSearch($c['elasticsearch.manager']);
        };

        $this->container['task_runner'] = function ($c) {
            return new TaskRunner($c['container']);
        };

        $this->container['task.index_products'] = function ($c) {
            return new IndexProductsTask($c['elasticsearch.manager'], $c['indexer']);
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

        $this->container['brad_js_uri'] = function ($c) {
            return $c['brad_uri'].'views/js/';
        };

        $this->container['brad_templates_dir'] = function ($c) {
            return $c['brad_dir'].'views/templates/';
        };
    }
}
