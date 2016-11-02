<?php

class Link extends LinkCore
{
    public function getPageLink($controller, $ssl = null, $id_lang = null, $request = null, $request_url_encode = false, $id_shop = null, $relative_protocol = false)
    {
        $controllerInstance = Context::getContext()->controller;

        if ($controllerInstance instanceof AbstractBradModuleFrontController) {
            return $this->getModuleLink('brad', 'search');
        }

        return parent::getPageLink($controller, $ssl, $id_lang, $request, $request_url_encode, $id_shop, $relative_protocol);
    }

    public function getPaginationLink($type, $id_object, $nb = false, $sort = false, $pagination = false, $array = false)
    {
        $result = parent::getPaginationLink($type, $id_object, $nb, $sort, $pagination, $array);

        if (!is_array($result)) {
            return $result;
        }

        $controllerInstance = Context::getContext()->controller;
        if (!$controllerInstance instanceof AbstractBradModuleFrontController) {
            return $result;
        }

        // Unset search query to avaid duplications in url
        unset($result['search_query']);

        $isFriendlyUrlEnabled = (bool) Configuration::get('PS_REWRITING_SETTINGS');
        if ($isFriendlyUrlEnabled) {
            unset($result['fc'], $result['module']);
        }

        return $result;
    }
}
