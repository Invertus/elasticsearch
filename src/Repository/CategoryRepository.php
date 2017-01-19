<?php
/**
 * Copyright (c) 2016-2017 Invertus, JSC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Invertus\Brad\Repository;

use Category;
use Context;
use Db;

/**
 * Class CategoryRepository
 *
 * @package Invertus\Brad\Repository
 */
class CategoryRepository extends \Core_Foundation_Database_EntityRepository
{
    /**
     * Find all categories ids by given shop id
     *
     * @param int $idShop
     *
     * @return array
     */
    public function findAllIdsByShopId($idShop)
    {
        $sql = '
            SELECT cs.`id_category`
            FROM `'.$this->getPrefix().'category_shop` cs
            LEFT JOIN `'.$this->getPrefix().'category` c
                ON c.`id_category` = cs.`id_category`
            WHERE cs.`id_shop` = '.(int)$idShop.'
                AND c.`active` = 1
        ';

        $results = $this->db->select($sql);

        if (!$results) {
            return [];
        }

        $categoriesIds = [];
        foreach ($results as $result) {
            $categoriesIds[] = $result['id_category'];
        }

        return $categoriesIds;
    }

    /**
     * Find child categories
     *
     * @param Category $category
     * @param int $idLang
     * @param int $idShop
     *
     * @return array
     */
    public function findChildCategoriesNamesAndIds(Category $category, $idLang, $idShop)
    {
        static $categories;

        $cacheKey = 'cache_'.(int)$category->id;

        if (isset($categories[$cacheKey])) {
            return $categories[$cacheKey];
        }

        $context = Context::getContext();
        $groups = $context->customer->getGroups();

        $sql = '
            SELECT DISTINCT c.`id_category`, cl.`name`
            FROM `'.$this->getPrefix().'category` c
            LEFT JOIN `'.$this->getPrefix().'category_lang` cl
                ON cl.`id_category` = c.`id_category`
            LEFT JOIN `'.$this->getPrefix().'category_shop` cs
                ON cs.`id_category` = c.`id_category`
            LEFT JOIN `'.$this->getPrefix().'category_group` cg
                ON cg.`id_category` = c.`id_category`
            WHERE cs.`id_shop` = '.(int)$idShop.'
                AND cl.`id_lang` = '.(int)$idLang.'
                AND c.`id_parent` > '.(int)$category->id.'
                AND c.`active` = 1
                AND cg.`id_group` IN ('.implode(',', array_map('intval', $groups)).')
        ';

        $categories[$cacheKey] = $this->db->select($sql);

        if (!is_array($categories) || !$categories) {
            return [];
        }

        return $categories[$cacheKey];
    }

    /**
     * Find all categories names
     * @todo optimize to find only specific categories
     *
     * @param int $idLang
     * @param int $idShop
     *
     * @return array
     */
    public function findAllCategoryNamesAndIds($idLang, $idShop)
    {
        static $categories;

        if ($categories) {
            return $categories;
        }

        $context = Context::getContext();
        $groups = $context->customer->getGroups();

        $sql = '
            SELECT DISTINCT c.`id_category`, cl.`name`
            FROM `'.$this->getPrefix().'category` c
            LEFT JOIN `'.$this->getPrefix().'category_lang` cl
                ON cl.`id_category` = c.`id_category`
            LEFT JOIN `'.$this->getPrefix().'category_shop` cs
                ON cs.`id_category` = c.`id_category`
            LEFT JOIN `'.$this->getPrefix().'category_group` cg
                ON cg.`id_category` = cg.`id_category`
            WHERE cs.`id_shop` = '.(int)$idShop.'
                AND cl.`id_lang` = '.(int)$idLang.'
                AND c.`active` = 1
                AND cg.`id_group` IN ('.implode(',', array_map('intval', $groups)).')
        ';

        $db = Db::getInstance();

        $result = $db->query($sql);

        while ($row = $db->nextRow($result)) {
            $categories[$row['id_category']] = $row['name'];
        }

        return $categories;
    }
}
