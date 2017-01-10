<?php

namespace Invertus\Brad\DataType;

/**
 * Class FilterStruct
 *
 * @package Invertus\Brad\DataType
 */
class FilterStruct
{
    /**
     * @var int BradFilter id
     */
    public $idFilter;

    /**
     * @var string Translated filter name (Manufcaturer, Price, Color, Size & etc)
     */
    public $name;

    /**
     * @var string Filter input name (price, category, manufacturer, feature_5, attribute_group_8 & etc)
     */
    public $inputName;

    /**
     * @var int List of values, checkboxes, slider, input fields
     */
    public $filterStyle;

    /**
     * @var int Filter type (price, feature, attribute group, manufacturer & etc)
     */
    public $filterType;

    /**
     * @var int Feature or attribute group id depends on filter type
     */
    public $idKey;

    /**
     * @var array Filter criterias
     */
    public $criterias = [];

    /**
     * @var string
     */
    public $criteriaNameKey;

    /**
     * @var string
     */
    public $criteriaValueKey;

    /**
     * @var string
     */
    public $criteriaSuffix = '';

    /**
     * @var int
     */
    public $customHeight;

    /**
     * @return array
     */
    public function getCriterias()
    {
        return $this->criterias;
    }

    /**
     * @param array $criterias
     */
    public function setCriterias($criterias)
    {
        $this->criterias = $criterias;
    }

    /**
     * @return string
     */
    public function getCriteriaNameKey()
    {
        return $this->criteriaNameKey;
    }

    /**
     * @param string $criteriaNameKey
     */
    public function setCriteriaNameKey($criteriaNameKey)
    {
        $this->criteriaNameKey = $criteriaNameKey;
    }

    /**
     * @return string
     */
    public function getCriteriaValueKey()
    {
        return $this->criteriaValueKey;
    }

    /**
     * @param string $criteriaValueKey
     */
    public function setCriteriaValueKey($criteriaValueKey)
    {
        $this->criteriaValueKey = $criteriaValueKey;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getInputName()
    {
        return $this->inputName;
    }

    /**
     * @param string $inputName
     */
    public function setInputName($inputName)
    {
        $this->inputName = $inputName;
    }

    /**
     * @return int
     */
    public function getFilterStyle()
    {
        return $this->filterStyle;
    }

    /**
     * @param int $filterStyle
     */
    public function setFilterStyle($filterStyle)
    {
        $this->filterStyle = $filterStyle;
    }

    /**
     * @return int
     */
    public function getFilterType()
    {
        return $this->filterType;
    }

    /**
     * @param int $filterType
     */
    public function setFilterType($filterType)
    {
        $this->filterType = $filterType;
    }

    /**
     * @return int
     */
    public function getIdKey()
    {
        return $this->idKey;
    }

    /**
     * @param int $idKey
     */
    public function setIdKey($idKey)
    {
        $this->idKey = $idKey;
    }

    /**
     * @return int
     */
    public function getIdFilter()
    {
        return $this->idFilter;
    }

    /**
     * @param int $idFilter
     */
    public function setIdFilter($idFilter)
    {
        $this->idFilter = $idFilter;
    }

    /**
     * @param string $criteriaSuffix
     */
    public function setCriteriaSuffix($criteriaSuffix)
    {
        $this->criteriaSuffix = $criteriaSuffix;
    }

    /**
     * @param int $customHeight
     */
    public function setCustomHeight($customHeight)
    {
        $this->customHeight = $customHeight;
    }

    /**
     * @return string
     */
    public function getCriteriaSuffix()
    {
        return $this->criteriaSuffix;
    }
}
