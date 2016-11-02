<?php

if (!isset($_GET['id_shop']) && isset($argv[3])) {
    $_GET['id_shop'] = $argv[3];
}

include_once(dirname(__FILE__).'/../../config/config.inc.php');

if (isset($_GET['cron'])) {
    $cron = Tools::getValue('cron');
} else {
    $cron = $argv[2];
}

if (isset($_GET['token'])) {
    $token = Tools::getValue('token');
} else {
    $token = $argv[1];
}

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
