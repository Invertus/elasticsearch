<?php

namespace Invertus\Brad\Service\Builder;
use Context;

/**
 * Class TemplateBuilder
 *
 * @package Invertus\Brad\Service\Builder
 */
class TemplateBuilder
{
    /**
     * @var Context
     */
    private $context;

    /**
     * TemplateBuilder constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function buildFilters(array $filterData)
    {
        //@todo: implement filters template builder
    }

    public function buildResults(array $filterResults)
    {
        //@todo: implement results builder
    }
}
