<?php

/*
 * File: /upgrade/upgrade-1.2.5.1.php
 */

function upgrade_module_1_2_5_1($module)
{

    $return = true;

    //TRUNCATE TABLES SEO
    $return &= Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'ads_af_seo`');
    $return &= Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'ads_af_seo_lang`');
    $return &= Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'ads_af_seo_shop`');

    //Regenerate
    $module->hookActionAdsImportVehicleFinish(array());

    return $return;
}