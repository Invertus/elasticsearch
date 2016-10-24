<?php

use Invertus\Brad\Exception\InvalidEntityException;
use Invertus\Brad\Exception\InvalidRepositoryException;

/**
 * Class AdminBradModuleController
 */
abstract class AbstractAdminBradModuleController extends ModuleAdminController
{
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
        if (!is_subclass_of($entityClassName, Core_Foundation_Database_EntityInterface::class)) {
            $message = sprintf(
                'Entity %s must implement %s interface',
                $entityClassName,
                Core_Foundation_Database_EntityInterface::class
            );
            throw new InvalidEntityException($message);
        }

        $repositoryClass = call_user_func([$entityClassName, 'getRepositoryClassName']);

        if (!$repositoryClass ||
            !is_subclass_of($repositoryClass, Core_Foundation_Database_EntityRepository::class)
        ) {
            $message = sprintf(
                'Repository %s must extend %s class',
                $repositoryClass,
                Core_Foundation_Database_EntityRepository::class
            );
            throw new InvalidRepositoryException($message);
        }

        $repository = $this->module->getEntityManager()
            ->getRepository($entityClassName);

        return $repository;
    }

    /**
     * Get service from container
     *
     * @param string $serviceName
     *
     * @return object
     */
    protected function get($serviceName)
    {
        return $this->module->getContainer()->get($serviceName);
    }

    /**
     * Initialize options
     */
    protected function initOptions()
    {
        //@todo: Override this method to initialize options
    }
}
