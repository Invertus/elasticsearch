<?php

namespace Invertus\Brad\Service;

use BradFilter;
use Context;
use Core_Foundation_Database_EntityManager;
use Invertus\Brad\Repository\AttributeGroupRepository;
use Invertus\Brad\Repository\FeatureRepository;
use Invertus\Brad\Repository\FilterTemplateRepository;
use Tools;

/**
 * Class Filter
 *
 * @package Invertus\Brad\Service
 */
class Filter
{
    /**
     * @var Core_Foundation_Database_EntityManager
     */
    private $em;

    /**
     * @var UrlParser
     */
    private $urlParser;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var array Array of filters data
     */
    private $filters;

    /**
     * Filter constructor.
     *
     * @param Context $context
     * @param Core_Foundation_Database_EntityManager $em
     * @param UrlParser $urlParser
     */
    public function __construct(Context $context, Core_Foundation_Database_EntityManager $em, UrlParser $urlParser)
    {
        $this->em = $em;
        $this->urlParser = $urlParser;
        $this->context = $context;
    }

    /**
     * Perform filtering
     */
    public function process()
    {
        //@todo: parse query and set up selected filter values
        $this->initFilters();
        $this->initFiltersValues();
        //@todo: perform search
        //@todo: setup search results
    }

    /**
     * Get configured filters
     */
    private function initFilters()
    {
        $idCategory = Tools::getValue('id_category');

        /** @var FilterTemplateRepository $filterTemplateRepository */
        $filterTemplateRepository = $this->em->getRepository('BradFilterTemplate');
        $this->filters = $filterTemplateRepository->findTemplateFilters($idCategory, $this->context->shop->id);

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
        $attributeGroupsNames = $attributeGroupRepository->findNames($this->context->language->id, $this->context->shop->id);

        $filterTypeTranslations = BradFilter::getFilterTypeTranslations();

        foreach ($this->filters as &$filter) {
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
     * Iniitialize filters values
     */
    private function initFiltersValues()
    {
        if (empty($this->filters)) {
            return;
        }

        /** @var FeatureRepository $featureRepository */
        $featureRepository = $this->em->getRepository('BradFeature');
        $featuresValues = $featureRepository->findFeaturesValues($this->context->language->id);

        /** @var AttributeGroupRepository $attributeGroupRepository */
        $attributeGroupRepository = $this->em->getRepository('BradAttributeGroup');
        $attributeGroupsValues = $attributeGroupRepository->findAttributesGroupsValues($this->context->language->id, $this->context->shop->id);

        foreach ($this->filters as &$filter) {
            $filterType = (int) $filter['filter_type'];
            switch ($filterType) {
                case BradFilter::FILTER_TYPE_ATTRIBUTE_GROUP:
                    $filter['criterias'] = $attributeGroupsValues[$filter['id_key']];
                    break;
                case BradFilter::FILTER_TYPE_FEATURE:
                    $filter['criterias'] = $featuresValues[$filter['id_key']];
                    break;
                case BradFilter::FILTER_TYPE_PRICE:
                    $filter['criterias'] = $this->getPriceCriterias($filter);
                    break;
            }
        }

        d($this->filters);
    }

    /**
     * Get price filter
     *
     * @param array $filterData
     *
     * @return array
     */
    private function getPriceCriterias(array $filterData)
    {
        //@todo: get price filter criterias
    }
}
