<?php

class AdminAlgoliaSeoController extends ModuleAdminController
{

    public function __construct()
    {

        $this->table = 'ads_af_seo';
        $this->className = 'AdsAlgoliaSeo';
        $this->identifier = 'id_seo';
        $this->lang = true;
        $this->bootstrap = true;

        parent::__construct();

        //$this->addRowAction('add');
        $this->addRowAction('edit');
        $this->addRowAction('address');
        $this->addRowAction('delete');

        $this->_pagination = array("20", "50", "100", "200", "500");
        $this->_default_pagination = 20;

        $alias = 'sa';
        $id_shop = Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP ? (int)$this->context->shop->id : (int)Configuration::get('PS_SHOP_DEFAULT');
        $this->_join .= ' JOIN `' . _DB_PREFIX_ . $this->table . '_shop` sa ON (a.`' . $this->identifier . '` = sa.`' . $this->identifier . '` AND sa.`id_shop` = ' . $id_shop . ' AND b.`id_shop` = ' . $id_shop . ')';

        $this->_select = $alias . '.`auto`, '. $alias . '.`active`';
        $this->_use_found_rows = false;
        $this->fields_list = array(
            'id_seo' => array(
                'title' => $this->l('Id'),
                'class' => 'fixed-width-sm',
            ),
            'criteria' => array(
                'title' => $this->l('Criteria'),
                'callback' => 'getHumanCriteria'
            ),
            'title' => array(
                'title' => $this->l('Title'),
                'class' => 'fixed-width-sm',
            ),
            'meta_title' => array(
                'title' => $this->l('Meta Title'),
                'class' => 'fixed-width-sm',
            ),
            'meta_description' => array(
                'title' => $this->l('Meta Description'),
                'class' => 'fixed-width-sm',
            ),
            'seo_url' => array(
                'title' => $this->l('SEO URL'),
                'callback' => 'getRealSeoUrl'
            ),
            'active' => array(
                'title' => $this->l('Active ?'),
                'type' => 'bool',
                'active' => 'active',
                'ajax' => true,
                'align' => 'center',
                'class' => 'fixed-width-sm'
            ),
            'auto' => array(
                'title' => $this->l('Auto ?'),
                'type' => 'bool',
                'active' => 'auto',
                'ajax' => true,
                'align' => 'center',
                'class' => 'fixed-width-sm',
                'filter_key' => 'sa!auto'
            )

        );

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Supprimer la sélection'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Êtes-vous sûr de vouloir supprimer ?')
            )
        );

        self::$currentIndex = 'index.php?controller=AdminModules&configure=' . $this->module->name;

        $this->initOptionFields();
    }

    public function initContent()
    {
        $this->content .= $this->module->displayNavigation();
        parent::initContent();
    }

    public function getShopName($id)
    {
        $id_shop = (string)$id;
        $ids_shops = explode(",", $id_shop);
        $return = array();
        foreach ($ids_shops as $id_shop) {
            $shop = Shop::getShop($id_shop);
            $return[] = $shop["name"];
        }
        return implode(", ", $return);
    }

    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_map'] = array(
                'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                'desc' => $this->l('Ajouter', null, null, false),
                'icon' => 'process-icon-new'
            );
        }

        parent::initPageHeaderToolbar();
    }

    public function renderList()
    {

        //Add table to shop table
        Shop::addTableAssociation($this->table, array('type' => 'shop'));

        $this->addJqueryUI('ui.sortable');

        $this->orderWay = "DESC";
        $this->orderBy = $this->identifier;
        $this->_defaultOrderBy = $this->identifier;
        $this->position_identifier = $this->identifier;

        $this->toolbar_btn['new_map'] = array(
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->l('Rajouter'),
            'icon' => 'process-icon-new'
        );

        return parent::renderList();
    }

    public function displayAjaxActiveAdsAfSeo()
    {
        $id = (int)Tools::getValue($this->identifier);
        $obj = new $this->className((int)$id);

        if (!Validate::isLoadedObject($obj)) {
            echo Tools::jsonEncode(array("success" => "0", "text" => $this->l('Mise à jour impossible, l\'objet n\'existe pas en base')));
            exit;
        }

        $obj->active = !$obj->active;
        try {
            $obj->update(true);
        } catch (Exception $exc) {
            echo Tools::jsonEncode(array("success" => "0", "text" => $this->l('Erreur dans le paramétrage du champ')));
            exit;
        }
        echo Tools::jsonEncode(array("success" => "1", "text" => $this->l('Status "actif" mis à jour')));
    }

    public function displayAjaxAutoAdsAfSeo()
    {
        $id = (int)Tools::getValue($this->identifier);
        $obj = new $this->className((int)$id);

        if (!Validate::isLoadedObject($obj)) {
            echo Tools::jsonEncode(array("success" => "0", "text" => $this->l('Mise à jour impossible, l\'objet n\'existe pas en base')));
            exit;
        }

        $obj->auto = !$obj->auto;
        try {
            $obj->update(true);
        } catch (Exception $exc) {
            echo Tools::jsonEncode(array("success" => "0", "text" => $this->l('Erreur dans le paramétrage du champ')));
            exit;
        }
        echo Tools::jsonEncode(array("success" => "1", "text" => $this->l('Status "auto" mis à jour')));
    }


    public function displayAjaxUpdatePositions()
    {
        $this->loadObject(true)->positionUpdate(Tools::getValue('ads_button_action'));
        die();
    }

    public function setMedia()
    {
        $this->context->controller->addCSS($this->module->getPath() . 'views/js/admin/multiselect/ui.multiselect.css');
        $this->context->controller->addCSS($this->module->getPath() . 'views/css/admin/styles.css');
        $this->context->controller->addJquery();
        $this->context->controller->addJqueryUI('ui.widget');
        $this->context->controller->addJqueryPlugin('tagify');
        $this->context->controller->addJS($this->module->getPath() . 'views/js/admin/multiselect/jquery.tmpl.1.1.1.js');
        $this->context->controller->addJS($this->module->getPath() . 'views/js/admin/multiselect/ui.multiselect.js');
        $this->context->controller->addJS($this->module->getPath() . 'views/js/admin/scripts-bo.js');

        $root_directory = PS_ADMIN_DIR;

        Media::addJsDef(array(
            "adsseolandingagl" => Context::getContext()->link->getModuleLink($this->module->name, "adsseolandingagl", array("seo_url" => "")),
            "admin_agf_ajax_url" => __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_) . DIRECTORY_SEPARATOR . $this->context->link->getAdminLink('AdminAlgoliaSeo')
        ));

        parent::setMedia();
    }

    public function renderForm()
    {
        if (!($obj = $this->loadObject(true))) {
            return;
        }
        $this->object = new $this->className();

        if ((int)Tools::getValue($this->identifier)) {
            $this->object = new $this->className((int)Tools::getValue($this->identifier));
            $this->identifier = $this->identifier;
            $id_shop = isset($this->object->id_shop) ? explode(",", $this->object->id_shop) : array();
        } else {
            $id_shop = array(Context::getContext()->shop->id);
        }

        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('Paramètres'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Title'),
                    'name' => 'title',
                    'required' => false,
                    'lang' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('SEO URL'),
                    'name' => 'seo_url',
                    'required' => false,
                    'lang' => true,
                    'class' => 'tagify delim-slash seo-page-url',
                    'desc' => '<span class="urlPageSeo"></span>'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Meta title'),
                    'name' => 'meta_title',
                    'required' => false,
                    'lang' => true
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Meta description'),
                    'name' => 'meta_description',
                    'required' => false,
                    'lang' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Meta keywords'),
                    'name' => 'meta_keywords',
                    'required' => false,
                    'lang' => true,
                    'class' => 'tagify'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Criteria'),
                    'name' => 'criteria',
                    'required' => true,
                    'class' => 'fixed-width-lg',
                    'suffix' => $this->l('Paste here') . '&nbsp;<i class="icon-paste"></i>'
                ),
                array(
                    'type' => 'html',
                    'name' => '<div class="alert alert-info">
                              ' . $this->l('Go to the search page and copy the desired search :') . '
                              <br/><br/><p><a target="_blank" href="' . Context::getContext()->link->getModuleLink($this->module->name, "adssearchagl", array()) . '" class="btn btn-primary">' . $this->l('Page de recherche') . '&nbsp;<i class="icon-search-plus"></i></a></p>
                              </div>',
                ),
                array(
                    'type' => 'html',
                    'name' => '<div class="card">
                              <div class="card-header">
                                ' . $this->l('Active filters') . '
                              </div>
                              <div class="card-body">
                                <h5 class="card-title"></h5>
                                <p class="card-text selected-filters">' . $this->getHumanCriteria($this->object->criteria) . '</p>
                              </div>
                              <div class="card-footer text-muted"></div>
                            </div>'
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Description top'),
                    'name' => 'description_top',
                    'required' => false,
                    'lang' => true,
                    'class' => 'rte',
                    'cols' => 60,
                    'rows' => 20,
                    'autoload_rte' => true,
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Description footer'),
                    'name' => 'description_footer',
                    'required' => false,
                    'lang' => true,
                    'class' => 'tagify',
                    'cols' => 60,
                    'rows' => 20,
                    'autoload_rte' => true,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Auto ?'),
                    'name' => 'auto',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Oui')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Non')
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Actif ?'),
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Oui')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Non')
                        )
                    ),
                ),
                array(
                    'type' => 'multiselect',
                    'label' => $this->l('Pages SEO liées'),
                    'name' => 'cross_links',
                    'selected_options' => $obj->getCrossLinksOptionsSelected(Context::getContext()->shop->id, Context::getContext()->language->id),
                    'id_seo_origin' => $this->object->id
                ),
            ),
            'buttons' => array(
                array(
                    'href' => self::$currentIndex,
                    'icon' => 'process-icon-back',
                    'class' => 'pull-right',
                    'title' => $this->l('Retour à la liste')
                )
            ),
            'submit' => array(
                'title' => $this->l('Sauvegarder')
            )
        );

        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association:'),
                'name' => 'checkBoxShopAsso',
            );
        }

        $this->context->smarty->assign(array(
            'token' => Tools::getAdminTokenLite('AdminAlgoliaSeoController'),
            'agl_ajax_url' => $this->context->link->getAdminLink('AdminModules')
        ));

        $this->context->controller->redirect_after = true;

        return parent::renderForm();
    }

    private function initOptionFields()
    {
        $this->fields_options = array();
    }

    public function getCodeSupplierUsingIdSupplier($id)
    {
        $supplier = new Supplier((int)$id);

        if (!Validate::isLoadedObject($supplier))
            return $id;
        else
            return $supplier->code_supplier;
    }

    public function getAllSupplier()
    {
        $results = Db::getInstance()->executeS('
                SELECT s.*
                FROM `' . _DB_PREFIX_ . 'supplier` s');

        $html = array();
        foreach ($results as $supplier) {
            $html[] = array('id' => $supplier['id_supplier'], 'name' => $supplier['code_supplier']);
        }
        return $html;
    }

    public function getRealSeoUrl($seo_url)
    {
        return Context::getContext()->link->getModuleLink($this->module->name, "adsseolandingagl", array("seo_url" => $seo_url));
    }

    public function getHumanCriteria($criteria)
    {
        $attributes = Module::getInstanceByName('ads_algolia_front')->getAlgoliaAttributes();
        $mapping_attributes = array();
        foreach ($attributes as $attribute)
            $mapping_attributes[$attribute["tax"]] = $attribute["name"];
        $criterias_html = "";
        $criterias_unserialize = unserialize(base64_decode($criteria));
        if (!$criteria)
            return;
        foreach ($criterias_unserialize as $key => $criterias) {
            foreach ($criterias as $criteria_name => $criterias_value) {
                //is_vo
                if ($criteria_name == "is_vo") {
                    $criteria_name = "";
                    $tmp_array = array();
                    if ($criterias_value[0] == false)
                        $tmp_array[] = $this->l('Neuf');
                    else
                        $tmp_array[] = $this->l('Occasion');
                    $criterias_value = $tmp_array;
                }
                if ($criteria_name) {
                    $criterias_html .= "<strong>" . (isset($mapping_attributes[$criteria_name]) ? $mapping_attributes[$criteria_name] : $criteria_name) . "</strong>";
                }
                $criterias_html .= "<br/>";
                if (is_array($criterias_value))
                    foreach ($criterias_value as $criteria_value) {
                        $criterias_html .= "<span class=\"badge badge-secondary\">" . $criteria_value . "</span>";
                    }
                if (is_object($criterias_value)) {
                    foreach ($criterias_value as $key_crit => $value_crit) {
                        $criterias_html .= "<span class=\"badge badge-secondary\">" . $key_crit . " " . $value_crit[0] . "</span>";
                    }
                }
                $criterias_html .= "<br/>";
            }
        }
        return $criterias_html;
    }

    public function ajaxProcessGetHumanCriteria()
    {
        $result = $this->getHumanCriteria(Tools::getValue("value"));
        die($result);
    }

    public function displayAddressLink($token = null, $id)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_address.tpl');
        if (!array_key_exists('PageSeo', self::$cache_lang))
            self::$cache_lang['PageSeo'] = $this->l('Show address URL');

        $tpl->assign(array(
            'href' => AdsAlgoliaSeo::displaySeoUrl((int)$id, (int)Context::getContext()->language->id),
            'action' => self::$cache_lang['PageSeo'],
            'id' => (int)$id,
            'token' => $token
        ));

        return $tpl->fetch();
    }

    public function ajaxProcessDisplaySeoSearchOptions()
    {
        /*
        if (Tools::getValue('id_seo_excludes'))
            $id_seo_excludes = explode(',', Tools::getValue('id_seo_excludes', false));
        else
            $id_seo_excludes = array();
        */
        if (Tools::getValue('id_seo_origin'))
            $id_seo_excludes[] = (int)Tools::getValue('id_seo_origin');
        $query_search = Tools::getValue('q', false);
        $limit = Tools::getValue('limit', 100);
        $start = Tools::getValue('start', 0);
        $nbResults = AdsAlgoliaSeo::getCrossLinksAvailable(Context::getContext()->shop->id, (int)Context::getContext()->language->id, $id_seo_excludes, $query_search, true);
        $results = AdsAlgoliaSeo::getCrossLinksAvailable(Context::getContext()->shop->id, (int)Context::getContext()->language->id, $id_seo_excludes, $query_search, false, $limit, $start);
        $return = "";
        foreach ($results as $key => $value) {
            $return .= $key . '=' . $value . "\n";
        }
        if ($nbResults > ($start + $limit))
            $return .= 'DisplayMore' . "\n";
        die($return);
    }
}
