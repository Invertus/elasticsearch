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
 * Class FilterRepository
 *
 * @package Invertus\Brad\Repository
 */
class FilterRepository extends \Core_Foundation_Database_EntityRepository
{
    /**
     * Find all filters by shop id
     *
     * @param $idShop
     *
     * @return array
     */
    public function findAllFilters($idShop)
    {
        static $filters;

        if ($filters) {
            return $filters;
        }

        $sql = '
            SELECT bf.`id_brad_filter`, bf.`name`, bf.`filter_type`, bf.`filter_style`, bf.`id_key`
            FROM `'.$this->getPrefix().'brad_filter` bf
            LEFT JOIN `'.$this->getPrefix().'brad_filter_shop` bfs
                ON bfs.`id_brad_filter` = bf.`id_brad_filter`
            WHERE bfs.`id_shop` = '.(int)$idShop.'    
        ';

        $filters = $this->db->select($sql);

        if (!is_array($filters) || empty($filters)) {
            return [];
        }

        return $filters;
    }

    /**
     *
     *
     * @return array
     */
    public function findAllCriterias()
    {
        $sql = '
            SELECT c.`id_brad_filter`, c.`min_value`, c.`max_value`, c.`position`
            FROM `'.$this->getPrefix().'brad_criteria` c
        ';

        $results = $this->db->select($sql);

        if (!is_array($results) || !$results) {
            return [];
        }

        $criterias = [];

        foreach ($results as $result) {
            $criterias[$result['id_brad_filter']][] = [
                'min_value' => $result['min_value'],
                'max_value' => $result['max_value'],
                'position' => $result['position'],
            ];
        }

        return $criterias;
    }
}
