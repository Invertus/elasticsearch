<?php

namespace Invertus\Brad\Service\Builder;

use Configuration;
use Context;
use Image;
use ImageType;
use Module;
use Tools;

/**
 * Class TemplateBuilder
 *
 * @package Invertus\Brad\Service\Builder
 */
class TemplateBuilder
{
    const FILENAME = 'TemplateBuilder';

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
     * @var \Core_Foundation_Database_EntityManager
     */
    private $em;

    /**
     * TemplateBuilder constructor.
     *
     * @param string $bradViewsDir
     * @param FilterBuilder $filterBuilder
     * @param $em
     */
    public function __construct($bradViewsDir, FilterBuilder $filterBuilder, $em)
    {
        $this->context = Context::getContext();
        $this->bradViewsDir = $bradViewsDir;
        $this->filterBuilder = $filterBuilder;
        $this->em = $em;
    }

    /**
     * Build filters html template
     *
     * @param array $selectedFilters
     *
     * @param int $p
     * @param int $n
     * @param string $orderWay
     * @param string $orderBy
     *
     * @return string
     */
    public function renderFiltersTemplate(array $selectedFilters, $p, $n, $orderWay, $orderBy)
    {
        $this->filterBuilder->build($selectedFilters);

        $this->context->smarty->assign([
            'filters' => $this->filterBuilder->getBuiltFilters(),
            'p' => $p,
            'n' => $n,
            'orderby' => $orderBy,
            'orderway' => $orderWay,
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

    /**
     * Render selected filters
     *
     * @param array $selectedFilters
     *
     * @return string
     */
    public function renderSelectedFilters($selectedFilters)
    {
        if (empty($selectedFilters)) {
            return '';
        }

        $idShop = $this->context->shop->id;
        $idLang = $this->context->language->id;

        $attributeGroupRep = $this->em->getRepository('BradAttributeGroup');
        $featuresRep = $this->em->getRepository('BradFeature');

        $featuresNames = $featuresRep->findNames($idLang, $idShop);
        $featuresValues = $featuresRep->findFeaturesValues($idLang);
        $attribtueGroupsNames = $attributeGroupRep->findNames($idLang, $idShop);
        $attribtueGroupsValuesNames = $attributeGroupRep->findAttributesGroupsValues($idLang, $idShop);

        $formattedSelectedFilters = [];

        foreach ($selectedFilters as $key => $selectedValues) {
            if (0 === strpos($key, 'attribute_group')) {
                if (!isset($formattedSelectedFilters[$key]['name'])) {
                    $idAttribtueGroup = end(explode('_', $key));
                    $formattedSelectedFilters[$key]['name'] = $attribtueGroupsNames[$idAttribtueGroup];
                }

                foreach ($selectedValues as $selectedValue) {

                    $value = is_array($selectedValue)
                        ? sprintf('%s:%s', $selectedValue['min_value'], $selectedValue['max_value'])
                        : $selectedValue;

                    $formattedSelectedFilters[$key]['values'][] = [
                        'filter' => $key,
                        'filter_value' => $value,
                    ];
                }
            }
        }

        $this->context->smarty->assign([
            'selected_filters' => $formattedSelectedFilters,
        ]);

        return $this->context->smarty->fetch($this->bradViewsDir.'front/selected-filters.tpl');
    }
}
