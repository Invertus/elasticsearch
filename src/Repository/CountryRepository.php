<?php

namespace Invertus\Brad\Repository;

/**
 * Class CountryRepository
 *
 * @package Invertus\Brad\Repository
 */
class CountryRepository extends \Core_Foundation_Database_EntityRepository
{
    /**
     * Find all countries ids by shop id
     *
     * @param int $idShop
     *
     * @return array
     */
    public function findAllIdsByShopId($idShop)
    {
        $ids = [];

        $sql = '
            SELECT c.`id_country`
            FROM `'.$this->getPrefix().'country` c
            LEFT JOIN `'.$this->getPrefix().'country_shop` cs
                ON cs.`id_country` = c.`id_country`
            WHERE cs.`id_shop` = '.(int)$idShop.'
                AND c.`active` = 1
        ';

        $results = $this->db->select($sql);

        if (!$results) {
            return $ids;
        }

        foreach ($results as $result) {
            $ids[] = (int) $result['id_country'];
        }

        return $ids;
    }
}
