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

namespace Invertus\Brad\Converter;

use BradFilter;
use Configuration;
use Context;
use Invertus\Brad\DataType\FilterStruct;

/**
 * Class NameTransformer
 *
 * @package Invertus\Brad\Converter
 */
class NameConverter
{
    /**
     * Convert filter input name into elasticsearch field name
     *
     * @param string $filterIntputName
     *
     * @return string
     */
    public static function getElasticsearchFieldName($filterIntputName)
    {
        $context = Context::getContext();
        $fieldName = '';

        if ('quantity' == $filterIntputName) {
            $orderOutOfStock = (bool) Configuration::get('PS_ORDER_OUT_OF_STOCK');
            switch ($orderOutOfStock) {
                case true:
                    $fieldName = 'in_stock_when_global_oos_allow_orders';
                    break;
                case false:
                    $fieldName = 'in_stock_when_global_oos_deny_orders';
                    break;
            }
        } elseif ('price' == $filterIntputName) {
            $idGroup    = $context->customer->id_default_group;
            $idCurrency = $context->currency->id;
            $idCountry  = $context->country->id;
            $fieldName  = sprintf('price_group_%s_country_%s_currency_%s', $idGroup, $idCountry, $idCurrency);
        } elseif ('manufacturer' == $filterIntputName) {
            $fieldName = 'id_manufacturer';
        } elseif ('weight' == $filterIntputName ||
            0 === strpos($filterIntputName, 'feature') ||
            0 === strpos($filterIntputName, 'attribute_group')
        ) {
            $fieldName = $filterIntputName;
        } elseif ('category' == $filterIntputName) {
            $fieldName = 'categories';
        }

        return $fieldName;
    }

    /**
     * Get filter input name
     *
     * @param FilterStruct $filterStruct
     *
     * @return string
     */
    public static function getInputFieldName(FilterStruct $filterStruct)
    {
        switch ($filterStruct->getFilterType()) {
            case BradFilter::FILTER_TYPE_ATTRIBUTE_GROUP:
                return 'attribute_group_'.$filterStruct->getIdKey();
            case BradFilter::FILTER_TYPE_FEATURE:
                return 'feature_'.$filterStruct->getIdKey();
            case BradFilter::FILTER_TYPE_PRICE:
                return 'price';
            case BradFilter::FILTER_TYPE_MANUFACTURER:
                return 'manufacturer';
            case BradFilter::FILTER_TYPE_QUANTITY:
                return 'quantity';
            case BradFilter::FILTER_TYPE_WEIGHT:
                return 'weight';
            case BradFilter::FILTER_TYPE_CATEGORY:
                return 'category';
        }

        return '';
    }

    /**
     * Convert elasticsearch input name into filter input field name
     *
     * @param string $fieldName
     *
     * @return string
     */
    public static function getInputNameFromElasticsearchFieldName($fieldName)
    {
        if ('weight' == $fieldName ||
            0 === strpos($fieldName, 'feature') ||
            0 === strpos($fieldName, 'attribute_group')
        ) {
            return $fieldName;
        } elseif ('id_manufacturer' == $fieldName) {
            return 'manufacturer';
        } elseif (0 === strpos($fieldName, 'price')) {
            return 'price';
        } elseif ('categories' == $fieldName) {
            return 'category';
        } elseif (0 === strpos($fieldName, 'in_stock')) {
            return 'quantity';
        }

        return '';
    }
}
