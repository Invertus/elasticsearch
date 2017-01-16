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

use Invertus\Brad\Util\Arrays;

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

        $this->initList();
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
        if (!empty($this->fields_list)) {
            return;
        }

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
        $this->validateCategories();
        $this->validateFilters();
    }

    /**
     * Initialize form fields values
     */
    protected function initFormFieldsValue()
    {
        /** @var \Invertus\Brad\Repository\FilterRepository $filterRepository */
        $filterRepository = $this->getRepository('BradFilter');
        $availableTemplateFilters = $filterRepository->findAllFilters($this->context->shop->id);
        $selectedTemplateFilters = [];

        if (Validate::isLoadedObject($this->object)) {
            /** @var \Invertus\Brad\Repository\FilterTemplateRepository $filterTemplateRepository */
            $filterTemplateRepository = $this->getRepository('BradFilterTemplate');
            $selectedFilters = $filterTemplateRepository->findAllFilters($this->object->id);

            $selectedFiltersIds = [];
            $selectedFiltersPositions = [];

            foreach ($selectedFilters as $selectedFilter) {
                $idFilter = (int) $selectedFilter['id_brad_filter'];
                $selectedFiltersIds[] = $idFilter;
                $selectedFiltersPositions[$idFilter] = (int) $selectedFilter['position'];
            }

            foreach ($availableTemplateFilters as $key => $availableTemplateFilter) {
                $idFilter = (int) $availableTemplateFilter['id_brad_filter'];
                if (!in_array($idFilter, $selectedFiltersIds)) {
                    continue;
                }

                $selectedFilter = $availableTemplateFilter;
                $selectedFilter['position'] = $selectedFiltersPositions[$idFilter];

                $selectedTemplateFilters[] = $selectedFilter;
                unset($availableTemplateFilters[$key]);
            }

            if (!empty($selectedTemplateFilters)) {
                Arrays::multiSort($selectedTemplateFilters, 'position');
            }
        }

        $this->context->smarty->assign([
            'available_filters' => $availableTemplateFilters,
            'selected_filters' => $selectedTemplateFilters,
        ]);

        $this->fields_value['template_filters'] = $this->context->smarty->fetch(
            $this->module->getLocalPath().'views/templates/admin/template_filters_select.tpl'
        );
    }

    /**
     * Validate template categories
     */
    protected function validateCategories()
    {
        $checkedCategories = Tools::getValue('filter_template_categories');

        if (empty($checkedCategories)) {
            $this->errors[] = $this->l('You must select at least one category');
            return;
        }

        $excludeTemplates = [];
        if (Tools::isSubmit('id_brad_filter_template')) {
            $excludeTemplates[] = (int) Tools::getValue('id_brad_filter_template');
        }

        /** @var \Invertus\Brad\Repository\FilterTemplateRepository $filterTemplateRepository */
        $filterTemplateRepository = $this->getRepository('BradFilterTemplate');
        $allTemplatesCategories = $filterTemplateRepository->findAllCategories(null, $excludeTemplates);

        $intersect = array_intersect($allTemplatesCategories, $checkedCategories);

        if (!empty($intersect)) {
            $this->errors[] = $this->l('More or more category is already assigned to other template');
        }
    }

    /**
     * Validate template filters
     */
    protected function validateFilters()
    {
        $templateFilers = [];
        $featuresIds = [];
        $attributeGroupsIds = [];

        foreach (array_keys($_POST) as $key) {
            if (0 !== strpos($key, 'template_filter')) {
                continue;
            }

            $filterData = explode(':', $key)[1];
            // $idKey - id_feature / id_attribute_group if filter type is feature or attribute group OR 0 otherwise
            list($idFilter, $filterType, $idKey) = explode('-', $filterData);

            $templateFilers[] = (int) $idFilter;

            if (BradFilter::FILTER_TYPE_ATTRIBUTE_GROUP == (int) $filterType) {
                $attributeGroupsIds[] = (int) $idKey;
            } elseif (BradFilter::FILTER_TYPE_FEATURE == (int) $filterType) {
                $featuresIds[] = (int) $idKey;
            }
        }

        if (empty($templateFilers)) {
            $this->errors[] = $this->l('Template must have at least one filter');
            return;
        }

        if (Arrays::hasDuplicateValues($featuresIds)) {
            $this->errors[] = $this->l('Template cannot have multipile filters with same feature');
            return;
        }

        if (Arrays::hasDuplicateValues($attributeGroupsIds)) {
            $this->errors[] = $this->l('Template cannot have multipile filters with same attribute group');
            return;
        }
    }
}
