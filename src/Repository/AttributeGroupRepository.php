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
}
