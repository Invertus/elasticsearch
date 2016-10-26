<?php

namespace Invertus\Brad\Service\Elasticsearch\Builder;

use Core_Business_ConfigurationInterface as ConfigurationInterface;
use Language;

class SearchQueryBuilder
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var Language
     */
    private $language;

    public function __construct(ConfigurationInterface $configuration, Language $language)
    {
        $this->configuration = $configuration;
        $this->language = $language;
    }

    /**
     * Build search query
     *
     * @param string $query
     * @param int $page
     * @param string $sortBy
     * @param string $sortWay
     *
     * @return array
     */
    public function buildQuery($query, $page, $sortBy, $sortWay)
    {
        $perPage = (int) $this->configuration->get('PS_PRODUCTS_PER_PAGE');
        $from = $perPage * ($page - 1);

        return [
            'q' => [
                'bool' => [
                    'should' => [
                        [
                            'match' => [
                                'name_lang_'.$this->language->id => [
                                    'query' => $query,
                                    'boost' => 2,
                                ],
                            ],
                        ],
                        [
                            'match' => [
                                'description_lang_'.$this->language->id => [
                                    'query' => $query,
                                    'boost' => 1.5,
                                ],
                            ],
                        ],
                        [
                            'match' => [
                                'short_description_lang_'.$this->language->id => [
                                    'query' => $query,
                                    'boost' => 1.5,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
//            'sort' => [
//
//            ],
//            'from' => $from,
//            'size' => $perPage,
        ];
    }
}
