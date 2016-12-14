<?php

use Invertus\Brad\Config\Setting;
use Invertus\Brad\Controller\AbstractBradModuleFrontController;
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

        $isFiltersEnabled = (bool) Configuration::get(Setting::ENABLE_FILTERS);
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

        $queryString     = $urlParser->getQueryString();
        $selectedFilters = $urlParser->getSelectedFilters();
        $p               = $urlParser->getPage();
        $n               = $urlParser->getSize();
        $orderWay        = $urlParser->getOrderWay();
        $orderBy         = $urlParser->getOrderBy();

        /** @var \Invertus\Brad\Service\FilterService $filterService */
        $filterService = $this->get('filter_service');

        $products = $filterService->filterProducts($selectedFilters, $p, $n, $orderBy, $orderWay);
        $productsCount = $filterService->countProducts($selectedFilters);

        $products = $this->formatProducts($products);
        $this->addColorsToProductList($products);

        /** @var \Invertus\Brad\Service\Builder\TemplateBuilder $templateBuilder */
        $templateBuilder = $this->get('template_builder');
        $bottomPaginationTemplate = $templateBuilder->renderPaginationTemplate($productsCount);
        $topPaginationTemplate = preg_replace('/(_bottom)/i', '', $bottomPaginationTemplate);

        $filtersBlockTemplate = $templateBuilder->renderFiltersTemplate($selectedFilters, $p, $n, $orderWay, $orderBy);

        die(json_encode([
            'query_string'          => $queryString,
            'filters_template'      => $filtersBlockTemplate,
            'products_list'         => $templateBuilder->renderProductsTemplate($products, $productsCount),
            'top_pagination'        => $topPaginationTemplate,
            'bottom_pagination'     => $bottomPaginationTemplate,
            'reset_original_layout' => empty($selectedFilters) && empty($queryString) ?: false,
        ]));
    }
}