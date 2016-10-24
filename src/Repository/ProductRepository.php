<?php

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
}
