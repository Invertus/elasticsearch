<?php
/**
 * Copyright (c) 2016-2017 Invertus, JSC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Invertus\Brad\DataType;

use BradFilter;
use BradProduct;
use Category;
use Context;
use Invertus\Brad\Repository\AttributeGroupRepository;
use Invertus\Brad\Repository\CategoryRepository;
use Invertus\Brad\Repository\FeatureRepository;
use Invertus\Brad\Repository\FilterRepository;
use Invertus\Brad\Repository\FilterTemplateRepository;
use Invertus\Brad\Repository\ManufacturerRepository;
use Invertus\Brad\Service\Elasticsearch\ElasticsearchHelper;
use Invertus\Brad\Util\RangeParser;
use Tools;

/**
 * Class FilterData
 *
 * @package Invertus\Brad\DataType
 */
class FilterData extends SearchData
{
    /**
     * @var array
     */
    private $selectedFilters;

    /**
     * @var int
     */
    private $idCategory;

    /**
     * @var array|FilterStruct[]
     */
    private $filters;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var \Core_Foundation_Database_EntityManager
     */
    private $em;

    /**
     * @var ElasticsearchHelper
     */
    private $esHelper;

    /**
     * FilterData constructor.
     */
    public function __construct()
    {
        $context = Context::getContext();
        /** @var \Brad $brad */
        $brad = \Module::getInstanceByName('brad');

        $this->em = $brad->getContainer()->get('em');
        $this->context = $context;
        $this->esHelper = $brad->getContainer()->get('elasticsearch.helper');
    }

    /**
     * @return array
     */
    public function getSelectedFilters()
    {
        return $this->selectedFilters;
    }

    /**
     * @param array $selectedFilters
     */
    public function setSelectedFilters($selectedFilters)
    {
        $this->selectedFilters = $selectedFilters;
    }

    /**
     * @return int
     */
    public function getIdCategory()
    {
        return $this->idCategory;
    }

    /**
     * @param int $idCategory
     */
    public function setIdCategory($idCategory)
    {
        $this->idCategory = $idCategory;
    }

    /**
     * @param bool $castArray
     *
     * @return array|FilterStruct[]
     */
    public function getFilters($castArray = true)
    {
        if (!$castArray) {
            return $this->filters;
        }

        return array_map(function($filter) {
            return (array) $filter;
        }, $this->filters);
    }

    /**
     * Init filters
     */
    public function initFilters()
    {
        $idShop = $this->context->shop->id;

        /** @var FilterTemplateRepository $filterTemplateRepository */
        $filterTemplateRepository = $this->em->getRepository('BradFilterTemplate');
        $filters = $filterTemplateRepository->findTemplateFilters($this->getIdCategory(), $idShop);

        if (empty($filters)) {
            return;
        }

        $selectedFilters = $this->getSelectedFilters();
        $selectedFiltersInputNames = array_keys($selectedFilters);

        /** @var FilterStruct $filter */
        foreach ($filters as &$filter) {
            switch ($filter->getFilterType()) {
                case BradFilter::FILTER_TYPE_ATTRIBUTE_GROUP:
                    $criterias = $this->getAttributeGroupCriterias($filter);
                    $filter->setCriterias($criterias);
                    $filter->setCriteriaNameKey('name');
                    $filter->setCriteriaValueKey('id_attribute');
                    break;
                case BradFilter::FILTER_TYPE_FEATURE:
                    $criterias = $this->getFeatureCriterias($filter);
                    $filter->setCriterias($criterias);
                    $filter->setCriteriaNameKey('name');
                    $filter->setCriteriaValueKey('id_feature_value');
                    break;
                case BradFilter::FILTER_TYPE_PRICE:
                    $criterias = $this->getPriceCriterias($filter);
                    $filter->setCriterias($criterias);
                    $filter->setCriteriaNameKey('name');
                    $filter->setCriteriaValueKey('value');
                    break;
                case BradFilter::FILTER_TYPE_MANUFACTURER:
                    $criterias = $this->getManufacturerCriterias();
                    $filter->setCriterias($criterias);
                    $filter->setCriteriaNameKey('name');
                    $filter->setCriteriaValueKey('id_manufacturer');
                    break;
                case BradFilter::FILTER_TYPE_QUANTITY:
                    $criterias = $this->getQuantityCriterias();
                    $filter->setCriterias($criterias);
                    $filter->setCriteriaNameKey('name');
                    $filter->setCriteriaValueKey('value');
                    break;
                case BradFilter::FILTER_TYPE_WEIGHT:
                    $criterias = $this->getWeightCriterias($filter);
                    $filter->setCriterias($criterias);
                    $filter->setCriteriaNameKey('name');
                    $filter->setCriteriaValueKey('value');
                    break;
                case BradFilter::FILTER_TYPE_CATEGORY:
                    $criterias = $this->getCategoryCriterias($this->getIdCategory());
                    $filter->setCriterias($criterias);
                    $filter->setCriteriaNameKey('name');
                    $filter->setCriteriaValueKey('id_category');
                    break;
            }

            if (in_array($filter->getInputName(), $selectedFiltersInputNames)) {
                $this->addSelectedValues($filter, $selectedFilters[$filter->getInputName()]);
            }

            $this->filters[$filter->getInputName()] = $filter;
        }

        $this->setFiltersNames();
    }

