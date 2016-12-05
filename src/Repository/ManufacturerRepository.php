<?php

namespace Invertus\Brad\Repository;

/**
 * Class ManufacturerRepository
 *
 * @package Invertus\Brad\Repository
 */
class ManufacturerRepository extends \Core_Foundation_Database_EntityRepository
{
    /**
     * Find all manufacturers
     *
     * @param int $idShop
     *
     * @return array
     */
    public function findAllByShopId($idShop)
    {
        $sql = '
            SELECT m.`id_manufacturer`, m.`name`
            FROM `'.$this->getPrefix().'manufacturer` m
            LEFT JOIN `'.$this->getPrefix().'manufacturer_shop` ms
                ON ms.`id_manufacturer` = m.`id_manufacturer`
            WHERE ms.`id_shop` = '.(int)$idShop.'
        ';

        $results = $this->db->select($sql);

        if (!is_array($results) || !$results) {
            return [];
        }

        return $results;
    }
}
