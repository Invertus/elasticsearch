<?php

namespace Invertus\Brad\Repository;

/**
 * Class FilterTemplateRepository
 *
 * @package Invertus\Brad\Repository
 */
class FilterTemplateRepository extends \Core_Foundation_Database_EntityRepository
{
    /**
     * Find all filter template categories ids
     *
     * @param int|null $idFilterTemplate
     * @param array $excludeTemplatesIds
     *
     * @return array|int[]
     */
    public function findAllCategories($idFilterTemplate = null, array $excludeTemplatesIds = [])
    {
        $sql = '
            SELECT bftc.`id_category`
            FROM `'.$this->getPrefix().'brad_filter_template_category` bftc
            WHERE 1
        ';

        if (null !== $idFilterTemplate) {
            $sql .= ' AND bftc.`id_brad_filter_template` = '.(int)$idFilterTemplate;
        }

        if (!empty($excludeTemplatesIds)) {
            $excludeTemplatesIds = array_map('intval', $excludeTemplatesIds);
            $sql .= ' AND bftc.`id_brad_filter_template` NOT IN ('.implode(',', $excludeTemplatesIds).')';
        }

        $results = $this->db->select($sql);
        $categoriesIds = [];

        if (!is_array($results)) {
            return $categoriesIds;
        }

        foreach ($results as $result) {
            $categoriesIds[] = (int) $result['id_category'];
        }

        return $categoriesIds;
    }

    /**
     * Find all filters ids
     *
     * @param int $idFilterTemplate
     *
     * @return array
     */
    public function findAllFilters($idFilterTemplate)
    {
        $sql = '
            SELECT bftf.`id_brad_filter`, bftf.`position`
            FROM `'.$this->getPrefix().'brad_filter_template_filter` bftf
            WHERE bftf.`id_brad_filter_template` = '.(int)$idFilterTemplate.'
        ';

        $results = $this->db->select($sql);

        if (!is_array($results)) {
            return [];
        }

        return $results;
    }

    /**
     * Find all template filters by category
     *
     * @param int $idCategory
     * @param int $idShop
     *
     * @return array
     */
    public function findTemplateFilters($idCategory, $idShop)
    {
        //@todo: finish implementing
        $sql = '
            SELET
            FROM `'.$this->getPrefix().'brad_filter_template` ft
            LEFT JOIN `'.$this->getPrefix().'brad_filter_template_shop` fts
                ON fts.`id_brad_filter_template` = ft.`id_brad_filter_template`
                    AND fts.`id_shop` = '.(int)$idShop.'
            LEFT JOIN `'.$this->getPrefix().'brad_filter_template_category` ftc
                ON ft.`id_brad_filter_template` = ftc.`id_brad_filter_template`
                    AND ftc.`id_category` = '.(int)$idCategory.'
        ';
    }
}
