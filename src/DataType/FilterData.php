<?php

namespace Invertus\Brad\DataType;

/**
 * Class FilterData
 *
 * @package Invertus\Brad\DataType
 */
class FilterData extends SearchData
{
    /**
     * @var array
     */
    private $selectedFilters;

    /**
     * @var int
     */
    private $idCategory;

    /**
     * @return array
     */
    public function getSelectedFilters()
    {
        return $this->selectedFilters;
    }

    /**
     * @param array $selectedFilters
     */
    public function setSelectedFilters($selectedFilters)
    {
        $this->selectedFilters = $selectedFilters;
    }

    /**
     * @return int
     */
    public function getIdCategory()
    {
        return $this->idCategory;
    }

    /**
     * @param int $idCategory
     */
    public function setIdCategory($idCategory)
    {
        $this->idCategory = $idCategory;
    }
}
