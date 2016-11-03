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

namespace Invertus\Brad\Install;

use Language;
use Tab;

/**
 * Class AbstractInstaller
 *
 * @package Invertus\Brad\Install
 */
abstract class AbstractInstaller
{
    /**
     * Get module name
     *
     * @return string
     */
    abstract protected function getModuleName();

    /**
     * Definition of tabs (controllers) to install
     *
     * @return array
     */
    abstract protected function tabs();

    /**
     * Installs all tabs defined in tabs() method
     *
     * @return bool
     */
    protected function installTabs()
    {
        $tabs = $this->tabs();

        if (empty($tabs) && !is_array($tabs)) {
            return true;
        }

        foreach ($tabs as $tab) {
            $parentId = (int) Tab::getIdFromClassName($tab['parent']);

            if (!$this->installTab($tab['name'], $parentId, $tab['class_name'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Uninstalls all tabs defined in tabs() method
     *
     * @return bool
     */
    protected function uninstallTabs()
    {
        $tabs = $this->tabs();

        if (empty($tabs) && !is_array($tabs)) {
            return true;
        }

        foreach ($tabs as $tab) {
            if (!$this->uninstallTab($tab['class_name'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Install single tab
     *
     * @param string $name Tab name
     * @param int $parentId Tab parent id
     * @param string $className Tab class name
     *
     * @return bool
     */
    private function installTab($name, $parentId, $className)
    {
        if (!Tab::getIdFromClassName($className)) {
            $moduleTab = new Tab();
            $languages = Language::getLanguages(true);

            foreach ($languages as $language) {
                $moduleTab->name[$language['id_lang']] = $name;
            }

            $moduleTab->class_name = $className;
            $moduleTab->id_parent = $parentId;
            $moduleTab->module = $this->getModuleName();

            if (!$moduleTab->save()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Uninstall single tab by name
     *
     * @param string $className Tab class name
     * @return bool
     */
    private function uninstallTab($className)
    {
        if ($tabId = (int) Tab::getIdFromClassName($className)) {
            $tab = new Tab($tabId);

            return $tab->delete();
        }

        return true;
    }
}
