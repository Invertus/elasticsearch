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

if (!isset($_GET['id_shop']) && isset($argv[3])) {
    $_GET['id_shop'] = $argv[3];
}

if (!isset($_GET['cron']) && isset($argv[2])) {
    $_GET['cron'] = $argv[2];
}

if (!isset($_GET['token']) && isset($argv[1])) {
    $_GET['token'] = $argv[1];
}

if (!isset($_GET['action']) && isset($argv[4])) {
    $_GET['action'] = $argv[4];
}

include_once(dirname(__FILE__).'/../../config/config.inc.php');

$cron = Tools::getValue('cron');
$token = Tools::getValue('token');

$moduleName = 'brad';

if ($token != Tools::encrypt($moduleName)) {
    exit;
}

if (!Module::isEnabled($moduleName)) {
    echo sprintf('Module %s is not enabled', $moduleName);
    die;
}

/** @var $module Brad */
$module = Module::getInstanceByName($moduleName);

$shopId = Tools::isSubmit('id_shop') ? Tools::getValue('id_shop') : Configuration::get('PS_SHOP_DEFAULT');

ini_set('memory_limit', '512M');
ini_set('max_execution_time', '100000');

$module->runTask($cron, $shopId);
