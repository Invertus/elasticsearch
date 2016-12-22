<?php

use Invertus\Brad\Config\Setting;
use Invertus\Brad\Controller\AbstractBradModuleFrontController;
use Invertus\Brad\DataType\FilterData;
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
        $idCategory      = $urlParser->getIdCategory();
        $p               = $urlParser->getPage();
        $n               = $urlParser->getSize();
        $orderWay        = $urlParser->getOrderWay();
        $orderBy         = $urlParser->getOrderBy();

        /** @var \Invertus\Brad\Service\FilterService $filterService */
        $filterService = $this->get('filter_service');

        $filterData = new FilterData();
        $filterData->setSize($n);
        $filterData->setPage($p);
        $filterData->setOrderWay($orderWay);
        $filterData->setOrderBy($orderBy);
        $filterData->setIdCategory($idCategory);
        $filterData->setSelectedFilters($selectedFilters);
        $filterData->initFilters();

        $products             = $filterService->filterProducts($filterData);
        $productsCount        = $filterService->countProducts($filterData);
        $productsAggregations = $filterService->aggregateProducts($filterData);

        $products = $this->formatProducts($products);
        $this->addColorsToProductList($products);

        /** @var \Invertus\Brad\Template\Templating $templating */
        $templating = $this->get('templating');
        $bottomPaginationTemplate = $templating->renderPaginationTemplate($productsCount, $p, $n);
        $topPaginationTemplate = preg_replace('/(_bottom)/i', '', $bottomPaginationTemplate);

        $filtersBlockTemplate    = $templating->renderFiltersBlockTemplate($filterData);
        $selectedFiltersTemplate = $templating->renderSelectedFilters($selectedFilters);
        $productListTemplate     = $templating->renderProductsTemplate($products, $productsCount);
        $categoryCountTemplate   = $templating->renderCategoryCountTemplate($productsCount);

        die(json_encode([
            'query_string'               => $queryString,
            'filters_block_template'     => $filtersBlockTemplate,
            'products_list_template'     => $productListTemplate,
            'top_pagination_template'    => $topPaginationTemplate,
            'bottom_pagination_template' => $bottomPaginationTemplate,
            'selected_filters_template'  => $selectedFiltersTemplate,
            'category_count_template'    => $categoryCountTemplate,
        ]));
    }
}