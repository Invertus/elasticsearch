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

/**
 * Class BradFilter
 */
class BradFilter extends ObjectModel
{
    const FILTER_TYPE_PRICE = 1;
    const FILTER_TYPE_WEIGHT = 2;
    const FILTER_TYPE_FEATURE = 3;
    const FILTER_TYPE_ATTRIBUTE_GROUP = 4;
    const FILTER_TYPE_MANUFACTURER = 5;
    const FILTER_TYPE_QUANTITY = 6;
    const FILTER_TYPE_CATEGORY = 7;

    const FILTER_STYLE_CHECKBOX = 1;
    const FILTER_STYLE_LIST_OF_VALUES = 2;
    const FILTER_STYLE_INPUT = 3;
    const FILTER_STYLE_SLIDER = 4;

    const ORDER_BY_NONE = 1;
    const ORDER_BY_NUMBER_OF_PRODUCTS = 2;
    const ORDER_BY_NATURAL = 3;
    const ORDER_BY_ALPHA_NUM = 4;

    /**
     * @var int
     */
    public $filter_type;

    /**
     * id_feture or id_attribute
     *
     * @var int
     */
    public $id_key;

    /**
     * @var int
     */
    public $filter_style;

    /**
     * @var int
     */
    public $custom_height;

    /**
     * @var string
     */
    public $criteria_suffix;

    /**
     * @var int
     */
    public $criteria_order_by = 0;

    /**
     * @var int
     */
    public $criteria_order_way = 0;

    /**
     * @var string
     */
    public $name;

    /**
     * @var array Entity definition
     */
    public static $definition = [
        'table' => 'brad_filter',
        'primary' => 'id_brad_filter',
        'fields' => [
            'name' => ['type' => self::TYPE_STRING, 'required' => true, 'validate' => 'isString'],
            'filter_type' => ['type' => self::TYPE_INT, 'required' => true, 'validate' => 'isUnsignedInt'],
            'id_key' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'filter_style' => ['type' => self::TYPE_INT, 'required' => true, 'validate' => 'isUnsignedInt'],
            'custom_height' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'criteria_suffix' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'criteria_order_by' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'criteria_order_way' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
        ],
        'multishop' => true,
    ];

