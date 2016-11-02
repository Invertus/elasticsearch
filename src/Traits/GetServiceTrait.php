<?php

namespace Invertus\Brad\Traits;

/**
 * Class GetServiceTrait
 *
 * @package Invertus\Brad\Traits
 */
trait GetServiceTrait
{
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
}
