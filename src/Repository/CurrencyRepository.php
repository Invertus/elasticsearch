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
 * Class CurrencyRepository
 *
 * @package Invertus\Brad\Repository
 */
class CurrencyRepository extends \Core_Foundation_Database_EntityRepository
{
    /**
     * Find all currencies ids by shop id
     *
     * @param int $idShop
     *
     * @return array
     */
    public function findAllIdsByShopId($idShop)
    {
        $ids = [];

        $sql = '
            SELECT c.`id_currency`
            FROM `'.$this->getPrefix().'currency` c
            LEFT JOIN `'.$this->getPrefix().'currency_shop` cs
                ON cs.`id_currency` = c.`id_currency`
            WHERE cs.`id_shop` = '.(int)$idShop.'
                AND c.`active` = 1
        ';

        $results = $this->db->select($sql);

        if (!$results) {
            return $ids;
        }

        foreach ($results as $result) {
            $ids[] = (int) $result['id_currency'];
        }

        return $ids;
    }
}
