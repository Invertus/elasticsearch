<?php
use Invertus\Brad\Config\Sort;

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
                    'type' => 'text',
                    'name' => 'criteria_suffix',
                    'class' => 'fixed-width-lg',
                ],
                [
                    'label' => $this->l('Order by'),
                    'type' => 'select',
                    'name' => 'criteria_order_by',
                    'options' => [
                        'query' => [
                            [
                                'id' => BradFilter::ORDER_BY_NONE,
                                'name' => $this->l('None'),
                            ],
                            [
                                'id' => BradFilter::ORDER_BY_NUMBER_OF_PRODUCTS,
                                'name' => $this->l('Number of products'),
                            ],
                            [
                                'id' => BradFilter::ORDER_BY_NATURAL,
                                'name' => $this->l('Natural'),
                            ],
                            [
                                'id' => BradFilter::ORDER_BY_ALPHA_NUM,
                                'name' => $this->l('Alpha / Numeric'),
                            ],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'label' => $this->l('Order way'),
                    'type' => 'select',
                    'name' => 'criteria_order_way',
                    'options' => [
                        'query' => [
                            [
                                'id' => Sort::WAY_DESC,
                                'name' => $this->l('Descending'),
                            ],
                            [
                                'id' => Sort::WAY_ASC,
                                'name' => $this->l('Ascending'),
                            ],
                        ],
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];
    }

    /**
     * Initialize fields value
     */
    protected function initFieldsValue()
    {
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
}
