<?php

namespace Invertus\Brad\Util;

use Product;

class Validator
{
    /**
     * Check if product is valid for indexing
     *
     * @param Product $product
     *
     * @return bool
     */
    public function isProductValidForIndexing(Product $product)
    {
        if (!$product->active || !in_array($product->visibility, ['both', 'search'])) {
            return false;
        }

        return true;
    }
}
