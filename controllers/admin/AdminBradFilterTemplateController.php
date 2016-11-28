<?php

/**
 * Class AdminBradFilterTemplateController
 */
class AdminBradFilterTemplateController extends AbstractAdminBradModuleController
{
    public function __construct()
    {
        $this->className = 'BradFilterTemplate';
        $this->table = BradFilterTemplate::$definition['table'];
        $this->identifier = BradFilterTemplate::$definition['primary'];

        parent::__construct();
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->addCSS($this->get('brad_css_uri').'admin/filter_template.css');
        $this->addJqueryPlugin('sortable');
    }

    protected function initList()
    {
        $this->fields_list = [
            BradFilterTemplate::$definition['primary'] => [
                'title' => $this->l('ID'),
                'type' => 'text',
            ],
            'name' => [
                'title' => $this->l('Name'),
                'type' => 'text',
            ],
        ];
    }

    protected function initForm()
    {
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
                    'name'  => 'id_parent',
                    'required' => true,
                    'tree'  => [
                        'id' => 'categories-tree',
                        'selected_categories' => [],
                        'root_category' => $this->context->shop->getCategory(),
                        'use_search' => true,
                        'use_checkbox' => true,
                    ],
                ],
                [
                    'type'  => 'free',
                    'label' => $this->l('Template filters'),
                    'name'  => 'template_filters',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];
    }

    public function initFieldsValue()
    {
        $this->fields_value['template_filters'] = $this->context->smarty->fetch(
            $this->module->getLocalPath().'views/templates/admin/template_filters_select.tpl'
        );
    }
}
