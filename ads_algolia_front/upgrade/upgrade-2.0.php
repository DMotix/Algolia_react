<?php

/*
 * File: /upgrade/upgrade-2.0.php
 */

function upgrade_module_2_0($module)
{
    $module->installTab($module->l('Algolia Pages SEO'), -1, 'AdminAlgoliaSeo');
    $module->installTab($module->l('Algolia Pages SEO Massive'), -1, 'AdminAlgoliaSeoMassive');
    $module->registerHook('displayBackOfficeHeader');
    $module->registerHook('displayAdminListBefore');
    $module->installDB();
    return true;
}