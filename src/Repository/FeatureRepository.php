<?php

namespace Invertus\Brad\Repository;

/**
 * Class FeatureRepository
 *
 * @package Invertus\Brad\Repository
 */
class FeatureRepository extends \Core_Foundation_Database_EntityRepository
{
    /**
     * Find all feature names and ids by given query
     *
     * @param $query
     * @param $limit
     * @param $idLang
     * @param $idShop
     *
     * @return array
     */
    public function findAllFeatureNamesAndIdsByQuery($query, $limit, $idLang, $idShop)
    {
        $sql = '
            SELECT fl.`id_feature`, fl.`name`
            FROM `'.$this->getPrefix().'feature_lang` fl
            LEFT JOIN `'.$this->getPrefix().'feature_shop` fs
                ON fs.`id_feature` = fl.`id_feature`
            WHERE fl.`id_lang` = '.(int)$idLang.'
                AND fs.`id_shop` = '.(int)$idShop.'
                AND fl.`name` LIKE "%'.$this->db->escape($query).'%"
            LIMIT '.(int)$limit.'
        ';

        $results = $this->db->select($sql);

        if (!$results || !is_array($results)) {
            return [];
        }

        $features = [];
        foreach ($results as $result) {
            $features[] = [
                'id' => $result['id_feature'],
                'name' => $result['name'],
            ];
        }

        return $features;
    }

    /**
     * Find feature name
     *
     * @param int $idLang
     * @param int $idShop
     *
     * @return string
     */
    public function findNames($idLang, $idShop)
    {
        $sql = '
            SELECT fl.`id_feature`, fl.`name`
            FROM `'.$this->getPrefix().'feature_lang` fl
            LEFT JOIN `'.$this->getPrefix().'feature_shop` fs
            WHERE fl.`id_lang` = '.(int)$idLang.'
                AND fs.`id_shop` = '.(int)$idShop.'
        ';

        $results = $this->db->select($sql);

        if (!is_array($results) || !$results) {
            return [];
        }

        $features = [];

        foreach ($results as $result) {
            $features[$result['id_feature']] = $result['name'];
        }

        return $features;
    }
}
