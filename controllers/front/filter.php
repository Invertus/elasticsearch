<?php

use Invertus\Brad\Config\Setting;
use Invertus\Brad\Service\UrlParser;

/**
 * Class BradFilterModuleFrontController
 */
class BradFilterModuleFrontController extends AbstractBradModuleFrontController
{
    public function init()
    {
        if (!$this->isXmlHttpRequest()) {
            $this->redirectToNotFoundPage();
        }

        /** @var Core_Business_ConfigurationInterface $configuration */
        $configuration = $this->get('configuration');

        $isFiltersEnabled = (bool) $configuration->get(Setting::ENABLE_FILTERS);
        if (!$isFiltersEnabled) {
            die(json_encode([]));
        }

        /** @var \Invertus\Brad\Service\Elasticsearch\ElasticsearchManager $elasticsearchManager */
        $elasticsearchManager = $this->get('elasticsearch.manager');

        if (!$elasticsearchManager->isConnectionAvailable() ||
            !$elasticsearchManager->isIndexCreated($this->context->shop->id)
        ) {
            die(json_encode([]));
        }

        parent::init();
    }

    /**
     * Process filtering request
     */
    public function postProcess()
    {
        $urlParser = new UrlParser();
        $urlParser->parse($_GET);
        $selectedFilters = $urlParser->getSelectedFilters();

        /** @var \Invertus\Brad\Service\Filter $filter */
        $filter = $this->get('filter');
        $products = $filter->filterProducts($selectedFilters);
        $products = $this->formatProducts($products);   //@todo: move

        /** @var \Invertus\Brad\Service\Builder\TemplateBuilder $templateBuilder */
        $templateBuilder = $this->get('template_builder');
        $filtersTemplate = $templateBuilder->buildFiltersTemplate($selectedFilters);
        $productsList = $templateBuilder->buildResultsTemplate($products);

        die(json_encode([
            'query_string' => $urlParser->getQueryString(),
            'filters_template' => $filtersTemplate,
            'products_list' => $productsList,
        ]));
    }
}