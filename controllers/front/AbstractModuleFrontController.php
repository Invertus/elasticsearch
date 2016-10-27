<?php

use Invertus\Brad\Traits\GetServiceTrait;

abstract class AbstractModuleFrontController extends ModuleFrontController
{
    /**
     * Let's controller get services from container
     */
    use GetServiceTrait;

    /**
     * @var Brad
     */
    public $module;
}
