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

        /** @var \Invertus\Brad\Template\Templating $templating */
        $templating = $this->get('templating');
        $bottomPaginationTemplate = $templating->renderPaginationTemplate($productsCount, $p, $n);
        $topPaginationTemplate = preg_replace('/(_bottom)/i', '', $bottomPaginationTemplate);

        $filtersBlockTemplate = $templating->renderFiltersBlockTemplate($selectedFilters, $p, $n, $orderWay, $orderBy);
        $selectedFiltersTemplate = $templating->renderSelectedFilters($selectedFilters);

        die(json_encode([
            'query_string'               => $queryString,
            'filters_block_template'     => $filtersBlockTemplate,
            'products_list_template'     => $templating->renderProductsTemplate($products, $productsCount),
            'top_pagination_template'    => $topPaginationTemplate,
            'bottom_pagination_template' => $bottomPaginationTemplate,
            'selected_filters_template'  => $selectedFiltersTemplate,
        ]));
    }
}