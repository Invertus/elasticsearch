<?php

/**
 * Class BradCurrency
 */
class BradCurrency extends Currency
{
    /**
     * Get currency repository class name
     *
     * @return string
     */
    public static function getRepositoryClassName()
    {
        return '\Invertus\Brad\Repository\CurrencyRepository';
    }
}
