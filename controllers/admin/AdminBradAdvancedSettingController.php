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

/**
 * Class AdminBradAdvancedSettingController
 */
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
                    $this->l('Settings marked as "Dynamic index setting" will update current Elasticsearch index if it exists.'),
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
