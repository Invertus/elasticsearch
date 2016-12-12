<?php

namespace Invertus\Brad\Service\Builder;

use Context;
use Image;
use ImageType;

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
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * TemplateBuilder constructor.
     *
     * @param Context $context
     * @param string $bradViewsDir
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(Context $context, $bradViewsDir, FilterBuilder $filterBuilder)
    {
        $this->context = $context;
        $this->bradViewsDir = $bradViewsDir;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Build filters html template
     *
     * @param array $selectedFilters
     *
     * @return string
     */
    public function buildFiltersTemplate(array $selectedFilters)
    {
        $this->filterBuilder->build($selectedFilters);

        $this->context->smarty->assign([
            'filters' => $this->filterBuilder->getBuiltFilters(),
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
        if (empty($filterResults)) {
            return null;
        }

        $frontController = Context::getContext()->controller;
        $frontController->addColorsToProductList($filterResults);

        $this->context->smarty->assign([
            'products' => $filterResults,
            'search_products' => $filterResults,
            'nbProducts' => 50,
            'homeSize' => Image::getSize(ImageType::getFormatedName('home')),
            'current_url' => '',
            'request' => '',
        ]);

        $renderedList = $this->context->smarty->fetch(_PS_THEME_DIR_.'search.tpl');

        return $renderedList;
    }
}
