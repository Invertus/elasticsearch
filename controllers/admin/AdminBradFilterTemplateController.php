<?php

/**
 * Class AdminBradFilterTemplateController
 */
class AdminBradFilterTemplateController extends AbstractAdminBradModuleController
{
    /**
     * @var BradFilterTemplate
     */
    protected $object;

    /**
     * AdminBradFilterTemplateController constructor.
     */
    public function __construct()
    {
        $this->className = 'BradFilterTemplate';
        $this->table = BradFilterTemplate::$definition['table'];
        $this->identifier = BradFilterTemplate::$definition['primary'];

        parent::__construct();
    }

    /**
     * Add custom CSS & JS
     */
    public function setMedia()
    {
        parent::setMedia();
        $this->addCSS($this->get('brad_css_uri').'admin/filter_template.css');
        $this->addJS($this->get('brad_js_uri').'admin/filter_template.js');
        $this->addJqueryPlugin('sortable');
    }

    /**
     * Initialize list fields
     */
    protected function initList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->fields_list = [
            BradFilterTemplate::$definition['primary'] => [
                'title' => $this->l('ID'),
                'type' => 'text',
                'width' => 20,
            ],
            'name' => [
                'title' => $this->l('Name'),
                'type' => 'text',
            ],
        ];
    }

    /**
     * Initialize form
     */
    protected function initForm()
    {
        $selectedCategories = [];

        if (Validate::isLoadedObject($this->object)) {
            /** @var \Invertus\Brad\Repository\FilterTemplateRepository $filterTemplateRepository */
            $filterTemplateRepository = $this->getRepository('BradFilterTemplate');
            $selectedCategories = $filterTemplateRepository->findAllCategories($this->object->id);
        }

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Filter template'),
            ],
            'input' => [
                [
                    'label' => $this->l('Name'),
                    'name' => 'name',
                    'type' => 'text',
                    'required' => true,
                    'class' => 'fixed-width-xxl',
                ],
                [
                    'type'  => 'categories',
                    'label' => $this->l('Categories'),
                    'name'  => 'filter_template_categories',
                    'required' => true,
                    'tree'  => [
                        'id' => 'categories-tree',
                        'selected_categories' => $selectedCategories,
                        'root_category' => $this->context->shop->getCategory(),
                        'use_search' => true,
                        'use_checkbox' => true,
                    ],
                ],
                [
                    'type'  => 'free',
                    'label' => $this->l('Enable template filters'),
                    'name'  => 'template_filters',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];
    }

    /**
     * Add custom form validation
     */
    protected function _childValidation()
    {
        //@todo: add template validation (check if category already selected in other template)
    }

    /**
     * Initialize form fields values
     */
    public function initFormFieldsValue()
    {
        /** @var \Invertus\Brad\Repository\FilterRepository $filterRepository */
        $filterRepository = $this->getRepository('BradFilter');
        $availableFilters = $filterRepository->findAllByShopId($this->context->shop->id);

        if (Validate::isLoadedObject($this->object)) {
            /** @var \Invertus\Brad\Repository\FilterTemplateRepository $filterTemplateRepository */
            $filterTemplateRepository = $this->getRepository('BradFilterTemplate');
            $selectedFilters = $filterTemplateRepository->findAllFilters($this->object->id);
            //@todo: mark available filters as selected
        }

        $this->context->smarty->assign([
            'available_filters' => $availableFilters,
        ]);

        $this->fields_value['template_filters'] = $this->context->smarty->fetch(
            $this->module->getLocalPath().'views/templates/admin/template_filters_select.tpl'
        );
    }
}
