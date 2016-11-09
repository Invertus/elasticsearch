<?php

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
    public $criteria_order_by;

    /**
     * @var int
     */
    public $criteria_order_way;

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

    public function add($autoData = true, $nullValues = false)
    {
        parent::add($autoData, $nullValues);
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
}