    /**
     * Set up filters names
     */
    private function setFiltersNames()
    {
        /** @var FeatureRepository $featureRepository */
        $featureRepository = $this->em->getRepository('BradFeature');
        $featuresNames = $featureRepository->findNames($this->context->language->id, $this->context->shop->id);

        /** @var AttributeGroupRepository $attributeGroupRepository */
        $attributeGroupRepository = $this->em->getRepository('BradAttributeGroup');
        $attributeGroupsNames =
            $attributeGroupRepository->findNames($this->context->language->id, $this->context->shop->id);

        $filterTypeTranslations = BradFilter::getFilterTypeTranslations();

        $name = '';
        foreach ($this->filters as &$filter) {
            $filterType = $filter->getFilterType();
            switch ($filterType) {
                case BradFilter::FILTER_TYPE_PRICE:
                case BradFilter::FILTER_TYPE_CATEGORY:
                case BradFilter::FILTER_TYPE_QUANTITY:
                case BradFilter::FILTER_TYPE_WEIGHT:
                case BradFilter::FILTER_TYPE_MANUFACTURER:
                    $name = $filterTypeTranslations[$filterType];
                    break;
                case BradFilter::FILTER_TYPE_FEATURE:
                    $name = $featuresNames[$filter->getIdKey()];
                    break;
                case BradFilter::FILTER_TYPE_ATTRIBUTE_GROUP:
                    $name = $attributeGroupsNames[$filter->getIdKey()];
                    break;
            }

            $filter->setName($name);
        }
    }

    /**
     * Get feature criterias
     *
     * @param FilterStruct $filter
     *
     * @return array
     */
    private function getFeatureCriterias(FilterStruct $filter)
    {
        /** @var FeatureRepository $featureRepository */
        $featureRepository = $this->em->getRepository('BradFeature');

        $featuresValues = $featureRepository->findFeaturesValues($this->context->language->id);

        $idFeature = $filter->getIdKey();
        $featureCriterias =  $featuresValues[$idFeature];

        return $featureCriterias;
    }

    /**
     * Get attribute group criterias
     *
     * @param FilterStruct $filter
     *
     * @return array
     */
    private function getAttributeGroupCriterias(FilterStruct $filter)
    {
        /** @var AttributeGroupRepository $attributeGroupRepository */
        $attributeGroupRepository = $this->em->getRepository('BradAttributeGroup');

        $idLang = $this->context->language->id;
        $idShop = $this->context->shop->id;

        $idAttributeGroup = (int) $filter->getIdKey();
        $attributeGroupsValues = $attributeGroupRepository->findAttributesGroupsValues($idLang, $idShop);

        $attributeGroupCriterias = $attributeGroupsValues[$idAttributeGroup];

        return $attributeGroupCriterias;
    }

    /**
     * Get price filter
     *
     * @param FilterStruct $filter
     *
     * @return array
     */
    private function getPriceCriterias(FilterStruct $filter)
    {
        $filterStyle = $filter->getFilterStyle();
        $pricesCriterias = [];

        if (in_array($filterStyle, [BradFilter::FILTER_STYLE_INPUT, BradFilter::FILTER_STYLE_SLIDER])) {
            $maxPrice = $this->esHelper->getAggregatedProductPrice(ElasticsearchHelper::AGGS_MAX);
            $minPrice = $this->esHelper->getAggregatedProductPrice(ElasticsearchHelper::AGGS_MIN);

            $pricesCriterias[] = [
                'name'  => '',
                'value' => sprintf('%s:%s', round($minPrice, 2), round($maxPrice, 2)),
            ];

            return $pricesCriterias;
        }

        $ranges = [];

        if (BradFilter::FILTER_STYLE_CHECKBOX == $filterStyle) {
            $maxPrice = $this->esHelper->getAggregatedProductPrice(ElasticsearchHelper::AGGS_MAX);
            $minPrice = $this->esHelper->getAggregatedProductPrice(ElasticsearchHelper::AGGS_MIN);

            $n = 10;
            $ranges = RangeParser::splitIntoRanges($minPrice, $maxPrice, $n);
        } elseif (BradFilter::FILTER_STYLE_LIST_OF_VALUES) {
            /** @var FilterRepository $filterRepository */
            $filterRepository = $this->em->getRepository('BradFilter');
            $criterias = $filterRepository->findAllCriterias();

            $idFilter = $filter->getIdFilter();
            $ranges = $criterias[$idFilter];
        }

        foreach ($ranges as $range) {
            $min = $range['min_value'];
            $max = $range['max_value'];

            $pricesCriterias[] = [
                'value' => sprintf('%s:%s', round($min, 2), round($max, 2)),
                'name'  => sprintf('%s - %s', Tools::displayPrice($min), Tools::displayPrice($max)),
            ];
        }

        return $pricesCriterias;
    }

