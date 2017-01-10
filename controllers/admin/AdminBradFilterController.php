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
 * Class AdminBradFilterController
 */
class AdminBradFilterController extends AbstractAdminBradModuleController
{
    /**
     * @var BradFilter
     */
    protected $object;

    /**
     * AdminBradFilterController constructor.
     */
    public function __construct()
    {
        $this->className = 'BradFilter';
        $this->table = BradFilter::$definition['table'];
        $this->identifier = BradFilter::$definition['primary'];

        parent::__construct();
    }

    /**
     * Handle ajax & processing
     *
     * @return bool
     */
    public function postProcess()
    {
        if ($this->isXmlHttpRequest()) {

            $query = Tools::getValue('q');
            $limit = (int) Tools::getValue('limit');
            $filterType = (int) Tools::getValue('filter_type');

            $idLang = (int) $this->context->language->id;
            $idShop = (int) $this->context->shop->id;

            $response = [];
            switch ($filterType) {
                case BradFilter::FILTER_TYPE_FEATURE:
                    /** @var \Invertus\Brad\Repository\FeatureRepository $featureRepository */
                    $featureRepository = $this->getRepository('BradFeature');
                    $response = $featureRepository->findAllFeatureNamesAndIdsByQuery($query, $limit, $idLang, $idShop);
                    break;
                case BradFilter::FILTER_TYPE_ATTRIBUTE_GROUP:
                    /** @var \Invertus\Brad\Repository\AttributeGroupRepository $attributeGroupRepository */
                    $attributeGroupRepository = $this->getRepository('BradAttributeGroup');
                    $response = $attributeGroupRepository->findAllFeatureNamesAndIdsByQuery($query, $limit, $idLang, $idShop);
                    break;
            }

            die(json_encode(['response' => $response]));
        }

        return parent::postProcess();
    }

    /**
     * Add custom JS & CSS
     */
    public function setMedia()
    {
        parent::setMedia();

        $this->addJS($this->module->getPathUri().'views/js/admin/filter.js');
        $this->addJS($this->module->getPathUri().'views/js/admin/custom-ranges.js');
        $this->addJqueryPlugin('sortable');
        $this->addJqueryPlugin('autocomplete');

        Media::addJsDef([
            '$globalBradFilterControllerUrl' =>
                $this->context->link->getAdminLink(Brad::ADMIN_BRAD_FILTER_CONTROLLER),
        ]);
    }

    /**
     * Customize list fields
     *
     * @param int $idLang
     * @param null $orderBy
     * @param null $orderWay
     * @param int $start
     * @param null $limit
     * @param bool $idLangShop
     */
    public function getList($idLang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $idLangShop = false)
    {
        parent::getList($idLang, $orderBy, $orderWay, $start, $limit, $idLangShop);

        if (empty($this->_list)) {
            return;
        }

        $translatedFilterTypeTranslations = BradFilter::getFilterTypeTranslations();
        $translatedFilterStyleTranslations = BradFilter::getFilterStyleTranslations();

        foreach ($this->_list as &$listItem) {
            $listItem['filter_type'] = $translatedFilterTypeTranslations[$listItem['filter_type']];
            $listItem['filter_style'] = $translatedFilterStyleTranslations[$listItem['filter_style']];
        }
    }

