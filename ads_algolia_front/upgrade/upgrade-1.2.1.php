<?php

/*
 * File: /upgrade/upgrade-1.2.1.php
 */

if (file_exists(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'algolia/classes/Registry.php'))
    require_once(_PS_ROOT_DIR_ . _MODULE_DIR_ . 'algolia/classes/Registry.php');

function upgrade_module_1_2_1($module) {

    $algolia_registry = \Algolia\Core\Registry::getInstance();
    $algolia_registry->__set("number_by_page", 12);

    return true;
}