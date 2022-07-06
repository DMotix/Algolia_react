<?php

/*
 * File: /upgrade/upgrade-1.2.5.php
 */

function upgrade_module_1_2_5($module)
{

    $return = true;

    //TRUNCATE TABLES SEO + ADD UNIQUE KEY SEO URL
    $return &= Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'ads_af_seo`');
    $return &= Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'ads_af_seo_lang`');
    $return &= Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'ads_af_seo_shop`');
    //$return &= Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ads_af_seo_lang` ADD CONSTRAINT seo_url UNIQUE KEY (seo_url,id_lang,id_shop)');

    $list_fields = Db::getInstance()->executeS('SHOW FIELDS FROM `' . _DB_PREFIX_ . 'ads_af_seo_lang`');
    if (is_array($list_fields)) {
        foreach ($list_fields as $k => $field)
            $list_fields[$k] = $field['Field'];
        if (!in_array('description_top', $list_fields)) {
            $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'ads_af_seo_lang` CHANGE description description_top text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL';
            $return &= Db::getInstance()->execute($query);
        }
        if (!in_array('description_footer', $list_fields)) {
            $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'ads_af_seo_lang` ADD COLUMN `description_footer` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `description_top`';
            $return &= Db::getInstance()->execute($query);
        }
    }

    return $return;
}