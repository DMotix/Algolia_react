<?php
/**
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
    exit;

require_once(dirname(__FILE__).'/controllers/front/FrontAlgolia.php');
require_once(dirname(__FILE__).'/classes/AlgoliaHelper.php');
require_once(dirname(__FILE__).'/classes/AttributesHelper.php');
require_once(dirname(__FILE__).'/classes/Registry.php');
require_once(dirname(__FILE__).'/classes/ThemeHelper.php');
require_once(dirname(__FILE__).'/classes/Indexer.php');
require_once(dirname(__FILE__).'/classes/PrestashopFetcher.php');
require_once(dirname(__FILE__).'/libraries/algolia/algoliasearch.php');


class Algolia extends Module
{
    private $front_controller;
    public $batch_count = 200;

    public function __construct() {
        $this->version = '1.1';
        $this->name = 'algolia';
        $this->author = 'dh42';
        $this->tab = 'front_office_features';

        $this->bootstrap = true;

        $this->controllers = array('searchlanding');

        parent::__construct();

        $this->displayName = $this->l('Algolia Search');
        $this->description = $this->l('Speed up your PrestaShop store with Algolia search');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->init();

        \AlgoliaSearch\Version::$custom_value = ' Prestashop';

        $this->front_controller = new FrontAlgoliaController($this);

        if (!defined('GSITEMAP_FILE'))
            define('GSITEMAP_FILE', dirname(__FILE__).'/../../sitemap.xml');
    }

    public function getPath() {
        return $this->_path;
    }

    public function getContext() {
        return $this->context;
    }

    public function install() {
        return parent::install() &&
        $this->registerHook('displayHeader') &&
        $this->registerHook('displayBackOfficeHeader') &&
        $this->registerHook('actionCronJob') &&
        $this->registerHook('actionProductAdd') &&
        $this->registerHook('actionProductUpdate') &&
        $this->registerHook('actionProductDelete') &&
        $this->registerHook('actionSearch') &&
        $this->registerHook('displayBackOfficeCategory') &&
        $this->registerHook('actionProductListOverride') &&
        $this->registerHook('moduleRoutes') &&
        $this->registerHook('assignProductList') &&
        $this->addAdminTab() &&
        $this->changeCategoryDBTable('add') &&
        $this->addSearchLandingTable();
    }

    public function uninstall() {
        Configuration::deleteByName('ALGOLIA_POSITION_FIXED');
        Module::enableByName('blocksearch');

        $this->removeAdminTab();
        $this->changeCategoryDBTable('remove');
        \Algolia\Core\Registry::getInstance()->remove_config();

        return parent::uninstall();
    }

    public function changeCategoryDBTable($type) {

        if($type == 'add')
            return Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'category ADD `facets` TEXT NOT NULL');
        else
            return Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'category DROP COLUMN `facets`');

    }

    public function addSearchLandingTable() {
        return (Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'searchterm (
            `id_searchterm` int(10) NOT NULL AUTO_INCREMENT,
            `term` varchar(255) NOT NULL,
            `url` varchar(255) NOT NULL,
            `title` varchar(255) NOT NULL,
            `description` varchar(255) NOT NULL,
            `keywords` varchar(255) NOT NULL,
            PRIMARY KEY(`id_searchterm`))
            ENGINE='._MYSQL_ENGINE_.' default CHARSET=utf8')
        );
    }

    public function addAdminTab() {
        $tab = new Tab();

        foreach(Language::getLanguages(false) as $lang)
            $tab->name[(int) $lang['id_lang']] = 'Algolia Search';

        $tab->class_name = 'AdminAlgolia';
        $tab->module = $this->name;
        $tab->id_parent = 0;

        if (!$tab->save())
            return false;

        return true;
    }

    public function removeAdminTab() {
        $classNames = array('AdminAlgolia');
        $return = true;

        foreach ($classNames as $className)
        {
            $tab = new Tab(Tab::getIdFromClassName($className));
            $return &= $tab->delete();
        }

        return $return;
    }

    private function _getSearchTerm($id_searchterm) {
        if (!(int)($id_searchterm))
            return false;
        return Db::getInstance()->getRow('
            SELECT *
            FROM `'._DB_PREFIX_.'searchterm` s
            WHERE `id_searchterm` = '.(int)($id_searchterm)
            );
    }


    public function _getSearchTermByUrl($url) {
        return Db::getInstance()->getRow('
            SELECT *
            FROM `'._DB_PREFIX_.'searchterm` s
            WHERE `url` = "'.($url).'"'
            );
    }

    private function _isSearchTermExists($id_searchterm) {
        if (!(int)($id_searchterm))
            return false;
        return (bool)Db::getInstance()->getValue('
            SELECT COUNT(*)
            FROM `'._DB_PREFIX_.'searchterm`
            WHERE `id_searchterm` = '.(int)($id_searchterm)
            );
    }

    /**
     * HOOKS
     */

    public function hookActionProductListOverride($params) {
        $this->front_controller->hookActionProductListOverride($params);
    }

    public function hookCustomProductUpdate($product) {
        $this->front_controller->hookCustomProductUpdate($product);
    }

    public function hookActionSearch($params) {
        $this->front_controller->hookActionSearch($params);
    }

    public function hookActionProductAdd($params) {
        $this->front_controller->hookActionProductAdd($params);
    }

    public function hookActionProductUpdate($params) {
        $this->front_controller->hookActionProductUpdate($params);
    }

    public function hookActionProductDelete($params) {
        $this->front_controller->hookActionProductDelete($params);
    }

    public function hookDisplayBackOfficeHeader() {
        $this->front_controller->hookDisplayBackOfficeHeader();
    }

    public function hookDisplayHeader() {
        $this->front_controller->hookDisplayHeader();
    }

    protected function init() {
        /* If the module is not active */
        if (Module::isEnabled($this->name) === false)
            return false;

        /* Add a default warning message if cURL extension is not available */
        if (function_exists('curl_init') == false)
            $this->warning = $this->l('To be able to use this module, please activate cURL (PHP extension).');
    }

    /* Run cron tasks */
    public function hookActionCronJob() {
    }

    /* Return cron job execution frequency */
    public function getCronFrequency() {
        return array(
            'hour' => -1,
            'day' => -1,
            'month' => -1,
            'day_of_week' => -1
            );
    }

    public function getCategoryFacets($id_category) {
        $category_facets = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT facets
            FROM '._DB_PREFIX_.'category
            WHERE id_category = ' .(int)$id_category);

        if($category_facets)
            return unserialize($category_facets);
        else return '';

    }

    public function hookDisplayBackOfficeCategory() {
        $algolia_registry = \Algolia\Core\Registry::getInstance();
        $attribute_helper = new \Algolia\Core\AttributesHelper();
        $attributes = $attribute_helper->getAllAttributes($this->context->language->id);
        foreach ($attributes as $key => $value)
        {
            if (isset($algolia_registry->metas[$key]) && $algolia_registry->metas[$key]['facetable'])
                $facets[] = array('tax' => $value->name, 'name' => $value->name, 'order1' => $value->order, 'order2' => 0, 'type' => $value->facet_type);
        }


        $saved_facets = $this->getCategoryFacets(Tools::getValue('id_category'));
        if($saved_facets)
        {
            foreach ($saved_facets as $sfacet)
            {
                foreach ($facets as $key => $facet)
                {
                    if($sfacet == $facet['name'])
                        $facets[$key]['active'] = 1;
                }

            } // end foreach saved facets

        } else { // by default, have them all active
            foreach ($facets as $key => $facet)
            {
                $facets[$key]['active'] = 1;
            }
        }

        $this->context->smarty->assign(array(
            'facets'=> $facets
            ));

        return $this->display(__FILE__, 'backOfficeCategory.tpl');


    }

    public function hookModuleRoutes($params) {
        return array(
            'module-algolia-searchlanding' => array(
                'controller' => 'module-algolia-searchlanding',
                'rule' => \Algolia\Core\Registry::getInstance()->search_prefix.'/{searchterm}',
                'keywords' => array(
                    'searchterm' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'searchterm'),
                    ),
                'params' => array(
                    'fc' => 'module',
                    ),
                )
            );
    }


}
