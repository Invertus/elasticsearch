<?php

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
     * @param int $idLang
     * @param int $idShop
     *
     * @return array
     */
    public function findAttributesGroupsValues($idLang, $idShop)
    {
        static $attributeGroupsValues;

        if ($attributeGroupsValues) {
            return $attributeGroupsValues;
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
        ';

        $results = $this->db->select($sql);

        if (!is_array($results) || !$results) {
            return [];
        }

        foreach ($results as $result) {
            $attributeGroupsValues[$result['id_attribute_group']][] = [
                'id_attribute' => $result['id_attribute'],
                'name' => $result['name'],
                'color' => $result['color'],
            ];
        }

        return $attributeGroupsValues;
    }

    /**
     * Get min attribute group value
     *
     * @param int $idAttributeGroup
     * @param int $idLang
     * @param int $idShop
     *
     * @return float
     */
    public function findMinAttributeGroupValue($idAttributeGroup, $idLang, $idShop)
    {
        $sql = '
            SELECT MIN(al.`name`) AS `min_value`
            FROM `'.$this->getPrefix().'attribute` a
            LEFT JOIN `'.$this->getPrefix().'attribute_group_shop` ags
                ON ags.`id_attribute_group` = a.`id_attribute_group`
            LEFT JOIN `'.$this->getPrefix().'attribute_lang` al
                ON al.`id_attribute` = a.`id_attribute`
            WHERE a.`id_attribute_group` = '.(int)$idAttributeGroup.'
                AND al.`id_lang` = '.(int)$idLang.'
                AND ags.`id_shop` = '.(int)$idShop.'
        ';

        $results = $this->db->select($sql);

        if (!is_array($results) || !$results) {
            return 0.0;
        }

        return $results[0]['min_value'];
    }

    /**
     * Get max attribute group value
     *
     * @param int $idAttributeGroup
     * @param int $idLang
     * @param int $idShop
     *
     * @return float
     */
    public function findMaxAttributeGroupValue($idAttributeGroup, $idLang, $idShop)
    {
        $sql = '
            SELECT MAX(al.`name`) AS `max_value`
            FROM `'.$this->getPrefix().'attribute` a
            LEFT JOIN `'.$this->getPrefix().'attribute_group_shop` ags
                ON ags.`id_attribute_group` = a.`id_attribute_group`
            LEFT JOIN `'.$this->getPrefix().'attribute_lang` al
                ON al.`id_attribute` = a.`id_attribute`
            WHERE a.`id_attribute_group` = '.(int)$idAttributeGroup.'
                AND al.`id_lang` = '.(int)$idLang.'
                AND ags.`id_shop` = '.(int)$idShop.'
        ';

        $results = $this->db->select($sql);

        if (!is_array($results) || !$results) {
            return 0.0;
        }

        return $results[0]['max_value'];
    }
}
