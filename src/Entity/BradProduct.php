<?php

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
}
