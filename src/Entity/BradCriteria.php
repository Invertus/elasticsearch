<?php

/**
 * Class BradCriteria
 */
class BradCriteria extends ObjectModel
{
    /**
     * @var int Filter id that criteria belongs to
     */
    public $id_brad_filter;

    /**
     * @var float
     */
    public $min_value;

    /**
     * @var float
     */
    public $max_value;

    /**
     * @var int
     */
    public $position;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'brad_criteria',
        'primary' => 'id_brad_criteria',
        'fields' => [
            'id_brad_filter' => ['type' => self::TYPE_INT, 'required' => true, 'validate' => 'isUnsignedInt'],
            'min_value' => ['type' => self::TYPE_FLOAT, 'required' => true, 'validate' => 'isUnsignedFloat'],
            'max_value' => ['type' => self::TYPE_FLOAT, 'required' => true, 'validate' => 'isUnsignedFloat'],
            'position' => ['type' => self::TYPE_INT, 'required' => true, 'validate' => 'isUnsignedInt'],
        ],
    ];

    /**
     * Delete all criterias by filter
     *
     * @param int $idFilter
     *
     * @return bool
     */
    public static function deleteFilterCriteria($idFilter)
    {
        $db = Db::getInstance();

        $success = $db->execute(
            'DELETE FROM `'._DB_PREFIX_.'brad_criteria` WHERE `id_brad_filter` = '.(int)$idFilter
        );

        return $success;
    }
}
