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
 * Class AttributeGroupRepository
 *
 * @package Invertus\Brad\Repository
 */
class AttributeGroupRepository extends \Core_Foundation_Database_EntityRepository
{
    /**
     * Find all attribute group names by query
     *
     * @param string $query
     * @param int $limit
     * @param int $idLang
     * @param int $idShop
     *
     * @return array
     */
    public function findAllFeatureNamesAndIdsByQuery($query, $limit, $idLang, $idShop)
    {
        $sql = '
            SELECT agl.`id_attribute_group`, agl.`name`
            FROM `'.$this->getPrefix().'attribute_group_lang` agl
            LEFT JOIN `'.$this->getPrefix().'attribute_group_shop` ags
                ON ags.`id_attribute_group` = agl.`id_attribute_group`
            WHERE agl.`id_lang` = '.(int)$idLang.'
                AND ags.`id_shop` = '.(int)$idShop.'
                AND agl.`name` LIKE "%'.$this->db->escape($query).'%"
            LIMIT '.(int)$limit.'
        ';

        $results = $this->db->select($sql);

        if (!$results || !is_array($results)) {
            return [];
        }

        $attributeGroups = [];
        foreach ($results as $result) {
            $attributeGroups[] = [
                'id' => $result['id_attribute_group'],
                'name' => $result['name'],
            ];
        }

        return $attributeGroups;
    }

    /**
     * Find all attribute group names
     *
     * @param int $idLang
     * @param int $idShop
     *
     * @return array
     */
    public function findNames($idLang, $idShop)
    {
        static $attributeGroups;

        if ($attributeGroups) {
            return $attributeGroups;
        }

        $sql = '
            SELECT agl.`id_attribute_group`, agl.`public_name` as `name`
            FROM `'.$this->getPrefix().'attribute_group_lang` agl
            LEFT JOIN `'.$this->getPrefix().'attribute_group_shop` ags
                ON ags.`id_attribute_group` = agl.`id_attribute_group`
            WHERE ags.`id_shop` = '.(int)$idShop.'
                AND agl.`id_lang` = '.(int)$idLang.'
        ';

        $results = $this->db->select($sql);

        if (!is_array($results) || !$results) {
            return [];
        }

        foreach ($results as $result) {
            $attributeGroups[$result['id_attribute_group']] = $result['name'];
        }

        return $attributeGroups;
    }

    /**
     * Find all attributes groups values
     *
     * @param int $idAttributeGroup
     * @param int $idLang
     * @param int $idShop
     *
     * @return array
     */
    public function findAttributesGroupsValues($idAttributeGroup, $idLang, $idShop)
    {
        static $attributeGroupsValues;

        $cachekey = 'ag_values_'.(int)$idAttributeGroup;
        if ($attributeGroupsValues[$cachekey]) {
            return $attributeGroupsValues[$cachekey];
        }

        $sql = '
            SELECT a.`id_attribute_group`, a.`id_attribute`, a.`color`, al.`name`
            FROM `'.$this->getPrefix().'attribute` a
            LEFT JOIN `'.$this->getPrefix().'attribute_lang` al
                ON al.`id_attribute` = a.`id_attribute`
            LEFT JOIN `'.$this->getPrefix().'attribute_shop` ashop
                ON ashop.`id_attribute` = a.`id_attribute`
            WHERE al.`id_lang` = '.(int)$idLang.'
                AND ashop.`id_shop` = '.(int)$idShop.'
                AND a.`id_attribute_group` = '.(int)$idAttributeGroup.'
        ';

        $results = $this->db->select($sql);

        if (!is_array($results) || !$results) {
            return [];
        }

        $values = [];
        foreach ($results as $result) {
            $values[$result['id_attribute_group']][$result['id_attribute']] = [
                'id_attribute' => $result['id_attribute'],
                'name' => $result['name'],
                'color' => $result['color'],
            ];
        }

        $attributeGroupsValues[$cachekey] = $values;

        return $values;
    }
}
