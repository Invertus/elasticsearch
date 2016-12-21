<?php

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
     * Transform filter input name into elasticsearch field name
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
}
