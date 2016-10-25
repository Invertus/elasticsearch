<?php

namespace Invertus\Brad\Repository;

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
                AND cs.`id_category` != 1
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
}
