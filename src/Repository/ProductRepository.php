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
 * Class ProductRepository
 *
 * @package Invertus\Brad\Repository
 */
class ProductRepository extends \Core_Foundation_Database_EntityRepository
{
    /**
     * Find all products ids by given sop id
     *
     * @param int $idShop
     *
     * @return array
     */
    public function findAllIdsByShopId($idShop)
    {
        $sql = '
            SELECT `id_product`
            FROM `'.$this->getPrefix().'product_shop` ps
            WHERE `id_shop` = '.(int)$idShop.'
        ';

        $results = $this->db->select($sql);

        $productsIds = [];
        foreach ($results as $result) {
            $productsIds[] = (int) $result['id_product'];
        }

        return $productsIds;
    }

    /**
     * Find min product weight
     *
     * @param int $idShop
     *
     * @return float
     */
    public function findMinWeight($idShop)
    {
        $sql = '
            SELECT MIN(p.`weight`) AS `min_weight`
            FROM `'.$this->getPrefix().'product` p
            LEFT JOIN `'.$this->getPrefix().'product_shop` ps
                ON ps.`id_product` = p.`id_product`
            WHERE ps.`id_shop` = '.(int)$idShop.'
        ';

        $results = $this->db->select($sql);

        if (!is_array($results) || !$results) {
            return 0.0;
        }

        return $results[0]['min_weight'];
    }

    /**
     * Find max product weight
     *
     * @param int $idShop
     *
     * @return float
     */
    public function findMaxWeight($idShop)
    {
        $sql = '
            SELECT MAX(p.`weight`) AS `max_weight`
            FROM `'.$this->getPrefix().'product` p
            LEFT JOIN `'.$this->getPrefix().'product_shop` ps
                ON ps.`id_product` = p.`id_product`
            WHERE ps.`id_shop` = '.(int)$idShop.'
        ';

        $results = $this->db->select($sql);

        if (!is_array($results) || !$results) {
            return 0.0;
        }

        return $results[0]['max_weight'];
    }

    /**
     * Find all products weights
     *
     * @param int $idShop
     *
     * @return array|float[]
     */
    public function findAllWeights($idShop)
    {
        $sql = '
            SELECT DISTINCT p.`weight`
            FROM `'.$this->getPrefix().'product` p
            LEFT JOIN `'.$this->getPrefix().'product_shop` ps
                ON ps.`id_product` = p.`id_product`
            WHERE ps.`id_shop` = '.(int)$idShop.'
        ';

        $results = $this->db->select($sql);

        if (!is_array($results) || !$results) {
            return [];
        }

        $weights = [];

        foreach ($results as $result) {
            $weights[] = $result['weight'];
        }

        return $weights;
    }
}
