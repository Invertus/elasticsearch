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

/**
 * Class BradProduct
 */
class BradProduct extends Product
{
    const DENY_ORDERS_WHEN_OOS = 0;
    const ALLOW_ORDERS_WHEN_OOS = 1;
    const USE_GLOBAL_WHEN_OOS = 2;

    /**
     * Get product repository class name
     *
     * @return string
     */
    public static function getRepositoryClassName()
    {
        return '\Invertus\Brad\Repository\ProductRepository';
    }

    /**
     * Get stock criteria values
     *
     * @return array
     */
    public static function getStockCriterias()
    {
        $brad = Module::getInstanceByName('brad');

        $criterias = [
            [
                'value' => 1,
                'name' => $brad->l('In stock', __CLASS__),
            ],
            [
                'value' => 0,
                'name' => $brad->l('Out of stock', __CLASS__),
            ],
        ];

        return $criterias;
    }
}
