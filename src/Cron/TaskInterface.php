<?php

namespace Invertus\Brad\Cron;

/**
 * Interface TaskInterface
 *
 * @package Invertus\Brad\Cron
 */
interface TaskInterface
{
    /**
     * Runs task
     *
     * @param int $idShop
     */
    public function runTask($idShop);
}
