<?php

namespace Invertus\Brad\Service\Builder;

use Configuration;
use Context;
use Image;
use ImageType;
use Tools;

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
     * @param string $bradViewsDir
     * @param FilterBuilder $filterBuilder
     */
    public function __construct($bradViewsDir, FilterBuilder $filterBuilder)
    {
        $this->context = Context::getContext();
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
    public function renderFiltersTemplate(array $selectedFilters)
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
     * @param array $products
     * @param $productsCount
     *
     * @return string
     */
    public function renderProductsTemplate(array $products, $productsCount)
    {
        if (empty($products)) {
            return $this->context->smarty->fetch($this->bradViewsDir.'front/no-products-found.tpl');
        }

        $frontController = Context::getContext()->controller;
        $frontController->addColorsToProductList($products);

        $this->context->smarty->assign([
            'products' => $products,
            'search_products' => $products,
            'nbProducts' => (int) $productsCount,
            'homeSize' => Image::getSize(ImageType::getFormatedName('home')),
            'current_url' => '',
            'request' => '',
        ]);

        $renderedList = $this->context->smarty->fetch(_PS_THEME_DIR_.'product-list.tpl');

        return $renderedList;
    }

    /**
     * Render pagination template
     *
     * @param int $productsCount
     *
     * @return string
     */
    public function renderPaginationTemplate($productsCount)
    {
        $p = (int) Tools::getValue('p');
        $n = (int) Tools::getValue('n');
        $range = 2;

        if ($n < 1) {
            $n = (int) Configuration::get('PS_PRODUCTS_PER_PAGE');
        }

        if ($p < 1) {
            $p = 1;
        }

        if ($p > ($productsCount / $n)) {
            $p = ceil($productsCount / $n);
        }

        $pagesCount = ceil($productsCount / $n);

        $start = $p - $range;
        $stop = $p + $range;

        if ($start < 1) {
            $start = 1;
        }

        if ($stop > $pagesCount) {
            $stop = $pagesCount;
        }

        $this->context->smarty->assign([
            'nb_products' => $productsCount,
            'pages_nb' => $pagesCount,
            'p' => $p,
            'n' => $n,
            'range' => $range,
            'start' => $start,
            'stop' => $stop,
            'paginationId' => 'bottom',
            'products_per_page' => (int) Configuration::get('PS_PRODUCTS_PER_PAGE'),
            'current_url' => '',
        ]);

        return $this->context->smarty->fetch(_PS_THEME_DIR_.'pagination.tpl');
    }
}
