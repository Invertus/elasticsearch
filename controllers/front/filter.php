<?php

use Invertus\Brad\Config\Setting;

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
        /** @var \Invertus\Brad\Service\UrlParser $urlParser */
        $urlParser = $this->get('url_parser');
        $urlParser->parse();

        $selectedFilters =$urlParser->getSelectedFilters();
        $queryString = $urlParser->getQueryString();

        /** @var \Invertus\Brad\Service\Filter $filter */
        $filter = $this->get('filter');
        $products = $filter->process($selectedFilters); //@TODO: implements filtering

        /** @var \Invertus\Brad\Service\Builder\FilterBuilder $filterBuilder */
        $filterBuilder = $this->get('filter_builder');
        $filterBuilder->build($selectedFilters);

        $filters = $filterBuilder->getBuiltFilters();

        /** @var \Invertus\Brad\Service\Builder\TemplateBuilder $templateBuilder */
        $templateBuilder = $this->get('template_builder');
        $filtersTemplate = $templateBuilder->buildFiltersTemplate($filters);

        die(json_encode([
            'query_string' => $queryString,
            'filters_template' => $filtersTemplate,
        ]));
    }
}