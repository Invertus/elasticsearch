<?php

namespace Invertus\Brad\Service\Elasticsearch\Builder;

use Context;
use Invertus\Brad\Config\Consts\Sort;

abstract class AbstractQueryBuilder
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * AbstractQueryBuilder constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Build query sort part
     *
     * @param string $sortBy
     * @param string $sortWay
     *
     * @return array
     */
    protected function buildSort($sortBy, $sortWay)
    {
        $idLang = (int) $this->context->language->id;
        $fieldNameToSortBy = null;

        switch ($sortBy) {
            case Sort::BY_NAME:
                $fieldNameToSortBy = $sortBy.'_lang_'.$idLang;
                break;
            case Sort::BY_PRICE:
                //@todo: BRAD add sorting by price
                break;
            case Sort::BY_STOCK:
                //@todo: BRAD add sorting by stock
                break;
            default:
            case Sort::BY_RELEVANCE:
                $fieldNameToSortBy = '_score';
                break;
        }

        $sortWay = in_array($sortWay, [Sort::WAY_DESC, Sort::WAY_ASC]) ? $sortWay : Sort::WAY_DESC;

        return [
            [
                $fieldNameToSortBy => [
                    'order' => $sortWay,
                ],
            ]
        ];
    }
}
