<?php

namespace Invertus\Brad\Service\Elasticsearch\Builder;

use Attribute;
use Category;
use Feature;
use FeatureValue;
use Manufacturer;
use Product;

class DocumentBuilder
{

    /**
     * Build product fields for indexing
     *
     * @param Product $product
     *
     * @return array
     */
    public function buildProductBody(Product $product)
    {
        $body = [];
        $body['id_supplier'] = $product->id_supplier;
        $body['id_manufacturer'] = $product->id_manufacturer;
        $body['manufacturer_name'] = Manufacturer::getNameById($product->id_manufacturer);
        $body['id_category_default'] = $product->id_category_default;
        $body['on_sale'] = $product->on_sale;
        $body['ean13'] = $product->ean13;
        $body['reference'] = $product->reference;
        $body['upc'] = $product->upc;
        $body['price'] = $product->price;
        $body['show_price'] = $product->show_price;
        $body['quantity'] = $product->quantity;
        $body['minimal_quantity'] = $product->minimal_quantity;
        $body['available_for_order'] = $product->available_for_order;
        $body['condition'] = $product->condition;
        $body['id_image'] = Product::getCover($product->id)['id_image'];
        $body['id_combination_default'] = $product->getDefaultIdProductAttribute();

        foreach ($product->name as $idLang => $name) {
            $body['lang_'.$idLang.'_name'] = $name;
        }

        foreach ($product->description as $idLang => $description) {
            $body['lang_'.$idLang.'_description'] = $description;
        }

        foreach ($product->description_short as $idLang => $shortDescription) {
            $body['lang_'.$idLang.'_short_description'] = $shortDescription;
        }

        foreach ($product->link_rewrite as $idLang => $linkRewrite) {
            $body['lang_'.$idLang.'_link_rewrite'] = $linkRewrite;
        }

        $features = $product->getFeatures();
        $attributes = Product::getAttributesInformationsByProduct($product->id);

        if ($features) {
            foreach ($features as $feature) {
                $featureObj = new Feature($feature['id_feature']);
                $featureValueObj = new FeatureValue($feature['id_feature_value']);

                foreach ($featureObj->name as $idLang => $name) {
                    $body['lang_'.$idLang.'_feature_'.$featureObj->id] = $name;
                    $body['lang_'.$idLang.'_feature_value_'.$featureValueObj->id] = $featureValueObj->value[$idLang];
                }

                $body['feature_'.$featureObj->id] = $featureValueObj->id;
            }
        }

        if ($attributes) {
            foreach ($attributes as $attribute) {
                $attributeObj = new Attribute($attribute['id_attribute']);

                foreach ($attributeObj->name as $idLang => $name) {
                    $body['lang_'.$idLang.'_attribute_'.$attributeObj->id] = $name;
                }

                $body['attribute_group_'.$attribute['id_attribute_group']][] = $attributeObj->id;
            }
        }

        return $body;
    }

    public function buildCategory(Category $category)
    {

    }
}
