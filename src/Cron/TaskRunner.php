<?php

namespace Invertus\Brad\Cron;

use BradShop;
use Exception;
use Invertus\Brad\Container\Container;

/**
 * Class TaskRunner
 *
 * @package Invertus\Brad\Cron
 */
class TaskRunner
{
    /**
     * @var Container
     */
    private $container;

    /**
     * TaskRunner constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Run given task
     *
     * @param string $taskName
     * @param int $idShop
     *
     * @throws Exception
     */
    public function run($taskName, $idShop)
    {
        $availableTasks = $this->getAvailableTasks();

        if (!in_array($taskName, $availableTasks)) {
            throw new Exception(sprintf('Task "%s" does not exist.', $taskName));
        }

        if (!in_array($idShop, BradShop::getCompleteListOfShopsID())) {
            throw new Exception('Shop with id "%s" does not exist');
        }

        $task = $this->container->get('task.'.$taskName);

        if (!$task instanceof TaskInterface) {
            throw new Exception(sprintf('Task "%s" must be instance of TaskInterface', $taskName));
        }

        $task->runTask($idShop);
    }

    /**
     * List of registered tasks
     *
     * @return array
     */
    private function getAvailableTasks()
    {
        return [
            'index_products',
        ];
    }
}
