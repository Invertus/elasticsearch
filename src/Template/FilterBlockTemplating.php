<?php

namespace Invertus\Brad\Template;

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
 * Class FilterBlockTemplating
 *
 * @package Invertus\Brad\Template
 */
class FilterBlockTemplating
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var \Core_Foundation_Database_EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $builtFilters = [];

    /**
     * @var ElasticsearchHelper
     */
    private $esHelper;

    /**
     * FilterBuilder constructor.
     *
     * @param \Core_Foundation_Database_EntityManager $em
     * @param ElasticsearchHelper $esHelper
     */
    public function __construct($em, ElasticsearchHelper $esHelper)
    {
        $this->context = Context::getContext();
        $this->em = $em;
        $this->esHelper = $esHelper;
    }

    /**
     * Build filters
     *
     * @param array $selectedFilters
     */
    public function build(array $selectedFilters)
    {
        $idCategory = (int) Tools::getValue('id_category');

        /** @var FilterTemplateRepository $filterTemplateRepository */
        $filterTemplateRepository = $this->em->getRepository('BradFilterTemplate');
        $filters = $filterTemplateRepository->findTemplateFilters($idCategory, $this->context->shop->id);

        if (empty($filters)) {
            return;
        }

        $selectedFiltersInputNames = array_keys($selectedFilters);

        foreach ($filters as $filter) {
            $filterType = (int) $filter['filter_type'];
            switch ($filterType) {
                case BradFilter::FILTER_TYPE_ATTRIBUTE_GROUP:
                    $filter['criterias'] = $this->getAttributeGroupCriterias($filter);
                    break;
                case BradFilter::FILTER_TYPE_FEATURE:
                    $filter['criterias'] = $this->getFeatureCriterias($filter);
                    break;
                case BradFilter::FILTER_TYPE_PRICE:
                    $filter['criterias'] = $this->getPriceCriterias($filter);
                    break;
                case BradFilter::FILTER_TYPE_MANUFACTURER:
                    $filter['criterias'] = $this->getManufacturerCriterias($filter);
                    break;
                case BradFilter::FILTER_TYPE_QUANTITY:
                    $filter['criterias'] = $this->getQuantityCriterias($filter);
                    break;
                case BradFilter::FILTER_TYPE_WEIGHT:
                    $filter['criterias'] = $this->getWeightCriterias($filter);
                    break;
                case BradFilter::FILTER_TYPE_CATEGORY:
                    $filter['criterias'] = $this->getCategoryCriterias($filter);
                    break;
            }

            if (in_array($filter['input_name'], $selectedFiltersInputNames)) {
                $this->addSelectedValues($filter, $selectedFilters[$filter['input_name']]);
            }

            $this->builtFilters[$filter['input_name']] = $filter;
        }

        $this->setFiltersNames();
    }

    /**
     * Get built filters
     *
     * @return array
     */
    public function getBuiltFilters()
    {
        return $this->builtFilters;
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

        foreach ($this->builtFilters as &$filter) {
            $filterType = (int) $filter['filter_type'];
            switch ($filterType) {
                case BradFilter::FILTER_TYPE_PRICE:
                case BradFilter::FILTER_TYPE_CATEGORY:
                case BradFilter::FILTER_TYPE_QUANTITY:
                case BradFilter::FILTER_TYPE_WEIGHT:
                case BradFilter::FILTER_TYPE_MANUFACTURER:
                    $filter['name'] = $filterTypeTranslations[$filterType];
                    break;
                case BradFilter::FILTER_TYPE_FEATURE:
                    $filter['name'] = $featuresNames[$filter['id_key']];
                    break;
                case BradFilter::FILTER_TYPE_ATTRIBUTE_GROUP:
                    $filter['name'] = $attributeGroupsNames[$filter['id_key']];
                    break;
            }
        }
    }

    /**
     * Get feature criterias
     *
     * @param array $filterData
     *
     * @return array
     */
    private function getFeatureCriterias(array &$filterData)
    {
        $filterData['input_name'] = 'feature_'.$filterData['id_key'];
        $filterStyle = (int) $filterData;

        /** @var FeatureRepository $featureRepository */
        $featureRepository = $this->em->getRepository('BradFeature');
        $featureCriterias = [];

        if (in_array($filterStyle, [BradFilter::FILTER_STYLE_INPUT, BradFilter::FILTER_STYLE_SLIDER])) {
            $idFeature = (int) $filterData['id_key'];
            $minWeight = $featureRepository->findMinFeatureValue($idFeature, $this->context->shop->id);
            $maxWeight = $featureRepository->findMaxFeatureValue($idFeature, $this->context->shop->id);;

            $featureCriterias = ['min_value' => $minWeight, 'max_value' => $maxWeight];
        } elseif (BradFilter::FILTER_STYLE_LIST_OF_VALUES == $filterStyle) {
            /** @var FilterRepository $filterRepository */
            $filterRepository = $this->em->getRepository('BradFilter');
            $criterias = $filterRepository->findAllCriterias();

            $featureCriterias = $criterias[$filterData['id_brad_filter']];
        } elseif (BradFilter::FILTER_STYLE_CHECKBOX == $filterStyle) {
            $featuresValues = $featureRepository->findFeaturesValues($this->context->language->id);

            $featureCriterias =  $featuresValues[$filterData['id_key']];

            $filterData['criteria_name'] = 'name';
            $filterData['criteria_value'] = 'id_feature_value';
        }

        return $featureCriterias;
    }

    /**
     * Get attribute group criterias
     *
     * @param array $filterData
     *
     * @return array
     */
    private function getAttributeGroupCriterias(array &$filterData)
    {
        $filterData['input_name'] = 'attribute_group_'.$filterData['id_key'];

        /** @var AttributeGroupRepository $attributeGroupRepository */
        $attributeGroupRepository = $this->em->getRepository('BradAttributeGroup');

        $idLang = $this->context->language->id;
        $idShop = $this->context->shop->id;
        $idAttributeGroup = (int) $filterData['id_key'];
        $filterStyle = (int) $filterData['filter_style'];
        $attributeGroupCriterias = [];

        if (in_array($filterStyle, [BradFilter::FILTER_STYLE_INPUT, BradFilter::FILTER_STYLE_SLIDER])) {
            $minAttributeGroupValue = $attributeGroupRepository->findMinAttributeGroupValue($idAttributeGroup, $idLang, $idShop);
            $maxAttributeGroupValue = $attributeGroupRepository->findMaxAttributeGroupValue($idAttributeGroup, $idLang, $idShop);

            $attributeGroupCriterias = ['min_value' => $minAttributeGroupValue, 'max_value' => $maxAttributeGroupValue];
        } elseif (BradFilter::FILTER_STYLE_CHECKBOX == $filterStyle) {
            $attributeGroupsValues = $attributeGroupRepository->findAttributesGroupsValues($this->context->language->id, $this->context->shop->id);

            $attributeGroupCriterias = $attributeGroupsValues[$idAttributeGroup];

            $filterData['criteria_name'] = 'name';
            $filterData['criteria_value'] = 'id_attribute';
        } elseif (BradFilter::FILTER_STYLE_LIST_OF_VALUES) {
            /** @var FilterRepository $filterRepository */
            $filterRepository = $this->em->getRepository('BradFilter');
            $criterias = $filterRepository->findAllCriterias();

            $attributeGroupCriterias = $criterias[$filterData['id_brad_filter']];
        }

        return $attributeGroupCriterias;
    }

    /**
     * Get price filter
     *
     * @param array $filterData
     *
     * @return array
     */
    private function getPriceCriterias(array &$filterData)
    {
        $filterData['input_name'] = 'price';
        $filterStyle = (int) $filterData['filter_style'];
        $pricesCriterias = [];

        if (in_array($filterStyle, [BradFilter::FILTER_STYLE_INPUT, BradFilter::FILTER_STYLE_SLIDER])) {
            $maxPrice = $this->esHelper->getAggregatedProductPrice(ElasticsearchHelper::AGGS_MAX);
            $minPrice = $this->esHelper->getAggregatedProductPrice(ElasticsearchHelper::AGGS_MIN);

            $pricesCriterias = ['max_value' => round($maxPrice, 2), 'min_value' => round($minPrice, 2)];
        } elseif (BradFilter::FILTER_STYLE_CHECKBOX == $filterStyle) {
            $maxPrice = $this->esHelper->getAggregatedProductPrice(ElasticsearchHelper::AGGS_MAX);
            $minPrice = $this->esHelper->getAggregatedProductPrice(ElasticsearchHelper::AGGS_MIN);

            //@todo: add setting for hardcoded value
            $n = 10;
            $ranges = RangeParser::splitIntoRanges($minPrice, $maxPrice, $n);

            foreach ($ranges as $range) {
                $min = $range['min_range'];
                $max = $range['max_range'];

                $pricesCriterias[] = [
                    'value' => sprintf('%s:%s', round($min, 2), round($max, 2)),
                    'name' => sprintf('%s - %s', Tools::displayPrice($min), Tools::displayPrice($max)),
                ];
            }

            $filterData['criteria_name'] = 'name';
            $filterData['criteria_value'] = 'value';
        } elseif (BradFilter::FILTER_STYLE_LIST_OF_VALUES) {
            /** @var FilterRepository $filterRepository */
            $filterRepository = $this->em->getRepository('BradFilter');
            $criterias = $filterRepository->findAllCriterias();

            $customCriterias = $criterias[$filterData['id_brad_filter']];

            foreach ($customCriterias as $customCriteria) {
                $min = $customCriteria['min_value'];
                $max = $customCriteria['max_value'];

                $pricesCriterias[] = [
                    'value' => sprintf('%s:%s', round($min, 2), round($max, 2)),
                    'name' => sprintf('%s - %s', Tools::displayPrice($min), Tools::displayPrice($max)),
                ];
            }

            $filterData['criteria_name'] = 'name';
            $filterData['criteria_value'] = 'value';
        }

        return $pricesCriterias;
    }

    /**
     * Get manufacturer filter criterias
     *
     * @param array $filterData
     *
     * @return array
     */
    private function getManufacturerCriterias(array &$filterData)
    {
        $filterData['input_name'] = 'manufacturer';

        /** @var ManufacturerRepository $manufacturerRepository */
        $manufacturerRepository = $this->em->getRepository('BradManufacturer');
        $manufacturers = $manufacturerRepository->findAllByShopId($this->context->shop->id);

        $filterData['criteria_name'] = 'name';
        $filterData['criteria_value'] = 'id_manufacturer';

        return $manufacturers;
    }

    /**
     * Get quantity criterias
     *
     * @param array $filterData
     *
     * @return array
     */
    private function getQuantityCriterias(array &$filterData)
    {
        $filterData['input_name'] = 'quantity';

        $criterias = BradProduct::getStockCriterias();

        $filterData['criteria_name'] = 'name';
        $filterData['criteria_value'] = 'value';

        return $criterias;
    }

    /**
     * Get weight criterias
     *
     * @param array $filterData
     *
     * @return array
     */
    private function getWeightCriterias(array &$filterData)
    {
        $filterData['input_name'] = 'weight';
        $filterStyle = (int) $filterData['filter_style'];
        $weightCriterias = [];

        if (in_array($filterStyle, [BradFilter::FILTER_STYLE_INPUT, BradFilter::FILTER_STYLE_SLIDER])) {
            $minWeight = $this->esHelper->getAggregatedProductWeight(ElasticsearchHelper::AGGS_MIN);
            $maxWeight = $this->esHelper->getAggregatedProductWeight(ElasticsearchHelper::AGGS_MAX);

            $weightCriterias = ['min_value' => $minWeight, 'max_value' => $maxWeight];
        } elseif (BradFilter::FILTER_STYLE_LIST_OF_VALUES == $filterStyle) {
            /** @var FilterRepository $filterRepository */
            $filterRepository = $this->em->getRepository('BradFilter');
            $criterias = $filterRepository->findAllCriterias();

            foreach ($criterias as $criteria) {
                $min = $criteria['min_value'];
                $max = $criteria['max_value'];

                $pricesCriterias[] = [
                    'value' => sprintf('%s:%s', $min, $max),
                    'name' => sprintf('%s - %s', $min, $max),
                ];
            }

            $filterData['criteria_name'] = 'name';
            $filterData['criteria_value'] = 'value';

            $weightCriterias = $criterias[$filterData['id_brad_filter']];
        } elseif (BradFilter::FILTER_STYLE_CHECKBOX == $filterStyle) {

            $minWeight = $this->esHelper->getAggregatedProductWeight(ElasticsearchHelper::AGGS_MIN);
            $maxWeight = $this->esHelper->getAggregatedProductWeight(ElasticsearchHelper::AGGS_MAX);

            $filterData['criteria_name'] = 'name';
            $filterData['criteria_value'] = 'value';

            //@todo: add setting for hardcoded value
            $n = 10;

            $ranges = RangeParser::splitIntoRanges($minWeight, $maxWeight, $n);

            foreach ($ranges as $range) {
                $min = $range['min_range'];
                $max = $range['max_range'];

                $weightCriterias[] = [
                    'name' => sprintf('%s %s - %s %s', $min, $filterData['criteria_suffix'], $max,  $filterData['criteria_suffix']),
                    'value' => sprintf('%s:%s', $min, $max),
                ];
            }
        }

        return $weightCriterias;
    }

    /**
     * Get categories filter
     *
     * @param array $filterData
     *
     * @return array
     */
    private function getCategoryCriterias(array &$filterData)
    {
        $filterData['input_name'] = 'category';

        $idShop = $this->context->shop->id;
        $idLang = $this->context->language->id;
        $idCategory = (int) Tools::getValue('id_category');
        $category = new Category($idCategory);

        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->em->getRepository('BradCategory');
        $childCategories = $categoryRepository->findChildCategoriesNamesAndIds($category, $idLang, $idShop);

        $filterData['criteria_name'] = 'name';
        $filterData['criteria_value'] = 'id_category';

        return $childCategories;
    }

    /**
     * Add selected values to filter
     *
     * @param array $filter
     * @param array $selectedValues
     */
    private function addSelectedValues(array &$filter, array $selectedValues)
    {
        if (empty($selectedValues)) {
            return;
        }

        $filterType = (int) $filter['filter_type'];
        $filterStyle = (int) $filter['filter_style'];

        $rangesTypeFilters = [BradFilter::FILTER_TYPE_PRICE, BradFilter::FILTER_TYPE_WEIGHT];

        if ((in_array($filterType, $rangesTypeFilters) &&
                !in_array($filterStyle, [BradFilter::FILTER_STYLE_SLIDER, BradFilter::FILTER_STYLE_INPUT])) ||
            BradFilter::FILTER_STYLE_LIST_OF_VALUES == $filterStyle
        ) {
            foreach ($filter['criterias'] as &$criteria) {
                foreach ($selectedValues as $selectedValue) {
                    $value = implode(':', [$selectedValue['min_value'], $selectedValue['max_value']]);

                    if ($value == $criteria['value']) {
                        $criteria['checked'] = true;
                    }
                }
            }
        }  elseif (BradFilter::FILTER_STYLE_CHECKBOX == $filterStyle) {
            foreach ($filter['criterias'] as &$criteria) {
                foreach ($selectedValues as $selectedValue) {
                    if ($selectedValue == $criteria[$filter['criteria_value']]) {
                        $criteria['checked'] = true;
                    }
                }
            }
        } elseif (BradFilter::FILTER_STYLE_INPUT == $filterStyle ||
            BradFilter::FILTER_STYLE_SLIDER == $filterStyle
        ) {
            $filter['criterias']['selected_min_value'] = $selectedValues[0]['min_value'];
            $filter['criterias']['selected_max_value'] = $selectedValues[0]['max_value'];
        }
    }
}
