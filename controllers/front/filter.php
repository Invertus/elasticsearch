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

        $filterData = new FilterData();
        $filterData->setSize($n);
        $filterData->setPage($p);
        $filterData->setOrderWay($orderWay);
        $filterData->setOrderBy($orderBy);
        $filterData->setIdCategory($idCategory);
        $filterData->setSelectedFilters($selectedFilters);
        $filterData->initFilters();

        /** @var \Invertus\Brad\Service\FilterService $filterService */
        $filterService = $this->get('filter_service');

        $products             = $filterService->filterProducts($filterData);
        $productsCount        = $filterService->countProducts($filterData);
        $productsAggregations = $filterService->aggregateProducts($filterData);

        $products = $this->formatProducts($products);
        $this->addColorsToProductList($products);

        /** @var \Invertus\Brad\Template\Templating $templating */
        $templating = $this->get('templating');
        $bottomPaginationTemplate = $templating->renderPaginationTemplate($productsCount, $p, $n);
        $topPaginationTemplate = preg_replace('/(_bottom)/i', '', $bottomPaginationTemplate);

        $filtersBlockTemplate    = $templating->renderFiltersBlockTemplate($filterData, $productsAggregations);
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