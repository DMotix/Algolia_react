<?php

class AdsAlgoliaSeoMassive extends ObjectModel
{

    public $id;
    public $cronjob = 1;
    public $meta_title;
    public $meta_description;
    public $meta_keywords;
    public $title;
    public $description_top;
    public $description_footer;
    public $criteria;
    public $active = 1;
    public $position;
    public $date_add;
    protected $fieldsRequired = array('position');
    protected $fieldsRequiredLang = array(
        'criteria',
        'meta_title',
        'meta_description',
        'title');
    protected $fieldsSizeLang = array(
        'meta_title' => 128,
        'meta_description' => 255,
        'title' => 128,
        'meta_keywords' => 255);
    protected $fieldsValidateLang = array(
        'meta_title' => 'isGenericName',
        'meta_description' => 'isGenericName',
        'meta_keywords' => 'isGenericName',
        'title' => 'isGenericName',
        'description_top' => 'isString',
        'description_footer' => 'isString',
        'criteria' => 'isString'
    );

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'ads_af_seo_massive',
        'primary' => 'id_seo_massive',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => array(
            'criteria' => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => true),
            'meta_title' => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'meta_description' => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'title' => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'meta_keywords' => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'description_top' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'lang' => true, 'required' => false),
            'description_footer' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'lang' => true, 'required' => false),
            /* SHOP FIELDS */
            'position' => array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId', 'required' => true),
            'active' => array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'),
            'date_add' => array('type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'),
        ),
    );

    public function __construct($id = NULL, $id_lang = NULL, $id_shop = null)
    {
        if (version_compare(_PS_VERSION_, '1.5.0.0', '>=')) {

            if (version_compare(_PS_VERSION_, '1.5', '>=') && version_compare(_PS_VERSION_, '1.5.2.0', '<=') && class_exists("ShopPrestaModule")) {
                ShopPrestaModule::PrestaModule_setAssoTable(self::$definition['table']);
            } else {
                //Table _shop
                Shop::addTableAssociation(self::$definition['table'], array('type' => 'shop'));
                //Table _lang (pour multishop)
                Shop::addTableAssociation(self::$definition['table'] . "_lang", array('type' => 'fk_shop'));
            }
            parent::__construct($id, $id_lang, $id_shop);
        } else {
            parent::__construct($id, $id_lang);
        }
    }

    public static function getMaxPositions()
    {
        $sql = 'SELECT COUNT(*)
                FROM `' . _DB_PREFIX_ . self::$definition['table'] . '_shop`';

        return Db::getInstance()->getValue($sql);
    }

    public static function getNewLastPosition()
    {
        return (Db::getInstance()->getValue('
			SELECT IFNULL(MAX(position),0)+1
			FROM `' . _DB_PREFIX_ . self::$definition['table'] . '_shop`'
        ));
    }

    public function getFields()
    {
        parent::validateFields();
        $fields['cronjob'] = pSQL($this->cronjob);
        return $fields;
    }

    public function add($auto_date = true, $null_values = false)
    {
        $this->position = self::getNewLastPosition();
        return parent::add($auto_date, $null_values);
    }

    public function positionUpdate($positions)
    {
        $sql = '';
        if (Shop::isFeatureActive()) {
            if (Shop::getContext() == Shop::CONTEXT_ALL) {
                foreach ($positions as $position => $id) {
                    $position_of_id = explode('_', $id);
                    $sql .= 'UPDATE `' . _DB_PREFIX_ . self::$definition['table'] . '_shop` SET `position` = ' . ((int)$position) . '
                    WHERE `' . self::$definition['primary'] . '` = ' . (int)$position_of_id[2] . '; ';
                }
            } else {
                foreach ($positions as $position => $id) {
                    $position_of_id = explode('_', $id);
                    $sql .= 'UPDATE `' . _DB_PREFIX_ . self::$definition['table'] . '_shop` SET `position` = ' . ((int)$position) . '
                    WHERE id_shop=' . (int)Context::getContext()->shop->id . ' AND `' . self::$definition['primary'] . '` = ' . (int)$position_of_id[2] . '; ';
                }
            }
        } else {
            foreach ($positions as $position => $id) {
                $position_of_id = explode('_', $id);
                $sql .= 'UPDATE `' . _DB_PREFIX_ . self::$definition['table'] . '_shop` SET `position` = ' . ((int)$position) . '
                    WHERE `' . self::$definition['primary'] . '` = ' . (int)$position_of_id[2] . '; ';
            }
        }
        return DB::getInstance()->execute($sql);
    }

    private static $_getAllSeoMassiveCache = array();

    public static function getAllSeoMassive($id_shop, $id_lang)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$_getAllSeoMassiveCache[$cacheKey])) return self::$_getAllSeoMassiveCache[$cacheKey];
        self::$_getAllSeoMassiveCache[$cacheKey] = Db::getInstance()->ExecuteS('
		SELECT *
		FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` aasm
		LEFT JOIN `' . _DB_PREFIX_ . self::$definition['table'] . '_lang` aasml ON (aasm.`' . self::$definition['primary'] . '` = aasml.`' . self::$definition['primary'] . '` AND aasml.`id_lang` = ' . ((int)$id_lang) . ')
		LEFT JOIN `' . _DB_PREFIX_ . self::$definition['table'] . '_shop` aasms ON (aasm.`' . self::$definition['primary'] . '` = aasms.`' . self::$definition['primary'] . '` AND aasms.`id_shop` = ' . ((int)$id_shop) . ')
		WHERE 1
		AND aasms.`active` = 1
		GROUP BY aasm.`' . self::$definition['primary'] . '`
		ORDER BY aasms.`position` ASC');
        return self::$_getAllSeoMassiveCache[$cacheKey];
    }
}
