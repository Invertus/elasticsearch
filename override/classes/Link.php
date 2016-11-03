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
