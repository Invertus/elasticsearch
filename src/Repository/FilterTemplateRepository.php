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
     * Find all filter tempalte categories ids
     *
     * @param int $idFilterTemplate
     *
     * @return array|int[]
     */
    public function findAllCategories($idFilterTemplate)
    {
        $sql = '
            SELECT bftc.`id_category`
            FROM `'.$this->getPrefix().'brad_filter_template_category` bftc
            WHERE bftc.`id_brad_filter_template` = '.(int)$idFilterTemplate.'
        ';

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
     * @return array|int[]
     */
    public function findAllFilters($idFilterTemplate)
    {
        $sql = '
            SELECT bftf.`id_brad_filter`
            FROM `'.$this->getPrefix().'brad_filter_template_filter` bftf
            WHERE bftf.`id_brad_filter_template` = '.(int)$idFilterTemplate.'
        ';

        $results = $this->db->select($sql);
        $filtersIds = [];

        if (!is_array($results)) {
            return $filtersIds;
        }

        foreach ($results as $result) {
            $filtersIds[] = (int) $result['id_brad_filter'];
        }

        return $filtersIds;
    }
}
