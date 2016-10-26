<?php

use Invertus\Brad\Config\Consts;

class BradSearchModuleFrontController extends AbstractModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function postProcess()
    {
        $searchQuery = Tools::getValue('brad_search_query') ?: null;
        $sortBy = Tools::getValue('brad_sort_by') ?: Consts::SORTING_TYPE_NAME;
        $sortWay = Tools::getValue('brad_sort_way') ?: Consts::SORTING_WAY_DESC;
        $page = (int) Tools::getValue('brad_search_page') ?: 1;

        //@todo: validate params

        /** @var \Invertus\Brad\Service\Elasticsearch\Builder\SearchQueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $this->get('elasticsearch.builder.search_query_builder');
        $query = $searchQueryBuilder->buildQuery($searchQuery, $page, $sortBy, $sortWay);

        /** @var \Invertus\Brad\Service\Elasticsearch\ElasticsearchManager $manager */
        $manager = $this->get('elasticsearch.manager');

        $params = [];
        $params['index'] = $manager->getIndexPrefix().$this->context->shop->id;
        $params['type'] = 'products';
        $params['body'] = $query;

        //@todo: parse results
        $results = $manager->getClient()->search($query);

        //@todo: format results

        if ($this->isXmlHttpRequest()) {
            //@todo: return results if ajax call (dynamic content & instant search)
            die('OK');
        }

        //@todo: return results if not ajax call
    }
}
