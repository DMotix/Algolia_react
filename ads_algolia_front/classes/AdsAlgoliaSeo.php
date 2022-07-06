<?php

class AdsAlgoliaSeo extends ObjectModel
{

    public $id;
    public $meta_title;
    public $meta_description;
    public $meta_keywords;
    public $title;
    public $seo_url;
    public $description_top;
    public $description_footer;
    public $criteria;
    public $seo_key;
    public $auto;
    public $active = 1;
    public $date_add;
    public $cross_links;
    protected $fieldsRequired = array('criteria');
    protected $fieldsRequiredLang = array(
        'meta_title',
        'meta_description',
        'title',
        'seo_url');
    protected $fieldsSizeLang = array(
        'meta_title' => 128,
        'meta_description' => 255,
        'title' => 128,
        'seo_url' => 128,
        'meta_keywords' => 255);
    protected $fieldsValidateLang = array(
        'meta_title' => 'isGenericName',
        'meta_description' => 'isGenericName',
        'meta_keywords' => 'isGenericName',
        'title' => 'isGenericName',
        'description_top' => 'isString',
        'description_footer' => 'isString',
        'seo_url' => 'isGenericName'
    );

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'ads_af_seo',
        'primary' => 'id_seo',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => array(
            'meta_title' => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'meta_description' => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'title' => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'seo_url' => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'meta_keywords' => array('type' => self::TYPE_STRING, 'lang' => true, 'required' => false),
            'description_top' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'lang' => true, 'required' => false),
            'description_footer' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'lang' => true, 'required' => false),
            /* SHOP FIELDS */
            'auto' => array('type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'),
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

    public function getFields()
    {
        parent::validateFields();
        $fields['criteria'] = pSQL($this->criteria);
        $fields['seo_key'] = pSQL($this->seo_key);
        return $fields;
    }

    private function getSeoKeyFromCriteria($criteria)
    {
        if (is_array($criteria))
            $criteria = $this->_seoCriterionSort($criteria);
        //$criteria = str_replace('biscriterion_', '', $criteria);
        $seo_key = md5(Tools::jsonEncode($criteria));
        return $seo_key;
    }

    private function _seoCriterionSort($criterions)
    {
        if (is_array($criterions)) {
            asort($criterions);
            foreach ($criterions as $k => $criterionList) {
                if (is_array($criterions[$k]))
                    $criterions[$k] = $this->_seoCriterionSort($criterions[$k]);
            }
        }
        return $criterions;
    }

    public function onSave()
    {
        $this->seo_key = $this->getSeoKeyFromCriteria($this->criteria);
        $seo_urls = explode("/", $this->seo_url[Context::getContext()->language->id]);
        $new_seo_urls = array();
        foreach ($seo_urls as $seo_url) {
            $new_seo_urls[] = Tools::link_rewrite($seo_url);
        }
        $this->seo_url[Context::getContext()->language->id] = implode("/", $new_seo_urls);
    }

    public function add($auto_date = true, $null_values = false)
    {
        $this->onSave();
        return parent::add($auto_date, $null_values);
    }

    public function update($short = false, $null_values = false)
    {
        //cross links
        if (!$short && $this->id)
            $this->cleanCrossLinks();
        $this->onSave();
        $ret = parent::update($null_values);
        if (is_array($this->cross_links) && sizeof($this->cross_links))
            $this->saveCrossLinks();
        return $ret;
    }

    public function save($null_values = false, $auto_date = true)
    {
        //cross links
        if ($this->id)
            $this->cleanCrossLinks();
        $this->onSave();
        $ret = parent::save($null_values, $auto_date);
        if (is_array($this->cross_links) && sizeof($this->cross_links))
            $this->saveCrossLinks();
        return $ret;
    }

    public function delete()
    {
        $this->cleanCrossLinks();
        return parent::delete();
    }

    public function processSeoCreate($die = false)
    {
        $this->seo_key = $this->getSeoKeyFromCriteria($this->criteria);
        $this->id = self::seoExists($this->seo_key);
        //exit when SEO page are not auto
        if ($this->id) {
            $obj = new AdsAlgoliaSeo($this->id, Context::getContext()->language->id, $this->id_shop);
            if ($obj->auto <> null && (int)$obj->auto === 0)
                return;
        }
        //if new ID and SEO URL exist return false
        if (!$this->id && self::getSeoSearchBySeoUrl($this->seo_url, Context::getContext()->language->id, $this->id_shop))
            if ($die)
                throw new PrestaShopException('seo_url must be unique');
            else
                return false;
        //if (!self::seoExists($this->seo_key))
        $this->save();
        $seo = new AdsAlgoliaSeo((int)$this->id, Context::getContext()->language->id, $this->id_shop);
        if ($this->id_supplier) {
            $seo->id_supplier = $this->id_supplier;
            $this->activeSeoByShop($seo);
        }
    }

    private static $_seoExistsCache = array();

    public static function seoExists($seo_key)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        //if (isset(self::$_seoExistsCache[$cacheKey])) return self::$_seoExistsCache[$cacheKey];
        $row = Db::getInstance()->getRow('
			SELECT `id_seo`
			FROM `' . _DB_PREFIX_ . self::$definition['table'] . '`
			WHERE `seo_key` = "' . pSQL($seo_key) . '"');
        self::$_seoExistsCache[$cacheKey] = (isset($row['id_seo']) ? $row['id_seo'] : false);
        return self::$_seoExistsCache[$cacheKey];
    }

    public function criteriaGenerator($id_lang, $criterias = array())
    {
        /*
        $new_criterias = array();

        foreach ($criterias as $criteria) {
            //Feature case
            if (preg_match('#feature_#', $criteria)) {
                $criteria = str_replace('feature_', '', $criteria);
                $info_criterion = explode('_', $criteria);

                //Criterion name
                $feature_name = Feature::getFeature($id_lang, $info_criterion[0])["name"];
                $feature_name = Tools::slugify($feature_name, "_");
                //Rules specific
                if ($feature_name == "marque" OR $feature_name == "modele")
                    $feature_name = $feature_name . ".name";

                //Criterion value
                $feature_value = FeatureValue::getFeatureValueWithLang($id_lang, $info_criterion[1])["value"];
                $feature_value = $feature_value;
                $new_criterias[$feature_name][] = $feature_value;
            }
            //Supplier ville
            if (preg_match('#supplier_city_#', $criteria)) {
                $criteria = str_replace('supplier_city_', '', $criteria);
                $supplier = new Supplier((int)$criteria);
                $new_criterias["supplier.city"][] = $supplier->ville;
            }
            //Supplier name
            if (preg_match('#supplier_id_#', $criteria)) {
                $criteria = str_replace('supplier_id_', '', $criteria);
                $supplier = new Supplier((int)$criteria);
                $new_criterias["supplier.name"][] = $supplier->name;
            }
            //Category name
            if (preg_match('#category_id_#', $criteria)) {
                $criteria = str_replace('category_id_', '', $criteria);
                $category = new Category((int)$criteria, $id_lang);
                $new_criterias["category"][] = $category->name;
            }
        }

        return $new_criterias;
        */
        return $criterias;
    }

    private static $_getSeoSearchByIdSeoCache = array();

    public static function getSeoSearchByIdSeo($id_seo, $id_lang, $id_shop)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$_getSeoSearchByIdSeoCache[$cacheKey])) return self::$_getSeoSearchByIdSeoCache[$cacheKey];
        self::$_getSeoSearchByIdSeoCache[$cacheKey] = Db::getInstance()->ExecuteS('
		SELECT *
		FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` aas
		LEFT JOIN `' . _DB_PREFIX_ . 'ads_af_seo_lang` aasl ON (aas.`id_seo` = aasl.`id_seo` AND aasl.`id_lang` = ' . ((int)$id_lang) . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'ads_af_seo_shop` aass ON (aas.`id_seo` = aass.`id_seo` AND aass.`id_shop` = ' . ((int)$id_shop) . ')
		WHERE aas.`id_seo` = "' . ((int)$id_seo) . '" AND aass.`active` = 1 AND aasl.`id_shop` = ' . ((int)$id_shop) . '
		GROUP BY aas.`id_seo`
		LIMIT 1');
        return self::$_getSeoSearchByIdSeoCache[$cacheKey];
    }

    private static $_getSeoSearchBySeoUrlCache = array();

    public static function getSeoSearchBySeoUrl($seo_url, $id_lang, $id_shop)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$_getSeoSearchBySeoUrlCache[$cacheKey])) return self::$_getSeoSearchBySeoUrlCache[$cacheKey];
        self::$_getSeoSearchBySeoUrlCache[$cacheKey] = Db::getInstance()->ExecuteS('
		SELECT *
		FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` aas
		LEFT JOIN `' . _DB_PREFIX_ . 'ads_af_seo_lang` aasl ON (aas.`id_seo` = aasl.`id_seo` AND aasl.`id_lang` = ' . ((int)$id_lang) . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'ads_af_seo_shop` aass ON (aas.`id_seo` = aass.`id_seo` AND aass.`id_shop` = ' . ((int)$id_shop) . ')
		WHERE aasl.`seo_url` = "' . ($seo_url) . '" AND aass.`active` = 1 AND aasl.`id_shop` = ' . ((int)$id_shop) . '
		GROUP BY aas.`id_seo`
		LIMIT 1');
        return self::$_getSeoSearchBySeoUrlCache[$cacheKey];
    }

    private static $_getSeoSearchsCache = array();

    public static function getSeoSearchs($id_shop, $id_lang, $withDeleted = 0)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$_getSeoSearchsCache[$cacheKey])) return self::$_getSeoSearchsCache[$cacheKey];
        self::$_getSeoSearchsCache[$cacheKey] = Db::getInstance()->ExecuteS('
		SELECT *
		FROM `' . _DB_PREFIX_ . self::$definition['table'] . '` aas
		LEFT JOIN `' . _DB_PREFIX_ . 'ads_af_seo_lang` aasl ON (aas.`id_seo` = aasl.`id_seo` AND aasl.`id_lang` = ' . ((int)$id_lang) . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'ads_af_seo_shop` aass ON (aas.`id_seo` = aass.`id_seo` AND aass.`id_shop` = ' . ((int)$id_shop) . ')
		WHERE 1
		AND aass.`active` = 1
		' . (!$withDeleted ? ' AND aass.`deleted` = 0' : '') . '
		GROUP BY aas.`id_seo`
		ORDER BY aas.`id_seo`');
        return self::$_getSeoSearchsCache[$cacheKey];
    }

    public
    function activeSeoByShop($seo)
    {
        foreach (Shop::getShops() as $shop) {
            $supplier = new Supplier((int)$seo->id_supplier, null, (int)$shop["id_shop"]);
            if (!$supplier->active) {
                Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'ads_af_seo_shop` SET `active` = 0 WHERE `id_seo` = ' . (int)$seo->id . ' AND `id_shop` = ' . (int)$shop["id_shop"]);
            }
        }
    }

    public static function displaySeoUrl($id, $id_lang)
    {
        $obj = new AdsAlgoliaSeo((int)$id, (int)$id_lang);
        return Context::getContext()->link->getModuleLink('ads_algolia_front', "adsseolandingagl", array("seo_url" => $obj->seo_url));
    }

    private static $_getCrossLinksOptionsSelectedCache = array();

    public function getCrossLinksOptionsSelected($id_shop, $id_lang)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$_getCrossLinksOptionsSelectedCache[$cacheKey])) return self::$_getCrossLinksOptionsSelectedCache[$cacheKey];
        $result = Db::getInstance()->ExecuteS('
		SELECT aascl.`id_seo_linked`, aasl.`title`
		FROM `' . _DB_PREFIX_ . 'ads_af_seo_crosslinks` aascl
		LEFT JOIN `' . _DB_PREFIX_ . 'ads_af_seo_lang` aasl ON (aascl.`id_seo_linked` = aasl.`id_seo` AND aasl.`id_lang` = ' . ((int)$id_lang) . ' AND aasl.`id_shop` = ' . ((int)$id_shop) . ')
		WHERE aascl.`id_seo` = ' . (int)($this->id));
        $return = array();
        foreach ($result as $row) {
            $return[$row['id_seo_linked']] = $row['title'];
        }
        self::$_getCrossLinksOptionsSelectedCache[$cacheKey] = $return;
        return self::$_getCrossLinksOptionsSelectedCache[$cacheKey];
    }

    private static $_getCrossLinksAvailableCache = array();

    public static function getCrossLinksAvailable($id_shop, $id_lang, $id_excludes = false, $query_search = false, $count = false, $limit = false, $start = 0)
    {
        $cacheKey = sha1(serialize(func_get_args()));
        if (isset(self::$_getCrossLinksAvailableCache[$cacheKey])) return self::$_getCrossLinksAvailableCache[$cacheKey];
        if ($count) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT COUNT(aas.`id_seo`) AS nb
			FROM `' . _DB_PREFIX_ . 'ads_af_seo` aas
			LEFT JOIN `' . _DB_PREFIX_ . 'ads_af_seo_lang` aasl ON (aas.`id_seo` = aasl.`id_seo` AND aasl.`id_lang` = ' . ((int)$id_lang) . ' )
			LEFT JOIN `' . _DB_PREFIX_ . 'ads_af_seo_shop` aass ON (aas.`id_seo` = aass.`id_seo` AND aass.`id_shop` = ' . ((int)$id_shop) . ' )
			WHERE ' . ($id_excludes ? ' aas.`id_seo` NOT IN(' . pSQL(implode(',', $id_excludes)) . ') AND ' : '') . 'aass.`deleted` = 0 AND aass.`active` = 1 AND aasl.`id_shop` = ' . (int)$id_shop . '
			' . ($query_search ? ' AND aasl.`title` LIKE "%' . pSQL($query_search) . '%"' : '') . '
			ORDER BY aas.`id_seo`');
            self::$_getCrossLinksAvailableCache[$cacheKey] = (int)($result['nb']);
            return self::$_getCrossLinksAvailableCache[$cacheKey];
        }
        $result = Db::getInstance()->ExecuteS('
		SELECT aas.`id_seo`, aasl.`title`
		FROM `' . _DB_PREFIX_ . 'ads_af_seo` aas
		LEFT JOIN `' . _DB_PREFIX_ . 'ads_af_seo_lang` aasl ON (aas.`id_seo` = aasl.`id_seo` AND aasl.`id_lang` = ' . ((int)$id_lang) . ' )
		LEFT JOIN `' . _DB_PREFIX_ . 'ads_af_seo_shop` aass ON (aas.`id_seo` = aass.`id_seo` AND aass.`id_shop` = ' . ((int)$id_shop) . ' )
		WHERE ' . ($id_excludes ? ' aas.`id_seo` NOT IN(' . pSQL(implode(',', $id_excludes)) . ') AND ' : '') . 'aass.`deleted` = 0 AND aass.`active` = 1 AND aasl.`id_shop` = ' . (int)$id_shop . '
		' . ($query_search ? ' AND aasl.`title` LIKE "%' . pSQL($query_search) . '%"' : '') . '
		ORDER BY aas.`id_seo`
		' . ($limit ? 'LIMIT ' . $start . ', ' . (int)$limit : ''));
        $return = array();
        foreach ($result as $row) {
            $return[$row['id_seo']] = $row['title'];
        }
        self::$_getCrossLinksAvailableCache[$cacheKey] = $return;
        return self::$_getCrossLinksAvailableCache[$cacheKey];
    }

    public function cleanCrossLinks()
    {
        Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'ads_af_seo_crosslinks` WHERE `id_seo` = ' . intval($this->id) . ' OR `id_seo_linked` = ' . intval($this->id));
    }

    public function saveCrossLinks()
    {
        foreach ($this->cross_links as $id_seo_linked) {
            $row = array('id_seo' => intval($this->id), 'id_seo_linked' => intval($id_seo_linked));
            Db::getInstance()->AutoExecute(_DB_PREFIX_ . 'ads_af_seo_crosslinks', $row, 'INSERT');
        }
    }

}