    /**
     * Initialize filters list
     */
    protected function initList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->fields_list = [
            'id_brad_filter' => [
                'title' => $this->l('ID'),
                'type' => 'text',
                'width' => 20,
            ],
            'name' => [
                'title' => $this->l('Name'),
                'type' => 'text',
            ],
            'filter_type' => [
                'title' => $this->l('Filter type'),
                'type' => 'select',
                'filter_key' => 'filter_type',
                'list' => [
                    BradFilter::FILTER_TYPE_PRICE => $this->l('Price'),
                    BradFilter::FILTER_TYPE_WEIGHT => $this->l('Weight'),
                    BradFilter::FILTER_TYPE_CATEGORY => $this->l('Category'),
                    BradFilter::FILTER_TYPE_QUANTITY => $this->l('Quantity'),
                    BradFilter::FILTER_TYPE_MANUFACTURER => $this->l('Manufacturer'),
                    BradFilter::FILTER_TYPE_FEATURE => $this->l('Feature'),
                    BradFilter::FILTER_TYPE_ATTRIBUTE_GROUP => $this->l('Attribute group'),
                ],
            ],
            'filter_style' => [
                'title' => $this->l('Filter style'),
                'type' => 'select',
                'filter_key' => 'filter_style',
                'list' => [
                    BradFilter::FILTER_STYLE_CHECKBOX => $this->l('Checkbox'),
                    BradFilter::FILTER_STYLE_LIST_OF_VALUES => $this->l('List of values'),
                    BradFilter::FILTER_STYLE_INPUT => $this->l('Input fields'),
                    BradFilter::FILTER_STYLE_SLIDER => $this->l('Slider'),
                ],
            ],
            'custom_height' => [
                'title' => $this->l('Custom height'),
                'type' => 'text',
                'suffix' => $this->l('px'),
            ],
        ];
    }

    /**
     * Initialize form
     */
    protected function initForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Filter'),
            ],
            'input' => [
                [
                    'label' => $this->l('Filter name'),
                    'hint' => $this->l('Only displayed in Back Office'),
                    'type' => 'text',
                    'name' => 'name',
                    'class' => 'fixed-width-xxl',
                    'required' => true,
                ],
                [
                    'label' => $this->l('Filter type'),
                    'type' => 'select',
                    'name' => 'filter_type',
                    'options' => [
                        'query' => BradFilter::getFilterTypesSelect(),
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'required' => true,
                ],
                [
                    'label' => $this->l('Filter style'),
                    'type' => 'select',
                    'name' => 'filter_style',
                    'options' => [
                        'query' => BradFilter::getFilterStylesSelect(),
                        'id' => 'id',
                        'name' => 'name',
                    ],
                    'required' => true,
                ],
                [
                    'label' => $this->l('Custom height'),
                    'hint' => $this->l('Custom height in pixels. Leave empty to use default.'),
                    'type' => 'text',
                    'name' => 'custom_height',
                    'suffix' => $this->l('px'),
                    'class' => 'fixed-width-lg'
                ],
                [
                    'label' => $this->l('Feature or attribute'),
                    'type' => 'text',
                    'name' => 'id_key_search',
                    'class' => 'fixed-width-xxl',
                    'hint' => $this->l('Only if feature or attribute filter style selected'),
                ],
                [
                    'label' => '',
                    'type' => 'hidden',
                    'name' => 'id_key',
                ],
                [
                    'label' => $this->l('Custom ranges'),
                    'type' => 'free',
                    'name' => 'custom_ranges',
                ],
                [
                    'label' => $this->l('Custom ranges suffix'),
                    'hint' => $this->l('E.g.: kg, g, mm, cm & etc.'),
                    'type' => 'text',
                    'name' => 'criteria_suffix',
                    'class' => 'fixed-width-lg',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        if (Shop::isFeatureActive()) {
            $form_fields['input'][] = [
                'type' => 'shop',
                'label' => $this->l('Shop association'),
                'name' => 'checkBoxShopAsso',
                'required' => true,
            ];
        }
    }

    /**
     * Initialize fields value
     */
    protected function initFormFieldsValue()
    {
        $customRanges = [];

        $attributeGroupOrFeature = [BradFilter::FILTER_TYPE_ATTRIBUTE_GROUP, BradFilter::FILTER_TYPE_FEATURE];

        if (in_array($this->object->filter_type, $attributeGroupOrFeature)) {
            if (BradFilter::FILTER_TYPE_ATTRIBUTE_GROUP == $this->object->filter_type) {
                $attributeGroup = new AttributeGroup($this->object->id_key, $this->context->language->id);
                $this->fields_value['id_key_search'] = $attributeGroup->public_name;
            } elseif (BradFilter::FILTER_TYPE_FEATURE == $this->object->filter_type) {
                $feature = new Feature($this->object->id_key, $this->context->language->id);
                $this->fields_value['id_key_search'] = $feature->name;
            }
        }

        if ($this->object->filter_style == BradFilter::FILTER_STYLE_LIST_OF_VALUES) {
            $criterias = new PrestaShopCollection('BradCriteria');
            $criterias->where('id_brad_filter', '=', $this->object->id);
            $criterias->orderBy('position');

            /** @var BradCriteria $criteria */
            foreach ($criterias->getResults() as $criteria) {
                $customRanges[] = [
                    'id' => $criteria->id,
                    'position' => $criteria->position,
                    'min_value' => $criteria->min_value,
                    'max_value' => $criteria->max_value,
                ];
            }
        }

        $this->context->smarty->assign([
            'custom_ranges' => $customRanges,
        ]);

        $this->fields_value['custom_ranges'] = $this->context->smarty->fetch(
            $this->module->getLocalPath().'views/templates/admin/custom-ranges.tpl'
        );
    }

    /**
     * Custom validations
     */
    protected function _childValidation()
    {
        if (Tools::getValue('filter_style') != BradFilter::FILTER_STYLE_LIST_OF_VALUES) {
            return;
        }

        foreach ($_POST as $key => $value) {
            if (0 === strpos($key, 'brad_min_range') ||
                0 === strpos($key, 'brad_max_range')
            ) {
                if (!is_numeric($_POST[$key])) {
                    $this->errors[] = $this->l('Custom ranges must have numeric values only');
                    return;
                }
            }
        }
    }

    /**
     * Load object only if its not loaded
     *
     * @param bool $opt
     *
     * @return BradFilter|false|ObjectModel
     */
    protected function loadObject($opt = false)
    {
        if (Validate::isLoadedObject($this->object)) {
            return $this->object;
        }

        return parent::loadObject($opt);
    }
}
