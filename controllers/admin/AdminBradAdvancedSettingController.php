<?php

use Invertus\Brad\Config\Setting;

class AdminBradAdvancedSettingController extends AbstractAdminBradModuleController
{
    protected function initOptions()
    {
        //@todo: update dynamic settings
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
                    ],
                    Setting::REFRESH_INTERVAL_ADVANCED => [
                        'title' => $this->l('Refresh interval'),
                        'validation' => 'isCleanHtml',
                        'type' => 'text',
                        'class' => 'fixed-width-xxl',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }
}
