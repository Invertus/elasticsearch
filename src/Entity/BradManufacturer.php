<?php

/**
 * Class BradManufacturer
 */
class BradManufacturer extends Manufacturer
{
    /**
     * @return string
     */
    public static function getRepositoryClassName()
    {
        return 'Invertus\Brad\Repository\ManufacturerRepository';
    }
}
