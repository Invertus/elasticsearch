<?php

/**
 * Class BradProduct
 */
class BradProduct extends Product
{
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
