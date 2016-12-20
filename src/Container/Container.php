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
use Db;
use Invertus\Brad\Exception\ServiceNotFoundException;
use Pimple\Container as Pimple;
use ReflectionClass;

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
        $this->initParameters();
        $this->initDependencies();
    }

    /**
     * Build container
     * @todo remove me
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

        $this->container['module'] = function () {
            return $this->module;
        };

        $this->container['db'] = function () {
            return Db::getInstance();
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

        $this->container['context.link'] = function ($c) {
            return $c['context']->link;
        };

        $this->container['context.shop'] = function ($c) {
            return $c['context']->shop;
        };

        $this->initCustomDependencies();
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

        $this->container['brad_img_uri'] = function ($c) {
            return $c['brad_uri'].'views/img/';
        };

        $this->container['brad_log_dir'] = function ($c) {
            return $c['brad_dir'].'logs/';
        };
    }

    /**
     * Init custom services defined in /config/services.php
     *
     * @throws ServiceNotFoundException
     */
    private function initCustomDependencies()
    {
        $servicesConfigFile = $this->container['brad_dir'].'config/services.php';

        if (!file_exists($servicesConfigFile)) {
            return;
        }

        $services = require_once $servicesConfigFile;

        if (!is_array($services) || empty($services)) {
            return;
        }

        foreach ($services as $serviceName => $service) {
            if (!class_exists($service['class'])) {
                $msg = sprintf('Class %s for service %s was not found', $service['class'], $serviceName);
                throw new ServiceNotFoundException($msg);
            }

            $this->container[$serviceName] = function ($container) use ($service) {

                $args = [];
                if (isset($service['arguments']) && is_array($service['arguments'])) {
                    foreach ($service['arguments'] as $argument) {
                        if (0 === strpos($argument, '@')) {
                            $configurationName = ltrim($argument, '@');
                            $args[] = $container['configuration']->get($configurationName);
                        } elseif (isset($container[$argument])) {
                            $args[] = $container[$argument];
                        } else {
                            $args[] = $argument;
                        }
                    }
                }

                $reflection = new ReflectionClass($service['class']);
                $serviceInstance = $reflection->newInstanceArgs($args);

                if (isset($service['call']['method'])) {
                    $method = $service['call']['method'];
                    $isFactory = $service['call']['factory'] ?: false;

                    if ($reflection->hasMethod($method)) {
                        $result = $serviceInstance->$method();

                        if ($isFactory) {
                            return $result;
                        }
                    }
                }

                return $serviceInstance;
            };
        }
    }
}
