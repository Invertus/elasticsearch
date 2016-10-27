<?php

use Invertus\Brad\Config\Consts\Sort;
use Invertus\Brad\Config\Setting;

class BradSearchModuleFrontController extends AbstractModuleFrontController
{
    //@todo: BRAD do something if elasticsearch connection is not available

    /**
     * @var Core_Business_ConfigurationInterface $configuration
     */
    private $configuration;

    public function __construct()
    {
        parent::__construct();

        $this->configuration = $this->get('configuration');
    }

    public function postProcess()
    {
        $searchQuery = Tools::getValue('brad_search_query', '');
        $sortBy = Tools::getValue('brad_sort_by', Sort::BY_NAME);
        $sortWay = Tools::getValue('brad_sort_way', Sort::WAY_DESC);
        $page = (int) Tools::getValue('brad_search_page', 1);

        if (0 >= $page) {
            $page = 1;
        }

        //@todo: BRAD check if search query exists

        $size = (int) $this->configuration->get('PS_PRODUCTS_PER_PAGE');
        $from = (int) ($size * ($page - 1));

        /** @var \Invertus\Brad\Service\Elasticsearch\Builder\SearchQueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $this->get('elasticsearch.builder.search_query_builder');

        $productsQuery = $searchQueryBuilder->buildProductsQuery($searchQuery, $from, $size, $sortBy, $sortWay);
        $productsCountQuery = $searchQueryBuilder->buildProductsQuery($searchQuery);

        /** @var \Invertus\Brad\Service\Elasticsearch\ElasticsearchSearch $elasticsearchSearch */
        $elasticsearchSearch = $this->get('elasticsearch.search');

        $products = $elasticsearchSearch->searchProducts($productsQuery, $this->context->shop->id);
        $productsCount = $elasticsearchSearch->countProducts($productsCountQuery, $this->context->shop->id);

        $formattedProducts = $this->formatProducts($products);

        if ($this->isXmlHttpRequest()) {

            $response = [];
            $response['instant_results'] = false;
            $response['dynamic_results'] = false;

            $isInstantSearchResultsEnabled = (bool) $this->configuration->get(Setting::INSTANT_SEARCH);
            $isDynamicSearchResultsEnabled = (bool) $this->configuration->get(Setting::DISPLAY_DYNAMIC_SEARCH_RESULTS);

            //@todo: Render response

            die(json_encode($response));
        }

        //@todo: BRAD handle products list display
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

        foreach ($products as $product) {

            $row = [];
            $row['id_product'] = $product['_source']['id_product'];

            //@todo: BRAD collect product details & index more fields
        }

        return $formatedProducts;
    }
}
