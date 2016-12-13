<?php

namespace Invertus\Brad\Controller;

use Brad;
use BradProduct;
use Configuration;
use Invertus\Brad\Traits\GetServiceTrait;
use Product;

abstract class AbstractBradModuleFrontController extends \ModuleFrontController
{
    /**
     * Let's controller get services directly from container
     */
    use GetServiceTrait;

    /**
     * @var Brad
     */
    public $module;

    /**
     * Redirect user to not found page
     */
    protected function redirectToNotFoundPage()
    {
        $this->setRedirectAfter(404);
        $this->redirect();
    }

    /**
     * Format products
     *
     * @param array $products
     *
     * @return array
     */
    protected function formatProducts(array $products)
    {
        $formatedProducts = [];
        $idLang = $this->context->language->id;
        $psOrderOutOfStock = (bool) Configuration::get('PS_ORDER_OUT_OF_STOCK');

        foreach ($products as $product) {

            $allowOosp =
                ($product['_source']['out_of_stock'] == BradProduct::ALLOW_ORDERS_WHEN_OOS ||
                    $product['_source']['out_of_stock'] == BradProduct::USE_GLOBAL_WHEN_OOS) &&
                $psOrderOutOfStock;

            $row = [];
            $row['id_product'] = $product['_source']['id_product'];
            $row['id_image'] = $product['_source']['id_image'];
            $row['out_of_stock'] = $product['_source']['out_of_stock'];
            $row['id_category_default'] = $product['_source']['id_category_default'];
            $row['ean13'] = $product['_source']['ean13'];
            $row['link_rewrite'] = $product['_source']['link_rewrite_lang_'.$idLang];
            $row['allow_oosp'] = $allowOosp;

            $productProperties = Product::getProductProperties($this->context->language->id, $row);

            foreach ($product['_source'] as $key => $value) {
                if (!array_key_exists($key, $productProperties)) {
                    $productProperties[$key] = $value;
                }
            }

            $productProperties['name'] = $product['_source']['name_lang_'.$idLang];
            $productProperties['description_short'] = $product['_source']['short_description_lang_'.$idLang];
            $productProperties['category_name'] = $product['_source']['default_category_name_lang_'.$idLang];

            $formatedProducts[] = $productProperties;
        }

        return $formatedProducts;
    }
}
