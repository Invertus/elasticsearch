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

use Invertus\Brad\Config\Sort;
use Invertus\Brad\Config\Setting;
use Invertus\Brad\Controller\AbstractBradModuleFrontController;
use Invertus\Brad\DataType\SearchData;
use Invertus\Brad\Service\Elasticsearch\Builder\SearchQueryBuilder;
use Invertus\Brad\Service\UrlParser;
use Invertus\Brad\Util\Arrays;

/**
 * Class BradSearchModuleFrontController
 */
class BradSearchModuleFrontController extends AbstractBradModuleFrontController
{
    /**
     * Check if elasticsearch connection is available & index exists
     */
    public function init()
    {
        $isSearchEnabled = (bool) Configuration::get(Setting::ENABLE_SEARCH);
        if (!$isSearchEnabled) {
            if ($this->isXmlHttpRequest()) {
                die(json_encode(['instant_results' => false, 'dynamic_results' => false]));
            }

            $this->redirectToNotFoundPage();
        }

        /** @var \Invertus\Brad\Service\Elasticsearch\ElasticsearchManager $elasticsearchManager */
        $elasticsearchManager = $this->get('elasticsearch.manager');

        if (!$elasticsearchManager->isConnectionAvailable() ||
            !$elasticsearchManager->isIndexCreated($this->context->shop->id)
        ) {
            if ($this->isXmlHttpRequest()) {
                die(json_encode(['instant_results' => false, 'dynamic_results' => false]));
            }

            $this->redirectToNotFoundPage();
        }

        parent::init();
    }

    /**
     * Set template for controller's content
     */
    public function initContent()
    {
        parent::initContent();

        $this->template = _PS_THEME_DIR_.'search.tpl';
    }

    /**
     * Add assets to controller
     */
    public function setMedia()
    {
        parent::setMedia();

        if (!$this->isXmlHttpRequest()) {
            $this->addCSS(_THEME_CSS_DIR_.'product_list.css');
            Media::addJsDef([
                '$globalBradScrollCenterColumn' => true,
            ]);
        }
    }

    /**
     * Perform search
     */
    public function postProcess()
    {
        $urlParser = new UrlParser();

        $page                = $urlParser->getPage();
        $size                = $urlParser->getSize();
        $orderWay            = $urlParser->getOrderWay();
        $orderBy             = $urlParser->getOrderBy();
        $originalSearchQuery = $urlParser->getSearchQuery();
        $searchQuery         = Tools::replaceAccentedChars(urldecode($originalSearchQuery));

        /** @var \Invertus\Brad\Service\SearchService $searchService */
        $searchService = $this->get('search_service');

        $searchData = new SearchData();
        $searchData->setSize($size);
        $searchData->setPage($page);
        $searchData->setOrderWay($orderWay);
        $searchData->setOrderBy($orderBy);
        $searchData->setSearchQuery($searchQuery);

        $products = $searchService->searchProducts($searchData);
        $productsCount = $searchService->countProducts($searchData);

        $formattedProducts = $this->formatProducts($products);

        if ($this->isXmlHttpRequest()) {
            die($this->renderAjaxResponse($formattedProducts, $originalSearchQuery));
        }

        $this->p = $page;
        $this->n = $size;

        $this->addColorsToProductList($formattedProducts);

        $currentUrl = $this->context->link->getModuleLink(
            $this->module->name,
            Brad::FRONT_BRAD_SEARCH_CONTROLLER,
            [
                'search_query' => $originalSearchQuery,
            ]
        );

        $this->context->smarty->assign([
            'search_products' => $formattedProducts,
            'nbProducts'      => $productsCount,
            'search_query'    => $originalSearchQuery,
            'homeSize'        => Image::getSize(ImageType::getFormatedName('home')),
            'current_url'     => $currentUrl,
            'request'         => $currentUrl,
        ]);

        $this->pagination($productsCount);
        $this->productSort();
    }

    /**
     * Render response for ajax search
     *
     * @param array $products
     * @param string $originalSearchQuery
     *
     * @return string JSON response
     */
    private function renderAjaxResponse(array $products, $originalSearchQuery)
    {
        $response = [];
        $response['instant_results'] = false;
        $response['dynamic_results'] = false;

        $token = Tools::getValue('token');
        if ($token != Tools::getToken(false)) {
            return json_encode($response);
        }

        $response['instant_results'] = $this->renderInstantSearchResults($products, $originalSearchQuery);
        $response['dynamic_results'] = $this->renderDynamicSearchResults($products, $originalSearchQuery);

        return json_encode($response);
    }

    /**
     * Render instant search results html
     *
     * @param array $products
     * @param string $originalSearchQuery
     *
     * @return string|false Rendered html or FALSE if instant search is not enabled
     */
    private function renderInstantSearchResults(array $products, $originalSearchQuery)
    {
        $isInstantSearchResultsEnabled = (bool) Configuration::get(Setting::INSTANT_SEARCH);

        if (!$isInstantSearchResultsEnabled) {
            return false;
        }

        $bradTemplatesDir = $this->get('brad_templates_dir');

        $numberOfInstantSearchResults = (int) Configuration::get(Setting::INSTANT_SEARCH_RESULTS_COUNT);
        $instantSearchResults = Arrays::getFirstElements($products, $numberOfInstantSearchResults);

        $imageType = ImageType::getFormatedName('small');
        $showMoreSearchResultsUrl = $this->context->link->getModuleLink(
            $this->module->name,
            'search',
            ['search_query' => $originalSearchQuery]
        );

        $this->context->smarty->assign([
            'instant_search_results' => $instantSearchResults,
            'image_type' => $imageType,
            'more_search_results_url' => $showMoreSearchResultsUrl,
        ]);

        return $this->context->smarty->fetch($bradTemplatesDir.'front/search-input-autocomplete.tpl');
    }

    /**
     * Render dynamic results html
     *
     * @param array $products
     * @param string $originalSearchQuery
     *
     * @return string|false Rendered html or FALSE if instant search is not enabled
     */
    private function renderDynamicSearchResults(array $products, $originalSearchQuery)
    {
        $isDynamicSearchResultsEnabled = (bool) Configuration::get(Setting::DISPLAY_DYNAMIC_SEARCH_RESULTS);

        if (!$isDynamicSearchResultsEnabled) {
            return false;
        }

        if (empty($products)) {
            $this->context->smarty->assign('search_query', $originalSearchQuery);

            return $this->context->smarty->fetch($this->get('brad_templates_dir').'front/results-not-found-alert.tpl');
        }

        $this->addColorsToProductList($products);

        $this->context->smarty->assign([
            'products' => $products,
        ]);

        return $this->context->smarty->fetch(_PS_THEME_DIR_.'product-list.tpl');
    }
}
