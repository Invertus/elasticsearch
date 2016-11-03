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

use Invertus\Brad\Exception\InvalidEntityException;
use Invertus\Brad\Exception\InvalidRepositoryException;
use Invertus\Brad\Traits\GetServiceTrait;

/**
 * Class AdminBradModuleController
 */
abstract class AbstractAdminBradModuleController extends ModuleAdminController
{
    use GetServiceTrait;

    /**
     * @var bool
     */
    public $bootstrap = true;

    /**
     * @var Brad
     */
    public $module;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->initOptions();

        parent::init();
    }

    /**
     * Get repository for given entity
     *
     * @param string $entityClassName Entity class name
     *
     * @return Core_Foundation_Database_EntityRepository
     *
     * @throws InvalidEntityException|InvalidRepositoryException
     */
    protected function getRepository($entityClassName)
    {
        if (!is_subclass_of($entityClassName, 'Core_Foundation_Database_EntityInterface')) {
            $message = sprintf(
                'Entity %s must implement %s interface',
                $entityClassName,
                'Core_Foundation_Database_EntityInterface'
            );
            throw new InvalidEntityException($message);
        }

        $repositoryClass = call_user_func([$entityClassName, 'getRepositoryClassName']);

        if (!$repositoryClass ||
            !is_subclass_of($repositoryClass, 'Core_Foundation_Database_EntityRepository')
        ) {
            $message = sprintf(
                'Repository %s must extend %s class',
                $repositoryClass,
                'Core_Foundation_Database_EntityRepository'
            );
            throw new InvalidRepositoryException($message);
        }

        $repository = $this->get('em')
            ->getRepository($entityClassName);

        return $repository;
    }

    /**
     * Initialize options
     */
    protected function initOptions()
    {
        //@todo: Override this method to initialize options
    }
}
