<?php

/**
 * Class BradGroup
 */
class BradGroup extends Group
{
    /**
     * Get Group repository class name
     *
     * @return string
     */
    public static function getRepositoryClassName()
    {
        return '\Invertus\Brad\Repository\GroupRepository';
    }
}
