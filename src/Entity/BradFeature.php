<?php

/**
 * Class BradFeature
 */
class BradFeature extends Feature
{
    /**
     * Get repository class name
     *
     * @return string
     */
    public static function getRepositoryClassName()
    {
        return 'Invertus\Brad\Repository\FeatureRepository';
    }
}
