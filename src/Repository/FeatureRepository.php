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
     * @todo select only specific features
     *
     * @param int $idLang
     * @param int $idShop
     *
     * @return string
     */
    public function findNames($idLang, $idShop)
    {
        static $features;

        if ($features) {
            return $features;
        }

        $sql = '
            SELECT fl.`id_feature`, fl.`name`
            FROM `'.$this->getPrefix().'feature_lang` fl
            LEFT JOIN `'.$this->getPrefix().'feature_shop` fs
                ON fs.`id_feature` = fl.`id_feature`
            WHERE fl.`id_lang` = '.(int)$idLang.'
                AND fs.`id_shop` = '.(int)$idShop.'
        ';

        $results = $this->db->select($sql);

        if (!is_array($results) || !$results) {
            return [];
        }

        foreach ($results as $result) {
            $features[$result['id_feature']] = $result['name'];
        }

        return $features;
    }

    /**
     * Find all feature values
     *
     * @param int $idFeature
     * @param int $idLang
     *
     * @return array
     */
    public function findFeaturesValues($idFeature, $idLang)
    {
        static $featureValues;

        if (isset($featureValues['features_'.(int)$idFeature])) {
            return $featureValues['features_'.(int)$idFeature];
        }

        $sql = '
            SELECT fv.`id_feature`, fvl.`id_feature_value`, fvl.`value`
            FROM `'.$this->getPrefix().'feature_value_lang` fvl
            LEFT JOIN `'.$this->getPrefix().'feature_value` fv
                ON fv.`id_feature_value` = fvl.`id_feature_value`
            WHERE fvl.`id_lang` = '.(int)$idLang.' AND fv.`id_feature` = '.(int)$idFeature.'
        ';

        $results = $this->db->select($sql);

        if (!is_array($results) || !$results) {
            return [];
        }

        $values = [];
        foreach ($results as $result) {
            $values[$result['id_feature']][$result['id_feature_value']] = [
                'name' => $result['value'],
                'id_feature_value' => $result['id_feature_value'],
            ];
        }

        $featureValues['features_'.(int)$idFeature] = $values;

        return $values;
    }
}
