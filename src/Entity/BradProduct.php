<?php

use Invertus\Brad\Repository\ProductRepository;

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
        return ProductRepository::class;
    }
}
