<?php

/**
 * Class BradCountry
 */
class BradCountry extends Country
{
    /**
     * Get country repository class name
     *
     * @return string
     */
    public static function getRepositoryClassName()
    {
        return '\Invertus\Brad\Repository\CountryRepository';
    }
}
