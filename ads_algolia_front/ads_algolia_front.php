<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once _PS_MODULE_DIR_ . 'jscomposer/jscomposer.php';
require_once(dirname(__FILE__) . '/classes/SearchAlert.php');
if (file_exists(dirname(__FILE__) . '/classes/AdsAlgoliaSeo.php'))
    require_once(dirname(__FILE__) . '/classes/AdsAlgoliaSeo.php');
if (file_exists(dirname(__FILE__) . '/classes/AdsAlgoliaSeoMassive.php'))
    require_once(dirname(__FILE__) . '/classes/AdsAlgoliaSeoMassive.php');
if (file_exists(dirname(__FILE__) . '/classes/AdsAlgoliaSeoGenerator.php'))
    require_once(dirname(__FILE__) . '/classes/AdsAlgoliaSeoGenerator.php');
require_once _PS_MODULE_DIR_ . 'algolia/classes/AttributesHelper.php';
require_once _PS_MODULE_DIR_ . 'algolia/classes/Registry.php';

if (!defined('_ADSAGL_DIR_'))
    define('_ADSAGL_DIR_', dirname(__FILE__) . '/');

if (!defined('_ADSAGL_TEMPLATES_DIR_'))
    define('_ADSAGL_TEMPLATES_DIR_', _ADSAGL_DIR_ . 'views/templates/');

if (!defined('_ADSAGL_CONTROLLERS_DIR_'))
    define('_ADSAGL_CONTROLLERS_DIR_', _ADSAGL_DIR_ . 'controllers/');

class ads_algolia_front extends Module
{

    const ALGOLIAFRONT_CLASSNAME = 'AdminAlgoliaFront';
    const ALGOLIASEO_CLASSNAME = 'AdminAlgoliaSeo';
    const ALGOLIASEOMASSIVE_CLASSNAME = 'AdminAlgoliaSeoMassive';
    const INSTALL_SQL_BASE_FILE = 'install_base.sql';

    private $html = '';
    public static $_path_to_list = '/recherche';
    public $module_url;

    public function __construct()
    {
        $this->installDB();
        $this->name = 'ads_algolia_front';
        $this->tab = 'front_office_features';
        $this->version = '2.0';
        $this->author = 'Adstrategy';

        $this->bootstrap = true;

        $this->controllers = array('adssearchagl', 'adsseoagl', 'adsseolandingagl', 'adsalertagl');

        parent::__construct();

        JsComposer::add_shortcode('shortcode_ads_algolia_front', array(&$this, 'hookDisplayHome'));

        $this->displayName = $this->l('[ADS] Algolia front search with Reactjs');
        $this->description = $this->l('Affiche un search homepage et crée une page de recherche');

        if (defined('_PS_ADMIN_DIR_'))
            $this->module_url = 'index.php?controller=AdminModules&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');

