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
