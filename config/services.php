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

return [
    /*
     |-----------------------------------------------
     | Module installer services
     |-----------------------------------------------
     */

    'db_installer' => [
        'class' => 'Invertus\Brad\Install\DbInstaller',
        'arguments' => ['db', 'brad_dir'],
    ],

    'installer' => [
        'class' => 'Invertus\Brad\Install\Installer',
        'arguments' => ['module', 'db_installer'],
    ],

    /*
     |-----------------------------------------------
     | Elasticsearch services
     |-----------------------------------------------
     */

    'elasticsearch.manager' => [
        'class' => 'Invertus\Brad\Service\Elasticsearch\ElasticsearchManager',
        'arguments' => ['elasticsearch.client', '@BRAD_ELASTICSEARCH_INDEX_PREFIX', 'logger'],
    ],

    'elasticsearch.client' => [
        'class' => 'Invertus\Brad\Service\Elasticsearch\Builder\ClientBuilder',
        'arguments' => ['configuration'],
        'call' => [
            'method' => 'buildClient',
            'factory' => true,
        ],
    ],

    'elasticsearch.indexer' => [
        'class' => 'Invertus\Brad\Service\Elasticsearch\ElasticsearchIndexer',
        'arguments' => [
            'elasticsearch.manager',
            'elasticsearch.builder.document_builder',
            'elasticsearch.builder.index_builder',
            'logger',
        ],
    ],

    'elasticsearch.search' => [
        'class' => 'Invertus\Brad\Service\Elasticsearch\ElasticsearchSearch',
        'arguments' => ['elasticsearch.manager'],
    ],

    'elasticsearch.helper' => [
        'class' => 'Invertus\Brad\Service\Elasticsearch\ElasticsearchHelper',
        'arguments' => ['elasticsearch.manager'],
    ],

    /*
     |-----------------------------------------------
     | Elasticsearch builder services
     |-----------------------------------------------
     */

    'elasticsearch.builder.filter_query_builder' => [
        'class' => 'Invertus\Brad\Service\Elasticsearch\Builder\FilterQueryBuilder',
    ],

    'elasticsearch.builder.document_builder' => [
        'class' => 'Invertus\Brad\Service\Elasticsearch\Builder\DocumentBuilder',
        'arguments' => ['context.link', 'context.shop', 'em', 'configuration'],
    ],

    'elasticsearch.builder.index_builder' => [
        'class' => 'Invertus\Brad\Service\Elasticsearch\Builder\IndexBuilder',
        'arguments' => ['configuration', 'em'],
    ],

    /*
     |-----------------------------------------------
     | Services
     |-----------------------------------------------
     */

    'indexer' => [
        'class' => 'Invertus\Brad\Service\Indexer',
        'arguments' => [
            'elasticsearch.indexer',
            'em',
            'configuration',
            'elasticsearch.builder.document_builder',
            'logger',
        ],
    ],

    'filter_service' => [
        'class' => 'Invertus\Brad\Service\FilterService',
        'arguments' => ['elasticsearch.builder.filter_query_builder', 'elasticsearch.search'],
    ],

    'search_service' => [
        'class' => 'Invertus\Brad\Service\SearchService',
        'arguments' => ['elasticsearch.search'],
    ],

    /*
     |-----------------------------------------------
     | CronJob services
     |-----------------------------------------------
     */

    'task_runner' => [
        'class' => 'Invertus\Brad\Cron\TaskRunner',
        'arguments' => ['container'],
    ],

    'task.index_products' => [
        'class' => 'Invertus\Brad\Cron\Task\IndexProductsTask',
        'arguments' => ['elasticsearch.manager', 'indexer'],
    ],

    /*
     |-----------------------------------------------
     | Logger service
     |-----------------------------------------------
     */

    'logger' => [
        'class' => 'Invertus\Brad\Logger\Logger',
        'arguments' => ['brad_log_dir'],
    ],

    /*
     |-----------------------------------------------
     | Templating services
     |-----------------------------------------------
     */

    'templating' => [
        'class' => 'Invertus\Brad\Template\Templating',
        'arguments' => ['brad_templates_dir', 'filter_block_templating', 'em'],
    ],

    'filter_block_templating' => [
        'class' => 'Invertus\Brad\Template\FilterBlockTemplating',
        'arguments' => ['em', 'elasticsearch.helper'],
    ],
];
