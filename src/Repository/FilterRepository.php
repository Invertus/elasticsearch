<?php

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
