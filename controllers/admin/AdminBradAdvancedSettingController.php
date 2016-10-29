<?php

use Invertus\Brad\Config\Setting;

class AdminBradAdvancedSettingController extends AbstractAdminBradModuleController
{
    /**
     * Update dynamic index settings
     */
    public function processUpdateOptions()
    {
        /** @var Core_Business_ConfigurationInterface $configuration */
        $configuration = $this->get('configuration');

        $oldValues = [
            'number_of_replicas' => (int) $configuration->get(Setting::NUMBER_OF_REPLICAS_ADVANCED),
            'refresh_interval' => (int) $configuration->get(Setting::REFRESH_INTERVAL_ADVANCED).'s',
        ];

        parent::processUpdateOptions();

        if (!empty($this->errors)) {
            return;
        }

        $updatedValues = [
            'number_of_replicas' => (int) $configuration->get(Setting::NUMBER_OF_REPLICAS_ADVANCED),
            'refresh_interval' => (int) $configuration->get(Setting::REFRESH_INTERVAL_ADVANCED).'s',
        ];

        $indexSettings = [];

        foreach ($updatedValues as $name => $value) {
            if ($updatedValues[$name] == $oldValues[$name]) {
                continue;
            }

            $indexSettings[$name] = $value;
        }

        if (empty($indexSettings)) {
            return;
        }

        /** @var \Invertus\Brad\Service\Elasticsearch\ElasticsearchIndexer $elasticsearchIndexer */
        $elasticsearchIndexer = $this->get('elasticsearch.indexer');
        $hasIndexUpdated = $elasticsearchIndexer->updateIndex($this->context->shop->id, $indexSettings);

        if (!$hasIndexUpdated) {
            $this->warnings[] = $this->l('Could not update Elasticsearch index settings.');
        }
    }

    protected function initOptions()
    {
        $this->fields_options = [
            'general_settings' => [
                'title' => $this->l('Indexing configuration'),
                'icon' => 'icon-cogs',
                'fields' => [
                    Setting::BULK_REQUEST_SIZE_ADVANCED => [
                        'title' => $this->l('Bulk request size'),
                        'hint' => $this->l('Size of bulk request which are sent to Elasticsearch when indexing'),
                        'validation' => 'isUnsignedInt',
                        'type' => 'text',
                        'class' => 'fixed-width-xxl',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
            'elasticserach_index_settings' => [
                'title' => $this->l('Elasticsearch index settings'),
                'icon' => 'icon-cogs',
                'description' =>
                    $this->l('These settings will be used to create Elasticsearch index before indexing products.')
                    .' '.
                    $this->l('Settings marked as "Dynamic index setting" will update current Elasticsearch index settings.'),
                'fields' => [
                    Setting::NUMBER_OF_SHARDS_ADVANCED => [
                        'title' => $this->l('Number of shards'),
                        'validation' => 'isUnsignedInt',
                        'type' => 'text',
                        'class' => 'fixed-width-xxl',
                    ],
                    Setting::NUMBER_OF_REPLICAS_ADVANCED => [
                        'title' => $this->l('Number of replicas'),
                        'validation' => 'isUnsignedInt',
                        'type' => 'text',
                        'class' => 'fixed-width-xxl',
                        'desc' => $this->l('Dynamic index setting'),
                    ],
                    Setting::REFRESH_INTERVAL_ADVANCED => [
                        'title' => $this->l('Refresh interval'),
                        'validation' => 'isUnsignedInt',
                        'type' => 'text',
                        'suffix' => $this->l('seconds'),
                        'class' => 'fixed-width-xxl',
                        'desc' => $this->l('Dynamic index setting'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }
}
