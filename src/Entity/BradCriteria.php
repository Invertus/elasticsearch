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
