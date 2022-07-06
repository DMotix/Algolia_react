<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Carousel
 *
 * @author Quentin
 */
class AdsAlgoliaAction extends ObjectModel {

    public $id_ads_button_action;
    public $id_shop;
    public $text_btn;
    public $icon_btn;
    public $url_btn;
    public $bgcolor_btn;
    public $target_btn;
    public $attr_btn;
    public $position;
    public $active;
    public static $definition = array(
        'table' => 'ads_button_action',
        'primary' => 'id_ads_button_action',
        'fields' => array(
            'id_shop' => array('type' => self::TYPE_STRING, 'required' => true),
            'text_btn' => array('type' => self::TYPE_STRING, 'required' => true),
            'icon_btn' => array('type' => self::TYPE_STRING, 'required' => false),
            'url_btn' => array('type' => self::TYPE_STRING, 'required' => true),
            'bgcolor_btn' => array('type' => self::TYPE_STRING, 'required' => false),
            'target_btn' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
            'attr_btn' => array('type' => self::TYPE_STRING, 'required' => false),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true)
        )
    );

    public static function installInDB() {
        return Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ads_button_action` (
                    `id_ads_button_action` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `id_shop` VARCHAR(1024) NOT NULL DEFAULT 1,
                    `text_btn` varchar(255) NOT NULL,
                    `icon_btn` varchar(255) DEFAULT NULL,
                    `url_btn` text NOT NULL,
                    `bgcolor_btn` varchar(255) DEFAULT NULL,
                    `target_btn` tinyint(1) unsigned NOT NULL,
                    `attr_btn` text DEFAULT NULL,
                    `position` int(11) unsigned NOT NULL,
                    `active` tinyint(1) unsigned NOT NULL,
                    PRIMARY KEY (`id_ads_button_action`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;');
    }

    public function uninstallInDB() {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ads_button_action`');
    }

    /**
     *
     * @param type $positions
     * @return type
     */
    public function positionUpdate($positions) {
        $sql = '';
        foreach ($positions as $position => $id) {
            $position_of_id = explode('_', $id);
            $sql .= 'UPDATE `' . _DB_PREFIX_ . 'ads_button_action` SET `position` = ' . ((int) $position) . '
                    WHERE `id_ads_button_action` = ' . (int) $position_of_id[2] . '; ';
        }
        return DB::getInstance()->execute($sql);
    }

    public static function getMaxPositions() {
        $sql = 'SELECT COUNT(*)
                FROM `' . _DB_PREFIX_ . 'ads_button_action`';

        return Db::getInstance()->getValue($sql);
    }
    
    public static function getListOfButton(){
        $id_shop = Context::getContext()->shop->id;
        $sql = 'SELECT *
                FROM `' . _DB_PREFIX_ . 'ads_button_action`
                WHERE `active` = 1 AND `url_btn` <> "" AND FIND_IN_SET(' . (int) ($id_shop) . ',`id_shop`)
                ORDER BY `position` ASC';

        return Db::getInstance()->executeS($sql);
    }
    
    public function update($null_values = false) {
        /* HACK : maj multishop */
        if(Tools::getIsset("cat_shop_association")){
            $ids_shops = implode(",", Tools::getValue("cat_shop_association"));
            $this->id_shop = $ids_shops;
        }else{
            $this->id_shop = (int)Context::getContext()->shop->id;
        }
        /* END HACK */
        return parent::update($null_values);
    }
    
    public function add($auto_date = true, $null_values = false) {
        $this->id_shop = (int)Context::getContext()->shop->id;
        return parent::add($auto_date, $null_values);
    }
    
}
