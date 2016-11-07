<?php

/**
 * Class AdminBradFilterController
 */
class AdminBradFilterController extends AbstractAdminBradModuleController
{
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

        $translatedFilterTypeNames = BradFilter::getFilterTypeNames();

        foreach ($this->_list as &$listItem) {
            $listItem['filter_type'] = $translatedFilterTypeNames[$listItem['filter_type']];
            $listItem['filter_style'] = ''; //@todo: get translated filter style name
        }
    }

    /**
     * Initialize filters list
     */
    protected function initList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->lang = true;

        $this->fields_list = [
            'id_brad_filter' => [
                'title' => $this->l('ID'),
                'type' => 'text',
                'width' => 20,
            ],
            'name' => [
                'title' => $this->l('Default name'),
                'type' => 'text',
            ],
            'custom_name' => [
                'title' => $this->l('Custom name'),
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
}