    /**
     * BradFilter constructor.
     *
     * @param int|null $id
     * @param int|null $idLang
     * @param int|null $idShop
     */
    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation(self::$definition['table'], ['type' => 'shop']);
    }

    /**
     * Custom add
     *
     * @param bool $autoData
     * @param bool $nullValues
     *
     * @return bool
     */
    public function add($autoData = true, $nullValues = false)
    {
        $parentReturn = parent::add($autoData, $nullValues);

        if (!$parentReturn) {
            return $parentReturn;
        }

        $this->updateCriteria();

        return $parentReturn;
    }

    /**
     * Custom update
     *
     * @param bool $nullValues
     *
     * @return bool
     */
    public function update($nullValues = false)
    {
        $parentReturn = parent::update($nullValues);

        if (!$parentReturn) {
            return $parentReturn;
        }

        $this->updateCriteria();

        return $parentReturn;
    }

    /**
     * Delete filter criteria & remove filter from templates
     *
     * @return bool
     */
    public function delete()
    {
        $idFilter = (int) $this->id;

        $parentReturn = parent::delete();

        if ($parentReturn) {
            BradCriteria::deleteFilterCriteria($idFilter);
            Db::getInstance()->delete('brad_filter_template_filter', 'id_brad_filter = '.(int)$idFilter);
        }

        return $parentReturn;
    }

    /**
     * Get tranlslated filter type name
     *
     * @param int|null $filterType
     *
     * @return array|string
     */
    public static function getFilterTypeTranslations($filterType = null)
    {
        $brad = Module::getInstanceByName('brad');

        $translatedFilterTypes = [
            self::FILTER_TYPE_PRICE => $brad->l('Price', __CLASS__),
            self::FILTER_TYPE_WEIGHT => $brad->l('Weight', __CLASS__),
            self::FILTER_TYPE_FEATURE => $brad->l('Feature', __CLASS__),
            self::FILTER_TYPE_ATTRIBUTE_GROUP => $brad->l('Attribute group', __CLASS__),
            self::FILTER_TYPE_MANUFACTURER => $brad->l('Manufacturer', __CLASS__),
            self::FILTER_TYPE_QUANTITY => $brad->l('Stock', __CLASS__),
            self::FILTER_TYPE_CATEGORY => $brad->l('Category', __CLASS__),
        ];

        if (null !== $filterType) {
            return $translatedFilterTypes[$filterType];
        }

        return $translatedFilterTypes;
    }

    /**
     * Get tranlslated filter styles name
     *
     * @param int|null $filterStyle
     *
     * @return array|string
     */
    public static function getFilterStyleTranslations($filterStyle = null)
    {
        $brad = Module::getInstanceByName('brad');

        $translatedFilterTypes = [
            self::FILTER_STYLE_LIST_OF_VALUES => $brad->l('List of values', __CLASS__),
            self::FILTER_STYLE_CHECKBOX => $brad->l('Checkbox', __CLASS__),
            self::FILTER_STYLE_INPUT => $brad->l('Input fields', __CLASS__),
            self::FILTER_STYLE_SLIDER => $brad->l('Slider', __CLASS__),
        ];

        if (null !== $filterStyle) {
            return $translatedFilterTypes[$filterStyle];
        }

        return $translatedFilterTypes;
    }

    /**
     * Get array for filter type selection input
     *
     * @return array
     */
    public static function getFilterTypesSelect()
    {
        $filterTypeSelect = [];

        foreach (self::getFilterTypeTranslations() as $key => $filterTypeTranslation) {
            $filterTypeSelect[] = [
                'id' => $key,
                'name' => $filterTypeTranslation,
            ];
        }

        return $filterTypeSelect;
    }

    /**
     * Get array for filter styles selection input
     *
     * @return array
     */
    public static function getFilterStylesSelect()
    {
        $filterStyleSelect = [];

        foreach (self::getFilterStyleTranslations() as $key => $filterStyleTranslation) {
            $filterStyleSelect[] = [
                'id' => $key,
                'name' => $filterStyleTranslation,
            ];
        }

        return $filterStyleSelect;
    }

    /**
     * Get available filter styles by filters
     *
     * @return array
     */
    public static function getFilterStylesByFilterType()
    {
        return [
            self::FILTER_TYPE_PRICE => [
                self::FILTER_STYLE_SLIDER,
                self::FILTER_STYLE_INPUT,
                self::FILTER_STYLE_LIST_OF_VALUES,
            ],
            self::FILTER_TYPE_WEIGHT => [
                self::FILTER_STYLE_SLIDER,
                self::FILTER_STYLE_INPUT,
                self::FILTER_STYLE_LIST_OF_VALUES,
            ],
            self::FILTER_TYPE_FEATURE => [
                self::FILTER_STYLE_SLIDER,
                self::FILTER_STYLE_INPUT,
                self::FILTER_STYLE_LIST_OF_VALUES,
                self::FILTER_STYLE_CHECKBOX,
            ],
            self::FILTER_TYPE_ATTRIBUTE_GROUP => [
                self::FILTER_STYLE_SLIDER,
                self::FILTER_STYLE_INPUT,
                self::FILTER_STYLE_LIST_OF_VALUES,
                self::FILTER_STYLE_CHECKBOX,
            ],
            self::FILTER_TYPE_MANUFACTURER => [
                self::FILTER_STYLE_CHECKBOX,
            ],
            self::FILTER_TYPE_QUANTITY => [
                self::FILTER_STYLE_CHECKBOX,
            ],
            self::FILTER_TYPE_CATEGORY => [
                self::FILTER_STYLE_CHECKBOX,
            ],
        ];
    }

    /**
     * Get repository class name
     *
     * @return string
     */
    public static function getRepositoryClassName()
    {
        return 'Invertus\Brad\Repository\FilterRepository';
    }

    /**
     * Update filter criteria (custom ranges)
     *
     * @return bool
     */
    protected function updateCriteria()
    {
        BradCriteria::deleteFilterCriteria($this->id);

        $criterias = [];
        $position = 1;

        foreach ($_POST as $key => $value) {
            if (0 === strpos($key, 'brad_min_range_')) {
                $criteriaNumber = substr($key, -1);
                $criterias[$criteriaNumber]['min'] = (float) $value;

                if (!isset($criterias[$criteriaNumber]['position'])) {
                    $criterias[$criteriaNumber]['position'] = $position;
                    $position += 1;
                }
            }

            if (0 === strpos($key, 'brad_max_range_')) {
                $criteriaNumber = substr($key, -1);
                $criterias[$criteriaNumber]['max'] = (float) $value;

                if (!isset($criterias[$criteriaNumber]['position'])) {
                    $criterias[$criteriaNumber]['position'] = $position;
                    $position += 1;
                }
            }
        }

        if (empty($criterias)) {
            return true;
        }

        foreach ($criterias as $criteria) {
            $filterCriteria = new BradCriteria();
            $filterCriteria->position = $criteria['position'];
            $filterCriteria->min_value = $criteria['min'];
            $filterCriteria->max_value = $criteria['max'];
            $filterCriteria->id_brad_filter = $this->id;

            if (!$filterCriteria->save()) {
                return false;
            }
        }

        return true;
    }
}
