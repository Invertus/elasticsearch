<?php

use Invertus\Brad\Config\Setting;

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

        /** @var Core_Business_ConfigurationInterface $configuration */
        $configuration = $this->get('configuration');

        $isFiltersEnabled = (bool) $configuration->get(Setting::ENABLE_FILTERS);
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

    public function postProcess()
    {
        $filters = Tools::getValue('filters');
    }
}