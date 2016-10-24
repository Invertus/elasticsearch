<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Brad
 */
class Brad extends Module
{
    /**
     * Brad constructor.
     */
    public function __construct()
    {
        $this->name = 'brad';
        $this->tab = 'front_office_features';
        $this->version = '2.0.0-dev';
        $this->author = 'Invertus';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('BRAD');
        $this->description = $this->l('Elasticsearch® module for PrestaShop that makes search and filter significantly faster');
        $this->ps_versions_compliancy = ['min' => '1.6.1.0', 'max' => '1.6.2.0'];
    }
}
