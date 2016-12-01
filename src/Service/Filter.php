<?php

namespace Invertus\Brad\Service;

use Context;
use Core_Foundation_Database_EntityManager;
use Invertus\Brad\Repository\FeatureRepository;
use Invertus\Brad\Repository\FilterTemplateRepository;
use Invertus\Brad\Service\Builder\TemplateBuilder;
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
     * @var TemplateBuilder
     */
    private $templatebuilder;

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
     * @param TemplateBuilder $templatebuilder
     */
    public function __construct(Context $context, Core_Foundation_Database_EntityManager $em, UrlParser $urlParser, TemplateBuilder $templatebuilder)
    {
        $this->em = $em;
        $this->urlParser = $urlParser;
        $this->context = $context;
        $this->templatebuilder = $templatebuilder;
    }

    /**
     * Perform filtering
     */
    public function process()
    {
        $this->filters = $this->getFilters();
    }

    /**
     * Render filters html
     *
     * @return string
     */
    public function renderFilters()
    {
        return $this->templatebuilder->buildFilters($this->filters);
    }

    /**
     * Render results html
     *
     * @return string
     */
    public function renderResults()
    {
        return 'rendered_results';
    }

    /**
     * Get filters
     *
     * @return array
     */
    private function getFilters()
    {
        $idCategory = Tools::getValue('id_category');

        /** @var FilterTemplateRepository $filterTemplateRepository */
        $filterTemplateRepository = $this->em->getRepository('BradFilterTemplate');

        $filters = $filterTemplateRepository->findTemplateFilters($idCategory, $this->context->shop->id);

        /** @var FeatureRepository $featureRepository */
        $featureRepository = $this->em->getRepository('BradFeature');
        $features = $featureRepository->findNames($this->context->language->id, $this->context->shop->id);
        //@todo: get all attribute groups

        //@todo: set filter names

        return $filters;
    }
}
