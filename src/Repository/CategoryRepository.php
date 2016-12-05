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
            WHERE cs.`id_shop` = '.(int)$idShop.'
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
    public function findChildCategories(Category $category, $idLang, $idShop)
    {
        $sql = '
            SELECT c.`id_category`, cl.`name`
            FROM `'.$this->getPrefix().'category` c
            LEFT JOIN `'.$this->getPrefix().'category_lang` cl
                ON cl.`id_category` = c.`id_category`
            LEFT JOIN `'.$this->getPrefix().'category_shop` cs
                ON cs.`id_category` = c.`id_category`
            WHERE cs.`id_shop` = '.(int)$idShop.'
                AND cl.`id_lang` = '.(int)$idLang.'
                AND c.`nleft` >= '.(int)$category->nleft.'
                AND c.`nright` <= '.(int)$category->nright.'
        ';

        $results = $this->db->select($sql);

        if (!is_array($results) || !$results) {
            return [];
        }

        return $results;
    }
}
