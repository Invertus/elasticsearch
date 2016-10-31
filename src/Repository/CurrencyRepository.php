<?php

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
