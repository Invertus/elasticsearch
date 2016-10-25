<?php

class BradCategory extends Category
{
    /**
     * Get class name of category repository
     *
     * @return string
     */
    public static function getRepositoryClassName()
    {
        return '\Invertus\Brad\Repository\CategoryRepository';
    }
}
