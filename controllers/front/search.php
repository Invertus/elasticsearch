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
use Invertus\Brad\Util\Arrays;

/**
 * Class BradSearchModuleFrontController
 */
class BradSearchModuleFrontController extends AbstractBradModuleFrontController
{
    /**
     * @var Core_Business_ConfigurationInterface $configuration
     */
    private $configuration;

    /**
     * BradSearchModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->configuration = $this->get('configuration');
    }

    /**
     * Check if elasticsearch connection is available & index exists
     */
    public function init()
    {
        $isSearchEnabled = (bool) $this->configuration->get(Setting::ENABLE_SEARCH);
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
        $originalSearchQuery = Tools::getValue('search_query', '');
        $searchQuery = Tools::replaceAccentedChars(urldecode($originalSearchQuery));

        $orderBy = Tools::getValue('orderby', Sort::BY_RELEVANCE);
        $orderWay = Tools::getValue('orderway', Sort::WAY_DESC);
        $page = (int) Tools::getValue('p');
        $size = (int) Tools::getValue('n');

        if (0 >= $page) {
            $page = 1;
        }

        if (0 >= $size) {
            $size = isset($this->context->cookie->nb_item_per_page) ?
                (int) $this->context->cookie->nb_item_per_page :
                (int) $this->configuration->get('PS_PRODUCTS_PER_PAGE');
        }

        $from = (int) ($size * ($page - 1));

        /** @var \Invertus\Brad\Service\Elasticsearch\Builder\SearchQueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $this->get('elasticsearch.builder.search_query_builder');
        $productsQuery = $searchQueryBuilder->buildProductsQuery($searchQuery, $from, $size, $orderBy, $orderWay);

        /** @var \Invertus\Brad\Service\Elasticsearch\ElasticsearchSearch $elasticsearchSearch */
        $elasticsearchSearch = $this->get('elasticsearch.search');
        $products = $elasticsearchSearch->searchProducts($productsQuery, $this->context->shop->id);

        $formattedProducts = $this->formatProducts($products);

        if ($this->isXmlHttpRequest()) {
            die($this->renderAjaxResponse($formattedProducts, $originalSearchQuery));
        }

        $productsCountQuery = $searchQueryBuilder->buildProductsQuery($searchQuery);
        $productsCount = (int) $elasticsearchSearch->countProducts($productsCountQuery, $this->context->shop->id);

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
            'nbProducts' => $productsCount,
            'search_query' => $originalSearchQuery,
            'homeSize' => Image::getSize(ImageType::getFormatedName('home')),
            'current_url' => $currentUrl,
            'request' => $currentUrl,
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
        $isInstantSearchResultsEnabled = (bool) $this->configuration->get(Setting::INSTANT_SEARCH);

        if (!$isInstantSearchResultsEnabled) {
            return false;
        }

        $bradTemplatesDir = $this->get('brad_templates_dir');

        $numberOfInstantSearchResults = (int) $this->configuration->get(Setting::INSTANT_SEARCH_RESULTS_COUNT);
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
        $isDynamicSearchResultsEnabled = (bool) $this->configuration->get(Setting::DISPLAY_DYNAMIC_SEARCH_RESULTS);

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

    /**
     * Format products
     *
     * @param array $products
     *
     * @return array
     */
    private function formatProducts(array $products)
    {
        $formatedProducts = [];
        $idLang = $this->context->language->id;
        $psOrderOutOfStock = (bool) $this->configuration->get('PS_ORDER_OUT_OF_STOCK');

        foreach ($products as $product) {

            $allowOosp =
                ($product['_source']['out_of_stock'] == BradProduct::ALLOW_ORDERS_WHEN_OOS ||
                $product['_source']['out_of_stock'] == BradProduct::USE_GLOBAL_WHEN_OOS) &&
                $psOrderOutOfStock;

            $row = [];
            $row['id_product'] = $product['_source']['id_product'];
            $row['id_image'] = $product['_source']['id_image'];
            $row['out_of_stock'] = $product['_source']['out_of_stock'];
            $row['id_category_default'] = $product['_source']['id_category_default'];
            $row['ean13'] = $product['_source']['ean13'];
            $row['link_rewrite'] = $product['_source']['link_rewrite_lang_'.$idLang];
            $row['allow_oosp'] = $allowOosp;

            $productProperties = Product::getProductProperties($this->context->language->id, $row);

            foreach ($product['_source'] as $key => $value) {
                if (!array_key_exists($key, $productProperties)) {
                    $productProperties[$key] = $value;
                }
            }

            $productProperties['name'] = $product['_source']['name_lang_'.$idLang];
            $productProperties['description_short'] = $product['_source']['short_description_lang_'.$idLang];
            $productProperties['category_name'] = $product['_source']['default_category_name_lang_'.$idLang];

            $formatedProducts[] = $productProperties;
        }

        return $formatedProducts;
    }
}
