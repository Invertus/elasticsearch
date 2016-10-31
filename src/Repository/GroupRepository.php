<?php

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
