<?php

/**
 * Class BradAttributeGroup
 */
class BradAttributeGroup extends AttributeGroup
{
    /**
     * Get repository class name
     *
     * @return string
     */
    public static function getRepositoryClassName()
    {
        return 'Invertus\Brad\Repository\AttributeGroupRepository';
    }
}
