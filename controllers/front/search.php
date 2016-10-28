<?php

use Invertus\Brad\Config\Consts\Sort;
use Invertus\Brad\Config\Setting;
use Invertus\Brad\Util\Arrays;

class BradSearchModuleFrontController extends AbstractModuleFrontController
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
        /** @var \Invertus\Brad\Service\Elasticsearch\ElasticsearchManager $elasticsearchManager */
        $elasticsearchManager = $this->get('elasticsearch.manager');

        if (!$elasticsearchManager->isConnectionAvailable() ||
            !$elasticsearchManager->isIndexCreated($this->context->shop->id)
        ) {
            if ($this->isXmlHttpRequest()) {
                die(json_encode(['instant_results' => false, 'dynamic_results' => false]));
            }

            $this->setRedirectAfter(404);
            $this->redirect();
        }

        parent::init();
    }

    /**
     * Perform search
     */
    public function postProcess()
    {
        $searchQuery = Tools::getValue('query', '');
        $sortBy = Tools::getValue('sort_by', Sort::BY_RELEVANCE);
        $sortWay = Tools::getValue('sort_way', Sort::WAY_DESC);
        $page = (int) Tools::getValue('page', 1);

        if (0 >= $page) {
            $page = 1;
        }

        $size = (int) $this->configuration->get('PS_PRODUCTS_PER_PAGE');
        $from = (int) ($size * ($page - 1));

        /** @var \Invertus\Brad\Service\Elasticsearch\Builder\SearchQueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $this->get('elasticsearch.builder.search_query_builder');
        $productsQuery = $searchQueryBuilder->buildProductsQuery($searchQuery, $from, $size, $sortBy, $sortWay);

        /** @var \Invertus\Brad\Service\Elasticsearch\ElasticsearchSearch $elasticsearchSearch */
        $elasticsearchSearch = $this->get('elasticsearch.search');

        $products = $elasticsearchSearch->searchProducts($productsQuery, $this->context->shop->id);


        $formattedProducts = $this->formatProducts($products);

        if ($this->isXmlHttpRequest()) {
            die($this->renderAjaxResponse($formattedProducts));
        }

        $productsCountQuery = $searchQueryBuilder->buildProductsQuery($searchQuery);
        $productsCount = $elasticsearchSearch->countProducts($productsCountQuery, $this->context->shop->id);

        //@todo: BRAD handle products list display
    }

    /**
     * Render response for ajax search
     *
     * @param array $products
     *
     * @return string JSON response
     */
    private function renderAjaxResponse(array $products)
    {
        $response = [];
        $response['instant_results'] = false;
        $response['dynamic_results'] = false;

        $token = Tools::getValue('token');
        if ($token != Tools::getToken(false)) {
            return json_encode($response);
        }

        $isInstantSearchResultsEnabled = (bool) $this->configuration->get(Setting::INSTANT_SEARCH);
        $isDynamicSearchResultsEnabled = (bool) $this->configuration->get(Setting::DISPLAY_DYNAMIC_SEARCH_RESULTS);

        if ($isInstantSearchResultsEnabled) {

            $numberOfInstantSearchResults = (int) $this->configuration->get(Setting::INSTANT_SEARCH_RESULTS_COUNT);
            $instantSearchResults = Arrays::getFirstElements($products, $numberOfInstantSearchResults);

            $imageType = ImageType::getFormatedName('small');

            $this->context->smarty->assign([
                'instant_search_results' => $instantSearchResults,
                'image_type' => $imageType,
            ]);

            $response['instant_results'] =
                $this->context->smarty->fetch($this->get('brad_templates_dir').'front/search-input-autocomplete.tpl');
        }

        if ($isDynamicSearchResultsEnabled) {
            $this->addColorsToProductList($products);

            $this->context->smarty->assign([
                'products' => $products,
            ]);

            $response['dynamic_results'] = $this->context->smarty->fetch(_PS_THEME_DIR_.'product-list.tpl');
        }

        return json_encode($response);
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
