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
 * Class BradFilterTemplate
 */
class BradFilterTemplate extends ObjectModel
{
    /**
     * @var string
     */
    public $name;

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

    /**
     * Get repository class name
     *
     * @return string
     */
    public static function getRepositoryClassName()
    {
        return 'Invertus\Brad\Repository\FilterTemplateRepository';
    }

    /**
     * @param bool $autoDate
     * @param bool $nullValues
     *
     * @return bool
     */
    public function add($autoDate = true, $nullValues = false)
    {
        $parentReturn = parent::add($autoDate, $nullValues);

        if ($parentReturn) {
            $this->updateFilters();
            $this->updateCategories();
        }

        return $parentReturn;
    }

    /**
     * @param bool $nullValues
     *
     * @return bool
     */
    public function update($nullValues = false)
    {
        $parentReturn = parent::update($nullValues);

        if ($parentReturn) {
            $this->updateFilters();
            $this->updateCategories();
        }

        return $parentReturn;
    }

    /**
     * Remove template categories & filters when deleting template
     *
     * @return bool
     */
    public function delete()
    {
        $parentReturn = parent::delete();

        if ($parentReturn) {
            $this->deleteCategories();
            $this->deleteFilters();
        }

        return $parentReturn;
    }

    /**
     * Update template filters
     *
     * @return bool
     */
    protected function updateFilters()
    {
        $this->deleteFilters();

        $templateFilters = [];
        $position = 1;

        foreach (array_keys($_POST) as $key) {
            if (0 !== strpos($key, 'template_filter')) {
                continue;
            }

            $filterData = explode(':', $key)[1];
            $idFilter = (int) explode('-', $filterData)[0];

            $templateFilters[] = [
                'id_brad_filter' => (int) $idFilter,
                'id_brad_filter_template' => (int) $this->id,
                'position' => $position,
            ];

            $position += 1;
        }

        if (empty($templateFilters)) {
            return true;
        }

        $result = Db::getInstance()->insert('brad_filter_template_filter', $templateFilters);

        return $result;
    }

    /**
     * Update filter template categories
     *
     * @return bool
     */
    protected function updateCategories()
    {
        $this->deleteCategories();

        $categoriesIds = Tools::getValue('filter_template_categories');

        if (!is_array($categoriesIds)) {
            return true;
        }

        $templateCategories = [];

        foreach ($categoriesIds as $idCategory) {
            $templateCategories[] = [
                'id_brad_filter_template' => (int) $this->id,
                'id_category' => (int) $idCategory,
            ];
        }

        $result = Db::getInstance()->insert('brad_filter_template_category', $templateCategories);

        return $result;
    }

    /**
     * Delete all template filters
     *
     * @return bool
     */
    private function deleteFilters()
    {
        $result = Db::getInstance()->delete('brad_filter_template_filter', 'id_brad_filter_template = '.(int)$this->id);

        return $result;
    }

    /**
     * Delete all template categories
     *
     * @return bool
     */
    private function deleteCategories()
    {
        $db = Db::getInstance();

        $result = $db->delete('brad_filter_template_category', 'id_brad_filter_template = '.(int)$this->id);

        return $result;
    }
}
