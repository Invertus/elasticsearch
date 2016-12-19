<?php

namespace Invertus\Brad\Template;

use Configuration;
use Context;
use Image;
use ImageType;
use Tools;

/**
 * Class Templating
 *
 * @package Invertus\Brad\Template
 */
class Templating
{
    const FILENAME = 'TemplateBuilder';

    /**
     * @var Context
     */
    private $context;

    /**
     * @var string
     */
    private $bradTemplatesDir;

    /**
     * @var FilterBlockTemplating
     */
    private $filterBlockTemplating;

    /**
     * @var \Core_Foundation_Database_EntityManager
     */
    private $em;

    /**
     * TemplateBuilder constructor.
     *
     * @param string $bradTemplates
     * @param FilterBlockTemplating $filterBlockTemplating
     * @param $em
     */
    public function __construct($bradTemplates, FilterBlockTemplating $filterBlockTemplating, $em)
    {
        $this->context = Context::getContext();
        $this->bradTemplatesDir = $bradTemplates;
        $this->filterBlockTemplating = $filterBlockTemplating;
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
    public function renderFiltersBlockTemplate(array $selectedFilters, $p, $n, $orderWay, $orderBy)
    {
        $this->filterBlockTemplating->build($selectedFilters);

        $this->context->smarty->assign([
            'filters' => $this->filterBlockTemplating->getBuiltFilters(),
            'p' => $p,
            'n' => $n,
            'orderby' => $orderBy,
            'orderway' => $orderWay,
        ]);

        return $this->context->smarty->fetch($this->bradTemplatesDir.'front/filter-template.tpl');
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
            return $this->context->smarty->fetch($this->bradTemplatesDir.'front/no-products-found.tpl');
        }

        $frontController = Context::getContext()->controller;
        $frontController->addColorsToProductList($products);

        $this->context->smarty->assign([
            'products' => $products,
            'search_products' => $products,
            'nbProducts' => (int) $productsCount,
            'homeSize' => Image::getSize(ImageType::getFormatedName('home')),
        ]);

        $renderedList = $this->context->smarty->fetch(_PS_THEME_DIR_.'product-list.tpl');

        return $renderedList;
    }

    /**
     * Render pagination template
     *
     * @param int $productsCount
     * @param int $page
     * @param int $n
     *
     * @return string
     */
    public function renderPaginationTemplate($productsCount, $page, $n)
    {
        $range = 2;

        if ($page > ($productsCount / $n)) {
            $page = ceil($productsCount / $n);
        }

        $pagesCount = ceil($productsCount / $n);

        $start = $page - $range;
        $stop = $page + $range;

        if ($start < 1) {
            $start = 1;
        }

        if ($stop > $pagesCount) {
            $stop = $pagesCount;
        }

        $this->context->smarty->assign([
            'nb_products' => $productsCount,
            'pages_nb' => $pagesCount,
            'p' => $page,
            'n' => $n,
            'range' => $range,
            'start' => $start,
            'stop' => $stop,
            'paginationId' => 'bottom',
            'products_per_page' => (int) Configuration::get('PS_PRODUCTS_PER_PAGE'),
            'current_url' => 'pagination',
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
        //@todo: finish implementing selected filters
        return '';

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

        return $this->context->smarty->fetch($this->bradTemplatesDir.'front/selected-filters.tpl');
    }

    /**
     * Render category count template
     *
     * @param int $productsCount
     *
     * @return string
     */
    public function renderCategoryCountTemplate($productsCount)
    {
        $this->context->smarty->assign([
            'nb_products' => (int) $productsCount,
        ]);

        return $this->context->smarty->fetch(_PS_THEME_DIR_.'category-count.tpl');
    }
}
