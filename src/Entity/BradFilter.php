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

    /**
     * @var string|array
     */
    public $name;

    /**
     * @var string|array
     */
    public $custom_name;

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
    public $criteria_sign;

    /**
     * @var int
     */
    public $criteria_order_by;

    /**
     * @var int
     */
    public $criteria_order_way;

    /**
     * @var array Entity definition
     */
    public static $definition = [
        'table' => 'brad_filter',
        'primary' => 'id_brad_filter',
        'fields' => [
            'name' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'],
            'custom_name' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName'],
            'filter_type' => ['type' => self::TYPE_INT, 'required' => true, 'validate' => 'isUnsignedInt'],
            'id_key' => ['type' => self::TYPE_INT, 'required' => true, 'validate' => 'isUnsignedInt'],
            'filter_style' => ['type' => self::TYPE_INT, 'required' => true, 'validate' => 'isUnsignedInt'],
            'custom_height' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'criteria_sign' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'criteria_order_by' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'criteria_order_way' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
        ],
        'multishop' => true,
        'multilang' => true,
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
     * Get tranlslated filter type name
     *
     * @param int|null $filterType
     *
     * @return array|string
     */
    public static function getFilterTypeNames($filterType = null)
    {
        $brad = Module::getInstanceByName('brad');

        $translatedFilterTypes = [
            self::FILTER_TYPE_PRICE => $brad->l('Price', __CLASS__),
            self::FILTER_TYPE_WEIGHT => $brad->l('Weight', __CLASS__),
            self::FILTER_TYPE_FEATURE => $brad->l('Feature', __CLASS__),
            self::FILTER_TYPE_ATTRIBUTE_GROUP => $brad->l('Attribute group', __CLASS__),
            self::FILTER_TYPE_MANUFACTURER => $brad->l('Manufacturer', __CLASS__),
            self::FILTER_TYPE_QUANTITY => $brad->l('Quantity', __CLASS__),
            self::FILTER_TYPE_CATEGORY => $brad->l('Category', __CLASS__),
        ];

        if (null !== $filterType) {
            return $translatedFilterTypes[$filterType];
        }

        return $translatedFilterTypes;
    }
}
