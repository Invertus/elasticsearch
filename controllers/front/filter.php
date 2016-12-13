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

        $selectedFilters = $urlParser->getSelectedFilters();
        $p               = $urlParser->getPage();
        $n               = $urlParser->getSize();
        $orderWay        = $urlParser->getOrderWay();
        $orderBy         = $urlParser->getOrderBy();

        /** @var \Invertus\Brad\Service\FilterService $filterService */
        $filterService = $this->get('filter_service');

        $products = $filterService->filterProducts($selectedFilters, $p, $n, $orderWay, $orderBy);
        $productsCount = $filterService->countProducts($selectedFilters);

        $products = $this->formatProducts($products);
        $this->addColorsToProductList($products);

        /** @var \Invertus\Brad\Service\Builder\TemplateBuilder $templateBuilder */
        $templateBuilder = $this->get('template_builder');
        $bottomPaginationTemplate = $templateBuilder->renderPaginationTemplate($productsCount);
        $topPaginationTemplate = preg_replace('/(_bottom)/i', '', $bottomPaginationTemplate);

        die(json_encode([
            'query_string'          => $urlParser->getQueryString(),
            'filters_template'      => $templateBuilder->renderFiltersTemplate($selectedFilters),
            'products_list'         => $templateBuilder->renderProductsTemplate($products, $productsCount),
            'top_pagination'        => $topPaginationTemplate,
            'reset_original_layout' => empty($selectedFilters) ? true : false,
        ]));
    }
}