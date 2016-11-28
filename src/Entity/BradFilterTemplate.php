<?php

/**
 * Class BradFilterTemplate
 */
class BradFilterTemplate extends ObjectModel
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $categories;

    /**
     * @var string
     */
    public $date_add;

    /**
     * @var string
     */
    public $date_upd;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'brad_filter_template',
        'primary' => 'id_brad_filter_template',
        'fields' => [
            'name' => ['type' => self::TYPE_STRING, 'required' => true, 'validate' => 'isGenericName'],
            'categories' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'date_add' => ['type' => self::TYPE_DATE],
            'date_upd' => ['type' => self::TYPE_DATE],
        ],
        'multishop' => true,
    ];

    /**
     * BradFilterTemplate constructor.
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
}
