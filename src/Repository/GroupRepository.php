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

/**
 * Class GroupRepository
 *
 * @package Invertus\Brad\Repository
 */
class GroupRepository extends \Core_Foundation_Database_EntityRepository
{
    /**
     * Find all group ids by shop id
     *
     * @param int $idShop
     *
     * @return array
     */
    public function findAllIdsByShopId($idShop)
    {
        $ids = [];

        $sql = '
            SELECT gs.`id_group` 
            FROM `'.$this->getPrefix().'group_shop` gs
            WHERE gs.`id_shop` = '.(int)$idShop.'
        ';

        $result = $this->db->select($sql);

        if (!$result) {
            return $ids;
        }

        foreach ($result as $item) {
            $ids[] = (int) $item['id_group'];
        }

        return $ids;
    }
}
