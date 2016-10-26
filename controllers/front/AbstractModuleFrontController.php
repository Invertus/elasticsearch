<?php

use Invertus\Brad\Traits\GetServiceTrait;

abstract class AbstractModuleFrontController extends ModuleFrontController
{
    use GetServiceTrait;

    /** @var Brad */
    public $module;
}
