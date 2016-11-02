<?php

namespace Invertus\Brad\Service\Elasticsearch\Builder;

use Context;
use Invertus\Brad\Config\Sort;

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
     * @param string $orderBy
     * @param string $orderWay
     *
     * @return array
     */
    protected function buildOrderQuery($orderBy, $orderWay)
    {
        $idLang = (int) $this->context->language->id;
        $fieldNameToSortBy = null;

        switch ($orderBy) {
            case Sort::BY_NAME:
                $fieldNameToSortBy = 'name_lang_'.$idLang.'.raw';
                break;
            case Sort::BY_PRICE:
                $fieldNameToSortBy =
                    'price_group_'.$this->context->customer->id_default_group.
                    '_country_'.$this->context->country->id.
                    '_currency_'.$this->context->currency->id;
                break;
            case Sort::BY_QUANTITY:
                $fieldNameToSortBy = 'total_quantity';
                break;
            case Sort::BY_REFERENCE:
                $fieldNameToSortBy = 'reference';
                break;
            default:
            case Sort::BY_RELEVANCE:
                $fieldNameToSortBy = '_score';
                break;
        }

        $orderWay = in_array($orderWay, [Sort::WAY_DESC, Sort::WAY_ASC]) ? $orderWay : Sort::WAY_DESC;

        return [
            [
                $fieldNameToSortBy => [
                    'order' => $orderWay,
                ],
            ]
        ];
    }
}