    /**
     * Get manufacturer filter criterias
     *
     * @return array
     */
    private function getManufacturerCriterias()
    {
        /** @var ManufacturerRepository $manufacturerRepository */
        $manufacturerRepository = $this->em->getRepository('BradManufacturer');
        $manufacturers = $manufacturerRepository->findAllByShopId($this->context->shop->id);

        return $manufacturers;
    }

    /**
     * Get quantity criterias
     *
     * @return array
     */
    private function getQuantityCriterias()
    {
        $criterias = BradProduct::getStockCriterias();

        return $criterias;
    }

    /**
     * Get weight criterias
     *
     * @param FilterStruct $filter
     *
     * @return array
     */
    private function getWeightCriterias(FilterStruct $filter)
    {
        $filterStyle = (int) $filter->getFilterStyle();
        $weightCriterias = [];

        if (in_array($filterStyle, [BradFilter::FILTER_STYLE_INPUT, BradFilter::FILTER_STYLE_SLIDER])) {
            $minWeight = $this->esHelper->getAggregatedProductWeight(ElasticsearchHelper::AGGS_MIN);
            $maxWeight = $this->esHelper->getAggregatedProductWeight(ElasticsearchHelper::AGGS_MAX);

            $weightCriterias[] = [
                'value' => sprintf('%s:%s', $minWeight, $maxWeight),
                'name' => '',
            ];

            return $weightCriterias;
        }

        $ranges = [];

        if (BradFilter::FILTER_STYLE_LIST_OF_VALUES == $filterStyle) {
            /** @var FilterRepository $filterRepository */
            $filterRepository = $this->em->getRepository('BradFilter');
            $criterias = $filterRepository->findAllCriterias();
            $ranges = $criterias[$filter->getIdFilter()];
        } elseif (BradFilter::FILTER_STYLE_CHECKBOX == $filterStyle) {

            $minWeight = $this->esHelper->getAggregatedProductWeight(ElasticsearchHelper::AGGS_MIN);
            $maxWeight = $this->esHelper->getAggregatedProductWeight(ElasticsearchHelper::AGGS_MAX);

            $n = 10;
            $ranges = RangeParser::splitIntoRanges($minWeight, $maxWeight, $n);
        }

        $customSuffix = $filter->getCriteriaSuffix() ? $filter->getCriteriaSuffix() : '';

        foreach ($ranges as $range) {
            $min = $range['min_value'];
            $max = $range['max_value'];

            $weightCriterias[] = [
                'name'  => sprintf('%s %s - %s %s', $min, $customSuffix, $max, $customSuffix),
                'value' => sprintf('%s:%s', $min, $max),
            ];
        }

        return $weightCriterias;
    }

    /**
     * Get categories filter
     *
     * @param int $idCategory
     *
     * @return array
     */
    private function getCategoryCriterias($idCategory)
    {
        $idShop = $this->context->shop->id;
        $idLang = $this->context->language->id;

        $category = new Category($idCategory);

        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->em->getRepository('BradCategory');
        $childCategories = $categoryRepository->findChildCategoriesNamesAndIds($category, $idLang, $idShop);

        return $childCategories;
    }

    /**
     * Add selected values to filter
     *
     * @param FilterStruct $filter
     * @param array $selectedValues
     */
    private function addSelectedValues(FilterStruct &$filter, array $selectedValues)
    {
        if (empty($selectedValues)) {
            return;
        }

        $filterType  = (int) $filter->getFilterType();
        $filterStyle = (int) $filter->getFilterStyle();

        $rangesTypeFilters = [BradFilter::FILTER_TYPE_PRICE, BradFilter::FILTER_TYPE_WEIGHT];
        $rangeStyles       = [BradFilter::FILTER_STYLE_SLIDER, BradFilter::FILTER_STYLE_INPUT];

        if ((in_array($filterType, $rangesTypeFilters) && !in_array($filterStyle, $rangeStyles)) ||
            BradFilter::FILTER_STYLE_LIST_OF_VALUES == $filterStyle
        ) {
            $criterias = $filter->getCriterias();

            foreach ($criterias as &$criteria) {
                foreach ($selectedValues as $selectedValue) {
                    $value = implode(':', [$selectedValue['min_value'], $selectedValue['max_value']]);

                    if ($value == $criteria['value']) {
                        $criteria['checked'] = true;
                    }
                }
            }

            $filter->setCriterias($criterias);
        }  elseif (BradFilter::FILTER_STYLE_CHECKBOX == $filterStyle) {
            $criterias = $filter->getCriterias();
            foreach ($criterias as &$criteria) {
                foreach ($selectedValues as $selectedValue) {
                    if ($selectedValue == $criteria[$filter->getCriteriaValueKey()]) {
                        $criteria['checked'] = true;
                    }
                }
            }
            $filter->setCriterias($criterias);
        } elseif (BradFilter::FILTER_STYLE_INPUT == $filterStyle ||
            BradFilter::FILTER_STYLE_SLIDER == $filterStyle
        ) {
            $criterias = $filter->getCriterias();
            $criterias[0]['selected_min_value'] = $selectedValues[0]['min_value'];
            $criterias[0]['selected_max_value'] = $selectedValues[0]['max_value'];

            $filter->setCriterias($criterias);
        }
    }
}