        require_once(dirname(__FILE__) . '/classes/AdsAlgoliaAction.php');
    }

    public function installDB()
    {
        if (!file_exists(dirname(__FILE__) . '/' . self::INSTALL_SQL_BASE_FILE))
            return (false);
        else if (!$sql = file_get_contents(dirname(__FILE__) . '/' . self::INSTALL_SQL_BASE_FILE))
            return (false);
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        if (version_compare(_PS_VERSION_, '1.4.0.0', '>='))
            $sql = str_replace('MYSQL_ENGINE', _MYSQL_ENGINE_, $sql);
        else
            $sql = str_replace('MYSQL_ENGINE', 'MyISAM', $sql);
        $sql = preg_split("/;\s*[\r\n]+/", $sql);
        foreach ($sql as $query)
            if (!Db::getInstance()->Execute(trim($query)))
                return (false);
        return true;
    }

    public function resetInstall()
    {
        Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ads_af_search_alert`');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ads_af_search_alert_shop`');
    }

    public function install()
    {
        if (!$this->installDB())
            return false;

        if (!Module::isInstalled('algolia') || !Module::isEnabled('algolia')) {
            $this->_errors[] = $this->l('Algolia Module must be installed & activated');
            return false;
        }

        if (!$this->installTab($this->l('Algolia Front Reactjs'), -1, self::ALGOLIAFRONT_CLASSNAME) ||
            !$this->installTab($this->l('Algolia Pages SEO'), -1, self::ALGOLIASEO_CLASSNAME) ||
            !$this->installTab($this->l('Algolia Pages SEO Massive'), -1, self::ALGOLIASEOMASSIVE_CLASSNAME)) {
            $this->_errors[] = $this->l('Could not install module tab');
            return false;
        }

        //SEO DEFAULT
        Configuration::updateValue('PS_ADS_ALGOLIA_FRONT_METATITLE_CATEGORY', '{{shop_name}} recherche pour vous votre voiture {{category}}');
        Configuration::updateValue('PS_ADS_ALGOLIA_FRONT_METATITLE_TYPEDEVEHICULE', '{{shop_name}} recherche pour vous votre voiture {{category}} {{type_de_vehicule}}');
        Configuration::updateValue('PS_ADS_ALGOLIA_FRONT_METATITLE_BRAND', 'Voitures {{brand}} {{category}} - Annonces {{brand}} | {{shop_name}}');
        Configuration::updateValue('PS_ADS_ALGOLIA_FRONT_METATITLE_MODEL', 'Voitures {{brand}} {{model}} {{category}} | {{shop_name}}');

        /* obligatoirement avant le registerhook */
        if (!parent::install())
            return false;

        if (!$this->registerHook('header') OR
            !$this->registerHook('footer') OR
            !$this->registerHook('displayCustomerAccount') OR
            !$this->registerHook('displayMyAccountBlock') OR
            !$this->registerHook('displayHome') OR
            !$this->registerHook('moduleRoutes') OR
            !$this->registerHook('GSitemapAppendUrls') OR
            !$this->registerHook('displayLeftColumn') OR
            !$this->registerHook('actionAdsImportVehicleFinish') OR
            !$this->registerHook('displayBackOfficeHeader') OR
            !$this->registerHook('displayAdminListBefore')
        )
            return false;

        if (Module::isInstalled('jscomposer') && Module::isEnabled('jscomposer')) {
            if (!$this->registerHook('vcBeforeInit'))
                return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!($this->resetInstall()))
            return false;

        if (!$this->uninstallTab($this->l('Algolia Front'), -1, self::ALGOLIAFRONT_CLASSNAME) ||
            !$this->uninstallTab($this->l('Algolia Pages SEO'), -1, self::ALGOLIASEO_CLASSNAME) ||
            !$this->uninstallTab($this->l('Algolia Pages SEO Massive'), -1, self::ALGOLIASEOMASSIVE_CLASSNAME)) {
            $this->_errors[] = $this->l('Could not install module tab');
            return false;
        }

        if (!$this->unregisterHook('header') OR
            !$this->unregisterHook('footer') OR
            !$this->unregisterHook('displayCustomerAccount') OR
            !$this->unregisterHook('displayMyAccountBlock') OR
            !$this->unregisterHook('displayHome') OR
            !$this->unregisterHook('moduleRoutes') OR
            !$this->unregisterHook('displayLeftColumn') OR
            !$this->unregisterHook('GSitemapAppendUrls') OR
            !$this->unregisterHook('displayBackOfficeHeader') OR
            !$this->unregisterHook('displayAdminListBefore') OR
            !$this->unregisterHook('vcBeforeInit'))
            return false;

        return parent::uninstall();
    }

    public function installTab($name, $id_parent, $class_name)
    {
        if (!Tab::getIdFromClassName($class_name)) {
            $module_tab = new Tab;

            foreach (Language::getLanguages(true) as $language)
                $module_tab->name[$language['id_lang']] = $name;

            $module_tab->class_name = $class_name;
            $module_tab->id_parent = $id_parent;
            $module_tab->module = $this->name;
            $module_tab->active = 1;

            if (!$module_tab->save())
                return false;

            return $module_tab->id;
        }

        return true;
    }

    public function uninstallTab($name, $id_parent, $class_name)
    {

        if (!Tab::getIdFromClassName($class_name)) {
            return true;
        } else {
            $module_tab = new Tab(Tab::getIdFromClassName($class_name));

            if (!$module_tab->delete())
                return false;
        }

        return true;
    }

    function getContent()
    {
        $this->displayNavigation();
        $this->displayConfiguration();

        return $this->html;
    }

    private function displayConfiguration()
    {
        require_once(_ADSAGL_CONTROLLERS_DIR_ . 'admin/AdminAlgoliaFrontController.php');

        $controller = new AdminAlgoliaFrontController();

        if (Tools::isSubmit('submitOptionsConfiguration')) {
            $controller->updateOptions();

            if (!empty($controller->errors))
                $this->html .= $this->displayError(implode('<br />', $controller->errors));
            else
                $this->html .= $this->displayConfirmation($this->l('Settings saved successfully'));
        }

        $this->html .= $controller->renderOptions();
    }

    public function hookdisplayAdminListBefore()
    {
        $display = false;
        if (Tools::getValue("configure"))
            if (Tools::getValue("configure") == $this->name)
                $display = true;
        if (Tools::getValue("controller"))
            if (strpos(Tools::getValue("controller"), "AdminAlgolia") != false)
                $display = true;
        if ($display)
            $this->displayNavigation();
        return $this->html;
    }

    public function displayNavigation()
    {
        $this->context->smarty->assign('menutabs', $this->initNavigation());
        $this->html .= $this->context->smarty->fetch(_ADSAGL_TEMPLATES_DIR_ . 'admin/navigation.tpl');
    }

    private function initNavigation()
    {
        $menu_tabs = array(
            'AdminModules' => array(
                'short' => 'Settings',
                'desc' => $this->l('Settings'),
                'href' => $this->module_url,
                'active' => false,
                'imgclass' => 'icon-cogs'
            ),
            'AdminAlgoliaSeo' => array(
                'short' => 'Pages SEO',
                'desc' => $this->l('Pages SEO'),
                'href' => $this->context->link->getAdminLink('AdminAlgoliaSeo'),
                'active' => false,
                'imgclass' => 'icon-filter'
            ),
            'AdminAlgoliaSeoMassive' => array(
                'short' => 'Pages SEO Massive',
                'desc' => $this->l('Pages SEO Massive'),
                'href' => $this->context->link->getAdminLink('AdminAlgoliaSeoMassive'),
                'active' => false,
                'imgclass' => 'icon-filter'
            )
        );

        $available_pages = array_keys($menu_tabs);
        $current_page = Tools::getValue('controller', reset($available_pages));

        if (in_array($current_page, array_keys($menu_tabs)))
            $menu_tabs[$current_page]['active'] = true;

        return $menu_tabs;
    }

    public function hookDisplayCustomerAccount()
    {
        return $this->display(__FILE__, 'my-account.tpl');
    }

    /* Hook for adding URLs to XML Sitemap */
    public function hookGSitemapAppendUrls($params)
    {

        $id_shop = Context::getContext()->shop->id;
        $id_lang = (int)$params["lang"]["id_lang"];
        $products_id = self::getAllProducts($id_shop);

        $links_tmp = array();
        $links = array();

        $dispatcher = Dispatcher::getInstance();

        foreach ($products_id as $product_id) {
            $maker = "";
            $model = "";
            $type_de_vehicule = "";
            $ps_product = new Product((int)$product_id['id_product'], false, $id_lang);
            $category = new \Category($ps_product->id_category_default, $id_lang);

            foreach ($ps_product->getFrontFeatures($id_lang) as $feature) {
                $name = Tools::slugify($feature['name']);
                $value = Tools::slugify($feature['value']);

                if ($name == "marque") {
                    $maker = $value;
                } elseif ($name == "modele") {
                    $model = $value;
                } elseif ($name == "type_de_vehicule") {
                    $type_de_vehicule = $value;
                }
            }

            //SEO CATEGORY VEHICLE
            if ($dispatcher->hasKeyword('product_rule', $id_lang, 'category', $id_shop))
                $links_tmp[] = Context::getContext()->link->getModuleLink($this->name, "adsseoagl", array("category" => Tools::slugify($category->name), "type_de_vehicule" => "", "brand" => "", "model" => ""));

            //SEO TYPE DE VEHICULE VEHICLE
            if ($dispatcher->hasKeyword('product_rule', $id_lang, 'type_de_vehicule', $id_shop))
                if ($type_de_vehicule)
                    $links_tmp[] = Context::getContext()->link->getModuleLink($this->name, "adsseoagl", array("category" => Tools::slugify($category->name), "type_de_vehicule" => Tools::slugify($type_de_vehicule), "brand" => "", "model" => ""));

            //SEO MARQUE VEHICLE
            if ($dispatcher->hasKeyword('product_rule', $id_lang, 'manufacturer', $id_shop))
                if ($maker)
                    $links_tmp[] = Context::getContext()->link->getModuleLink($this->name, "adsseoagl", array("category" => Tools::slugify($category->name), "type_de_vehicule" => Tools::slugify($type_de_vehicule), "brand" => $maker, "model" => ""));

            //SEO MODELE VEHICLE
            if ($dispatcher->hasKeyword('product_rule', $id_lang, 'model', $id_shop))
                if ($model)
                    $links_tmp[] = Context::getContext()->link->getModuleLink($this->name, "adsseoagl", array("category" => Tools::slugify($category->name), "type_de_vehicule" => Tools::slugify($type_de_vehicule), "brand" => $maker, "model" => $model));

        }

        //SEO PAGES
        $seo_pages = AdsAlgoliaSeo::getSeoSearchs($id_shop, $id_lang);
        foreach ($seo_pages as $seo_page) {
            $links_tmp[] = Context::getContext()->link->getModuleLink($this->name, "adsseolandingagl", array("seo_url" => $seo_page["seo_url"]));
        }

        $links_tmp = array_unique($links_tmp);
        asort($links_tmp);
        foreach ($links_tmp as $link_tmp) {
            $link = [];
            $link['link'] = $link_tmp;
            $link['type'] = 'module';
            $links[] = $link;
        }

        return $links;
    }

    public function hookDisplayFooter($params)
    {
        $page_name = Dispatcher::getInstance()->getController();
        if ($page_name == "product")
            return $this->displayProductPath();
        return $this->display(__FILE__, 'autocomplete.tpl');
    }

    public function hookDisplayHome($params)
    {
        $this->context->smarty->assign(array(
            'shop_domain_search_google' => Tools::getHttpHost(true),
            'path_to_list' => self::$_path_to_list,
            'home_search_bg' => Configuration::get('PS_ADS_ALGOLIA_FRONT_BG_IMG'),
            'home_search_title' => Configuration::get('PS_ADS_ALGOLIA_FRONT_H2'),
            'home_search_subtitle' => Configuration::get('PS_ADS_ALGOLIA_FRONT_H3'),
            'mode_ligne_search' => Configuration::get('PS_ADS_ALGOLIA_FRONT_SEARCH_HORIZONTAL'),
            'mode_ligne_search_simple' => Configuration::get('PS_ADS_ALGOLIA_FRONT_SEARCH_HORIZONTAL_SIMPLE'),
            'agl_facets' => $this->getAlgoliaAttributes(false, "show_home")
        ));

        return $this->display(__FILE__, 'home_search.tpl');
    }

    public function hookDisplayLeftColumn($params)
    {

        $this->context->smarty->assign(array(
            'agl_facets' => $this->getAlgoliaAttributes()
        ));

        return $this->display(__FILE__, 'left_column.tpl');
    }

    public function get_static_definitions()
    {
        $agl_definitions_tmp = array(
            'label_vn' => $this->l('Neuve & 0Km'),
            'label_vo' => $this->l('Occasion')
        );
        return array_map('Tools::myTrim', $agl_definitions_tmp);
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue("configure") && Tools::getValue("configure") != $this->name)
            return;
        if (Tools::getValue("controller") && Tools::getValue("controller") != "AdminAlgoliaSeo")
            return;
        return '<script>
				var admin_algolia_ajax_url = \'' . $this->context->link->getAdminLink('AdminAlgoliaSeo') . '\';
				var current_id_tab = ' . (int)$this->context->controller->id . ';
			</script>';

    }

    public function hookDisplayHeader($params)
    {
        $page_name = Dispatcher::getInstance()->getController();
        Media::addJsDef(array('algolia_prefiltered' => $this->getPreFilter()));
        Media::addJsDef(array('home_search_subtitle' => Configuration::get('PS_ADS_ALGOLIA_FRONT_H3')));

        if ($page_name == "index")
            Media::addJsDef(array(
                'agl_facets' => $this->getAlgoliaAttributes(true, "show_home"),
                'agl_definitions' => $this->get_static_definitions()
            ));
        else
            Media::addJsDef(array(
                'agl_facets' => $this->getAlgoliaAttributes(true),
                'agl_definitions' => $this->get_static_definitions()
            ));

        // Gestion copy btn (only connected)
        $cookie = Context::getContext()->cookie;
        if ($cookie->id_employee) {
            $this->context->controller->addJS(_PS_JS_DIR_ . 'lib/clipboard/clipboard.min.js');
            $this->context->controller->addJS($this->_path . 'views/js/admin/scripts.js');
        }

        /* HACK */
        if ($page_name != "index")
            return;
        $this->context->controller->addJS($this->_path . 'views/js/lib/algolia/instantsearch.min.js');
        $this->context->controller->addJS($this->_path . 'views/js/lib/algolia/algoliasearch.min.js');
        $this->context->controller->addJS($this->_path . 'views/js/lib/algolia/autocomplete.jquery.min.js');
        $this->context->controller->addJS($this->_path . 'views/js/lib/jquery/ion.rangeslider/ion.rangeSlider.min.js');
        $this->context->controller->addJS($this->_path . 'views/js/lib/algolia/widgets/instantsearch-ion.rangeSlider.min.js');
        $this->context->controller->addJS($this->_path . 'views/js/src/main/searchpage_index.js');
        $this->context->controller->addJS($this->_path . 'views/js/src/main/init.js');
        //$this->context->controller->addJS($this->_path . 'views/js/build/vendors.adsaf_script.js');
        //$this->context->controller->addJS($this->_path . 'views/js/build/main.adsaf_script.js');

        //$this->context->controller->addCSS($this->_path . 'views/css/lib/ion.rangeSlider-2.2.0/src/ion.rangeSlider.css', 'all');
        //$this->context->controller->addCSS($this->_path . 'views/css/lib/ion.rangeSlider-2.2.0/src/ion.rangeSlider.skinFlat.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/lib/ion.rangeSlider-2.2.0/src/ion.rangeSlider.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/lib/ion.rangeSlider-2.2.0/src/ion.rangeSlider.skinFlat.css', 'all');

        /* recherche ligne */
        if (Configuration::get('PS_ADS_ALGOLIA_FRONT_SEARCH_HORIZONTAL_SIMPLE')) {
            $this->context->controller->addCSS($this->_path . 'views/css/main-ligne-simple.css', 'all');
            Media::addJsDef(array('algolia_search_horizontal_simple' => true));
        }elseif (Configuration::get('PS_ADS_ALGOLIA_FRONT_SEARCH_HORIZONTAL')) {
            $this->context->controller->addCSS($this->_path . 'views/css/main-ligne.css', 'all');
            Media::addJsDef(array('algolia_search_horizontal' => true));
        } else {
            $this->context->controller->addCSS($this->_path . 'views/css/main.css', 'all');
            Media::addJsDef(array('algolia_search_horizontal' => false));
        }
        /* SPRITE DES LOGOS MARQUES */
        $this->context->controller->addCSS($this->_path . 'views/css/logo-marque-home.css', 'all');

        Media::addJsDef(array('path_to_list' => self::$_path_to_list));
    }

    public function getPreFilter()
    {
        $category_vehicle = "";
        $ids_supplier = false;
        if (Tools::getValue("category")) {
            if (Tools::getValue("category") == "occasion") {
                $category_vehicle = " AND is_vo = 1";
                if (Module::isInstalled('ads_mapsuppliers') && Module::isEnabled('ads_mapsuppliers')) {
                    /* STORELOCATOR */
                    $ids_supplier = infoSuppliersMap::getIdSupplierStoreLocator(false, true);
                }
            } else {
                $category_vehicle = " AND is_vo = 0";
                if (Module::isInstalled('ads_mapsuppliers') && Module::isEnabled('ads_mapsuppliers')) {
                    /* STORELOCATOR */
                    $ids_supplier = infoSuppliersMap::getIdSupplierStoreLocator();
                }
            }
        } else if(Module::isInstalled('ads_mapsuppliers') && Module::isEnabled('ads_mapsuppliers')) {
            /* STORELOCATOR */
            $ids_supplier = infoSuppliersMap::getIdSupplierStoreLocator(true);
        }

        /* ENCHERES */
        $id_encheres = "";
        if (Module::isInstalled('ads_encheres') && Module::isEnabled('ads_encheres') && $enchere = Tools::getValue("enchere")) {
            /* on récupère l'id supplier */
            $enchere = explode('-', $enchere);
            $id_enchere = (int)end($enchere);
            if (isset($id_enchere) && \Validate::isInt($id_enchere)) {
                $enchere = new Encheres((int)$id_enchere, Context::getContext()->language->id, Context::getContext()->shop->id);
                $id_encheres = ' AND (supplier.id=' . $enchere->id_supplier . ')';
            }
        }

        /* STORELOCATOR */
        $supplier = "";
        $full_stock = (bool)Tools::getValue('full_stock');
        if ($ids_supplier != false && !$full_stock) {
            $string_ids_supplier = "";
            $string_ids_supplier = implode(',', $ids_supplier);
            $string_ids_supplier = str_replace(',', ' OR supplier.id=', $string_ids_supplier);
            $supplier = ' AND (supplier.id=' . $string_ids_supplier . ')';
        }

        return "shops_association.shop_" . (int)Context::getContext()->shop->id . " = 1" . $category_vehicle . $supplier . $id_encheres;
    }

    public function seoTagsMapping()
    {
        $category = Tools::getValue("category");
        if (Tools::strpos($category, "occasion") !== false)
            $category = "d'occasion";
        $model = Tools::getValue("model");
        return array(
            "shop_name" => Configuration::get('PS_SHOP_NAME'),
            "category" => $category,
            "brand" => $this->getHitsWithSlug("brand", Tools::getValue("brand"), true),
            "model" => $this->getHitsWithSlug("model", Tools::getValue("model"), true),
            "type_de_vehicule" => $this->getHitsWithSlug("type_de_vehicule", Tools::getValue("type_de_vehicule"), true),
            "count" => $this->algoliaCurrentPageCountProducts()
        );
    }

    public function getHitsWithSlug($property, $slug, $only_name = false)
    {
        $index = $this->algoliaSetIndex();
        $res["hits"] = array();
        if ($property == "brand") {
            $value = "marque";
            $attribute = "marque.name";
            $slug = "marque.slug";
        } elseif ($property == "type_de_vehicule") {
            $value = "type_de_vehicule";
            $attribute = "type_de_vehicule.name";
            $slug = "type_de_vehicule.slug";
        } elseif ($property == "model") {
            $value = "modele";
            $attribute = "modele.name";
            $slug = "modele.slug";
        } elseif ($property == "supplier") {
            $value = "supplier";
            $attribute = "supplier.id";
            $slug = "supplier.id";
        }

        if (Tools::getValue($property))
            $res = $index->search('', array(
                'attributesToRetrieve' => array(
                    $attribute
                ),
                'facetFilters' => $slug . ":" . Tools::getValue($property),
                'hitsPerPage' => 1
            ));
        if ($only_name)
            return (count($res["hits"]) > 0 ? $res["hits"][0][$value]["name"] : "");
        return $res["hits"];
    }

    public function seoConstructor($type)
    {
        $value = Configuration::get("PS_ADS_ALGOLIA_FRONT_" . $type . "_CATEGORY");
        if (Tools::getValue("type_de_vehicule"))
            $value = Configuration::get("PS_ADS_ALGOLIA_FRONT_" . $type . "_TYPEDEVEHICULE");
        if (Tools::getValue("brand"))
            $value = Configuration::get("PS_ADS_ALGOLIA_FRONT_" . $type . "_BRAND");
        if (Tools::getValue("model"))
            $value = Configuration::get("PS_ADS_ALGOLIA_FRONT_" . $type . "_MODEL");

        return $this->replace_tags($value, $this->seoTagsMapping());
    }

    function replace_tags($string, $tags, $force_lower = false)
    {
        return preg_replace_callback('/\\{\\{([^{}]+)\}\\}/', function ($matches) use ($force_lower, $tags) {
            $key = $force_lower ? strtolower($matches[1]) : $matches[1];
            return array_key_exists($key, $tags) ? $tags[$key] : '';
        }
            , $string);
    }

    function algoliaSetIndex()
    {
        global $cookie;
        $current_language = \Language::getIsoById($cookie->id_lang);
        $algolia_registry = \Algolia\Core\Registry::getInstance();
        $algolia_client = new \AlgoliaSearch\Client($algolia_registry->app_id, $algolia_registry->admin_key);

        return $algolia_client->initIndex($algolia_registry->index_name . 'all_' . $current_language);
    }

    public function algoliaCurrentPageCountProducts()
    {
        $index = $this->algoliaSetIndex();

        $res = array();
        $res["nbHits"] = "";
        if (Tools::getValue("category"))
            $res = $index->search('', array(
                'attributesToRetrieve' => null,
                'attributesToHighlight' => null,
                'filters' => $this->getPreFilter(),
                'hitsPerPage' => 0
            ));
        if (Tools::getValue("brand")) {
            $res = $index->searchDisjunctiveFaceting(
                "", array("marque.name"), array(
                "facets" => "marque.name",
                "filters" => $this->getPreFilter()
            ), array(
                    "marque.name" => array($this->getHitsWithSlug("brand", Tools::getValue("brand"), true))
                )
            );
        }
        if (Tools::getValue("model")) {
            $res = $index->searchDisjunctiveFaceting(
                "", array("marque.name", "modele.name"), array(
                "facets" => "marque.name, modele.name",
                "filters" => $this->getPreFilter(),
            ), array(
                    "marque.name" => array($this->getHitsWithSlug("brand", Tools::getValue("brand"), true)),
                    "modele.name" => array($this->getHitsWithSlug("model", Tools::getValue("model"), true))
                )
            );
        }
        return $res["nbHits"];
    }

    public function setTagNoIndex()
    {
        if ((Tools::getValue('idx') || Tools::getValue('q'))) {
            header('X-Robots-Tag: noindex, nofollow', true);
            $this->context->smarty->assign(array(
                'nofollow' => true,
                'nobots' => true,
            ));
        }
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function hookModuleRoutes($params)
    {
        unset($params);
        return array(
            'module-ads_algolia_front-adssearchagl' => array(
                'controller' => 'module-ads_algolia_front-adssearchagl',
                'rule' => 'recherche',
                'keywords' => array(
                    'module' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'module'),
                    'controller' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'controller')
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ads_algolia_front',
                    'controller' => 'adssearchagl'
                ),
            ),
            'module-ads_algolia_front-adsseoagl' => array(
                'controller' => 'module-ads_algolia_front-adsseoagl',
                'rule' => $this->l('voiture') . '{/:category}{/:type_de_vehicule}{/:brand}{/:model}', /* HACK */
                'keywords' => array(
                    'category' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'category'),
                    'type_de_vehicule' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'type_de_vehicule'),
                    'brand' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'brand'),
                    'model' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'model')
                ),
                'params' => array(
                    'fc' => 'module'
                )
            ),
            'module-ads_algolia_front-adsseolandingagl' => array(
                'controller' => 'module-ads_algolia_front-adsseolandingagl',
                'rule' => $this->l('voitures') . '{/:seo_url}', /* HACK */
                'keywords' => array(
                    'seo_url' => array('regexp' => '[_a-zA-Z0-9-/\pL]*', 'param' => 'seo_url')
                ),
                'params' => array(
                    'fc' => 'module'
                )
            ),
            'module-ads_algolia_front-adsalertagl' => array(
                'controller' => 'module-ads_algolia_front-adsalertagl',
                'rule' => 'alerte-recherche',
                'keywords' => array(
                    'module' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'module'),
                    'controller' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'controller')
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ads_algolia_front',
                    'controller' => 'adsalertagl'
                ),
            ),
            'module-ads_algolia_front-account' => array(
                'controller' => 'module-ads_algolia_front-account',
                'rule' => 'alerte-recherche/list', /* HACK */
                'keywords' => array(
                    'module' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'module'),
                    'controller' => array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'controller')
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ads_algolia_front',
                    'controller' => 'account'
                ),
            )
        );
    }

    public function displayProductPath()
    {
        $product = new Product((int)(Tools::getValue("id_product")));
        $id_lang = (int)$this->context->language->id;
        $id_shop = Context::getContext()->shop->id;

        $features_product = array();
        foreach ($product->getFrontFeatures($id_lang) as $feature) {
            $name = Tools::slugify($feature['name'], "_");
            $value = $feature['value'];
            $features_product[$name] = $value;
        }

        $category = new Category($product->id_category_default, $id_lang);
        $product->category = $category->name;

        $link = new Link();

        $dispatcher = Dispatcher::getInstance();

        $seo_breadcrumb = array();
        if ($dispatcher->hasKeyword('product_rule', $id_lang, 'category', $id_shop))
            $seo_breadcrumb["category"] = array(
                "label" => $product->category,
                "path" => $link->getModuleLink($this->name, "adsseoagl", array("category" => Tools::link_rewrite($product->category), "type_de_vehicule" => "", "brand" => "", "model" => ""))
            );
        if ($dispatcher->hasKeyword('product_rule', $id_lang, 'type_de_vehicule', $id_shop))
            $seo_breadcrumb["type_de_vehicule"] = array(
                "label" => $features_product["type_de_vehicule"],
                "path" => $link->getModuleLink($this->name, "adsseoagl", array("category" => Tools::link_rewrite($product->category), "type_de_vehicule" => Tools::link_rewrite($features_product["type_de_vehicule"]), "brand" => "", "model" => ""))
            );
        if ($dispatcher->hasKeyword('product_rule', $id_lang, 'manufacturer', $id_shop))
            $seo_breadcrumb["brand"] = array(
                "label" => $features_product["marque"],
                "path" => $link->getModuleLink($this->name, "adsseoagl", array("category" => Tools::link_rewrite($product->category), "type_de_vehicule" => Tools::link_rewrite($features_product["type_de_vehicule"]), "brand" => Tools::link_rewrite($features_product["marque"]), "model" => ""))
            );
        if ($dispatcher->hasKeyword('product_rule', $id_lang, 'model', $id_shop))
            $seo_breadcrumb["model"] = array(
                "label" => $features_product["modele"],
                "path" => $link->getModuleLink($this->name, "adsseoagl", array("category" => Tools::link_rewrite($product->category), "type_de_vehicule" => Tools::link_rewrite($features_product["type_de_vehicule"]), "brand" => Tools::link_rewrite($features_product["marque"]), "model" => Tools::link_rewrite($features_product["modele"])))
            );
        if ($dispatcher->hasKeyword('product_rule', $id_lang, 'version', $id_shop))
            $seo_breadcrumb["version"] = array(
                "label" => $features_product["version"],
                "path" => ""
            );


        $this->context->smarty->assign(array(
                'ads_obj' => $this,
                'ads_af_product' => $product,
                'features_product' => $features_product,
                'seo_breadcrumb' => $seo_breadcrumb,
            )
        );

        return $this->display(__FILE__, 'product_breadcrumb.tpl');
    }

    public function hookvcBeforeInit()
    {
        $vc = vc_manager();
        if (function_exists("vc_map")) {
            // VC elements
            vc_map(array(
                'name' => $vc->l("Recherche Algolia"),
                'base' => 'shortcode_ads_algolia_front',
                'icon' => 'shortcode_ads_algolia_front',
                'category' => 'Modules',
                'params' => array(
                    array(
                        "type" => "dropdown",
                        "heading" => $vc->l("Executed Hook"),
                        "param_name" => "execute_hook",
                        "value" => array('displayHome')
                    ), array(
                        "type" => "vc_hidden_field",
                        "param_name" => "execute_module",
                        "def_value" => 'ads_algolia_front',
                        "value" => 'ads_algolia_front'
                    )
                )
            ));
        }
    }

    public function getAlgoliaTranslation()
    {
        return array(
            'price_tax_incl_asc' => $this->l('Trier par prix croissant'),
            'price_tax_incl_desc' => $this->l('Trier par prix décroissant'),
            'kilometrage_asc' => $this->l('Trier par kilométrage croissant'),
            'kilometrage_desc' => $this->l('Trier par kilométrage décroissant'),
            'date_add_desc' => $this->l('Trier par nouveauté')
        );
    }

    public function getAllProducts($id_shop)
    {
        $products = Db::getInstance()->executeS('
			SELECT `id_product`
			FROM `' . _DB_PREFIX_ . 'product_shop`
			WHERE `active` = 1
				AND `id_shop` = "' . (int)$id_shop . '"
				AND `visibility` IN ("both", "search")'
        );

        return $products ? $products : array();
    }

    public function getAlgoliaAttributes($return_json = false, $display = "show_front")
    {
        $algolia_registry = \Algolia\Core\Registry::getInstance();
        $attribute_helper = new \Algolia\Core\AttributesHelper();
        $attributes = $attribute_helper->getAllAttributes($this->context->language->id);
        $facets = array();
        foreach ($attributes as $key => $value) {
            if (isset($algolia_registry->metas[$key]) && $algolia_registry->metas[$key]['facetable'] && $algolia_registry->metas[$key][$display])
                $facets[] = array(
                    'id' => Tools::link_rewrite($value->name),
                    'tax' => $value->name,
                    'name' => $value->label,
                    'icon' => $value->icon,
                    'order' => (int)$value->order,
                    'type' => $value->facet_type,
                    'collapsed' => $value->collapsed,
                    'show_front' => $value->show_front,
                    'hide_css_front' => $value->hide_css_front,
                    'css_class' => $value->css_class,
                );
        }
        $order = array_column($facets, 'order');
        array_multisort($order, SORT_ASC, $facets);
        return ($return_json ? json_encode($facets) : $facets);
    }

    public function hookActionAdsImportVehicleFinish($params)
    {
        $prestashop_fetcher = \Algolia\Core\PrestashopFetcher::getInstance();
        $id_lang = $this->context->language->id;
        $seos_to_create = array();
        //die(var_dump());
        foreach (Shop::getShops() as $shop) {
            Shop::setContext(Shop::CONTEXT_SHOP, (int)$shop["id_shop"]);
            $products = $this->getAllProducts((int)$shop["id_shop"]);
            foreach ($products as $product) {
                foreach (Language::getLanguages() as $language) {
                    $object = $prestashop_fetcher->getProductObj((int)$product['id_product'], $language);
                    $object = json_decode(json_encode($object));
                    $object->ps_shop_name = Configuration::get('PS_SHOP_NAME');
                    $massive_seos = AdsAlgoliaSeoMassive::getAllSeoMassive((int)$shop["id_shop"], $id_lang);
                    //Loop SEO Massive
                    foreach ($massive_seos as $massive_seo) {
                        $seo_generator = new AdsAlgoliaSeoGenerator();
                        $seo_generator->product_object = $object;
                        $seo_generator->massive_seo = $massive_seo;
                        $seos_to_create[] = $seo_generator->generateSeoPage();
                    }
                }
            }
            $res_all_seos = array();
            foreach ($seos_to_create as $seo_to_create) {
                if (is_array($seo_to_create))
                    foreach ($seo_to_create as $key => $item) {
                        $res_all_seos[$key] = $item;
                    }
            }
            $obj = new AdsAlgoliaSeo(null, $id_lang, (int)$shop["id_shop"]);
            foreach ($res_all_seos as $seo_elements) {
                foreach ($seo_elements as $seo_key => $seo_value) {
                    $obj->{$seo_key} = $seo_value;
                    $obj->date_add = date('Y-m-d H:i:s');
                }
                $obj->processSeoCreate();
            }
        }
    }
}