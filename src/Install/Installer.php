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

use Brad;
use Configuration;
use Invertus\Brad\Config\Setting;
use Tools;

/**
 * Class Installer
 *
 * @package Invertus\Brad\Install
 */
class Installer extends AbstractInstaller
{
    /**
     * @var Brad
     */
    private $module;

    /**
     * Installer constructor
     *
     * @param Brad $module
     */
    public function __construct(Brad $module)
    {
        $this->module = $module;
    }

    /**
     * Install module, register hooks, install tabs & etc.
     *
     * @return bool TRUE if everything were installed or FALSE if anything went wrong
     */
    public function install()
    {
        if (!$this->registerHooks()) {
            return false;
        }

        if (!$this->installTabs()) {
            return false;
        }

        if (!$this->installSettings()) {

        }

        return true;
    }

    /**
     * Uninstall module, tabs & etc.
     *
     * @return bool TRUE if everything were uninstalled or FALSE if anything went wrong
     */
    public function uninstall()
    {
        if (!$this->uninstallTabs()) {
            return false;
        }

        if (!$this->uninstallSettings()) {
            return false;
        }

        return true;
    }

    /**
     * Get module name
     *
     * @return string
     */
    protected function getModuleName()
    {
        return $this->module->name;
    }

    /**
     * Definition of tabs (controllers) to install
     *
     * @return array
     */
    protected function tabs()
    {
        return [
            [
                'name' => $this->module->l('BRAD', __CLASS__),
                'parent' => 1,
                'class_name' => Brad::ADMIN_BRAD_MODULE_CONTROLLER,
            ],
            [
                'name' => $this->module->l('Settings', __CLASS__),
                'parent' => Brad::ADMIN_BRAD_MODULE_CONTROLLER,
                'class_name' => Brad::ADMIN_BRAD_SETTING_CONTROLLER,
            ],
            [
                'name' => $this->module->l('Advanced settings', __CLASS__),
                'parent' => Brad::ADMIN_BRAD_MODULE_CONTROLLER,
                'class_name' => Brad::ADMIN_BRAD_ADVANCED_SETTING_CONTROLLER,
            ],
            [
                'name' => $this->module->l('Info', __CLASS__),
                'parent' => Brad::ADMIN_BRAD_MODULE_CONTROLLER,
                'class_name' => Brad::ADMIN_BRAD_INFO_CONTROLLER,
            ],
        ];
    }

    /**
     * Register module hooks
     *
     * @return bool
     */
    private function registerHooks()
    {
        $hooks = [
            'moduleRoutes',
            'displayBackOfficeHeader',
            'displayTop',
            'displayHeader',
            'actionObjectProductAddAfter',
            'actionObjectProductUpdateAfter',
            'actionObjectProductDeleteAfter',
        ];

        foreach ($hooks as $hookName) {
            if (!$this->module->registerHook($hookName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Install default module settings
     *
     * @return bool
     */
    private function installSettings()
    {
        $settings = Setting::getDefaultSettings();

        foreach ($settings as $settingName => $value) {
            if (!Configuration::updateValue($settingName, $value)) {
                return false;
            }
        }

        $indexPrefix = strtolower(Tools::passwdGen()).'_';
        if (!Configuration::updateValue(Setting::INDEX_PREFIX, $indexPrefix)) {
            return false;
        }

        return true;
    }

    /**
     * Uninstall module settings
     *
     * @return bool
     */
    private function uninstallSettings()
    {
        $settings = array_keys(Setting::getDefaultSettings());
        $settings[] = Setting::INDEX_PREFIX;

        foreach ($settings as $settingName) {
            if (!Configuration::deleteByName($settingName)) {
                return false;
            }
        }

        return true;
    }
}
