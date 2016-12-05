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
     * @var string
     */
    private $bradViewsDir;

    /**
     * TemplateBuilder constructor.
     *
     * @param Context $context
     * @param string $bradViewsDir
     */
    public function __construct(Context $context, $bradViewsDir)
    {
        $this->context = $context;
        $this->bradViewsDir = $bradViewsDir;
    }

    /**
     * Build filters html template
     *
     * @param array $filterData
     *
     * @return string
     */
    public function buildFiltersTemplate(array $filterData)
    {
        $this->context->smarty->assign([
            'filters' => $filterData,
        ]);

        return $this->context->smarty->fetch($this->bradViewsDir.'front/filter-template.tpl');
    }

    /**
     * Build filter results html template
     *
     * @param array $filterResults
     *
     * @return string
     */
    public function buildResultsTemplate(array $filterResults)
    {
        //@todo: implement results builder
    }
}
