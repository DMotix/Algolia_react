<?php

class SearchAlert extends ObjectModel
{

    public $id;
    public $id_customer = 0;
    public $marque;
    public $modele;
    public $energie;
    public $kilometrage_max;
    public $annee_max;
    public $prix_ttc_max;
    public $active;
    public $date_add;
    public static $definition = array(
        'table' => 'ads_af_search_alert',
        'primary' => 'id_ads_af_search_alert',
        'multishop' => true,
        'fields' => array(
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'marque' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'modele' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'energie' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'kilometrage_max' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'annee_max' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'prix_ttc_max' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'copy_post' => false)
        ),
    );

    public function __construct($id = NULL, $id_lang = NULL, $id_shop = null)
    {
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {
            if (version_compare(_PS_VERSION_, '1.5', '>=') && version_compare(_PS_VERSION_, '1.5.2.0', '<=') && class_exists("ShopPrestaModule")) {
                ShopPrestaModule::PrestaModule_setAssoTable(self::$definition['table']);
            } else {
                Shop::addTableAssociation(self::$definition['table'], array('type' => 'shop'));
            }
            parent::__construct($id, $id_lang, $id_shop);
        } else {
            parent::__construct($id, $id_lang);
        }
    }

    public static function getList()
    {
        return DB::getInstance()->executeS('
			SELECT *
			FROM ' . _DB_PREFIX_ . 'ads_af_search_alert
		');
    }

    public static function existIdCustomer($id_customer)
    {
        return DB::getInstance()->getRow('
			SELECT aasa.*
			FROM `' . _DB_PREFIX_ . 'ads_af_search_alert` aasa
			WHERE aasa.`active` = 1 AND aasa.`id_customer` = ' . (int)$id_customer
        );
    }

    public function add($autodate = true, $nullValues = false)
    {
        $this->id_customer = (int)\Context::getContext()->cookie->id_customer;
        $this->active = 1;
        return parent::add($autodate, $nullValues);
    }


    public function validateFields($die = true, $error_return = false)
    {
        $empty = true;
        foreach ($this->def['fields'] as $field => $data) {
            if (!empty($this->$field))
                $empty = false;
        }
        $message = 'Veuillez renseigner au moins un champ';
        if ($empty == true) {
            if ($die) {
                throw new PrestaShopException($message);
            }
            return $error_return ? $message : false;
        } else {
            foreach ($this->def['fields'] as $field => $data) {
                if (!empty($data['lang'])) {
                    continue;
                }

                if (is_array($this->update_fields) && empty($this->update_fields[$field]) && isset($this->def['fields'][$field]['shop']) && $this->def['fields'][$field]['shop']) {
                    continue;
                }

                $message = $this->validateField($field, $this->$field);
                if ($message !== true) {
                    if ($die) {
                        throw new PrestaShopException($message);
                    }
                    return $error_return ? $message : false;
                }
            }

            return true;
        }

    }

    /*
	 * Get objects that will be viewed on "My alerts" page
	 */
    public static function getSearchAlerts($id_customer, $id_lang, Shop $shop = null)
    {
        Shop::addTableAssociation(self::$definition['table'], array('type' => 'shop'));
        if (!Validate::isUnsignedId($id_customer) || !Validate::isUnsignedId($id_lang))
            die (Tools::displayError());

        if (!$shop)
            $shop = Context::getContext()->shop;

        $customer = new Customer($id_customer);

        $list_shop_ids = Shop::getContextListShopID(false);

        $sql = '
			SELECT sa.*
			FROM `'._DB_PREFIX_.self::$definition['table'].'` sa
			'.Shop::addSqlAssociation(self::$definition['table'], 'sa').'
			WHERE sa.`active` = 1
			AND sa.`id_customer` = '.(int)$customer->id .'
			ORDER BY sa.`date_add` desc';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }
}
