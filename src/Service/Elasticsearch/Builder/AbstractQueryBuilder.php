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

namespace Invertus\Brad\Service\Elasticsearch\Builder;

use Context;
use Invertus\Brad\Config\Sort;

/**
 * Class AbstractQueryBuilder
 *
 * @package Invertus\Brad\Service\Elasticsearch\Builder
 */
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
