<?php

class AdminAlgoliaSeoMassiveController extends ModuleAdminController
{

    public function __construct()
    {

        $this->table = 'ads_af_seo_massive';
        $this->className = 'AdsAlgoliaSeoMassive';
        $this->identifier = 'id_seo_massive';
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

        $this->_select = $alias . '.`active`';
        $this->_use_found_rows = false;
        $this->fields_list = array(
            $this->identifier => array(
                'title' => $this->l('Id'),
                'class' => 'fixed-width-sm'
            ),
            'criteria' => array(
                'title' => $this->l('Criteria'),
                'class' => 'fixed-width-sm'
            ),
            'title' => array(
                'title' => $this->l('Title'),
                'class' => 'fixed-width-sm'
            ),
            'meta_title' => array(
                'title' => $this->l('Meta Title'),
                'class' => 'fixed-width-sm'
            ),
            'meta_description' => array(
                'title' => $this->l('Meta Description'),
                'class' => 'fixed-width-sm'
            ),
            'active' => array(
                'title' => $this->l('Active ?'),
                'type' => 'bool',
                'active' => 'active',
                'ajax' => true,
                'align' => 'center',
                'class' => 'fixed-width-sm'
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'filter_key' => 'sa!position',
                'align' => 'center',
                'position' => 'sa.position',
                'class' => 'fixed-width-sm',
                'search' => false,
                'orderby' => true
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

        $this->orderWay = "ASC";
        $this->orderBy = "sa.position";
        $this->_defaultOrderBy = "position";
        $this->position_identifier = $this->identifier;

        $this->toolbar_btn['new_map'] = array(
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->l('Rajouter'),
            'icon' => 'process-icon-new'
        );

        return parent::renderList();
    }

    public function displayAjaxActiveAdsAfSeoMassive()
    {
        $id = (int)Tools::getValue($this->identifier);
        $obj = new $this->className((int)$id);

        if (!Validate::isLoadedObject($obj)) {
            echo Tools::jsonEncode(array("success" => "0", "text" => $this->l('Mise à jour impossible, l\'objet n\'existe pas en base')));
            exit;
        }

        $obj->active = !$obj->active;
        try {
            $obj->update();
        } catch (Exception $exc) {
            echo Tools::jsonEncode(array("success" => "0", "text" => $this->l('Erreur dans le paramétrage du champ')));
            exit;
        }
        echo Tools::jsonEncode(array("success" => "1", "text" => $this->l('Status "actif" mis à jour')));
    }

    public function displayAjaxUpdatePositions()
    {
        $this->loadObject(true)->positionUpdate(Tools::getValue("seo_massive"));
        die();
    }

    public function setMedia()
    {

        $this->context->controller->addCSS($this->module->getPath() . 'views/css/admin/styles.css');
        $this->context->controller->addJqueryUI('ui.widget');
        $this->context->controller->addJqueryPlugin('tagify');

        $this->context->controller->addJS($this->module->getPath() . 'views/js/admin/scripts-bo.js');

        Media::addJsDef(array(
            "adsseolandingagl" => Context::getContext()->link->getModuleLink($this->module->name, "adsseolandingagl", array("seo_url" => ""))
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
                    'lang' => true,
                    'required' => true,
                    'desc' => $this->l('Coller ici les critères désirés, chaque critère sera automatiquement séparé par un / pour construire l\'URL SEO'),
                    'class' => 'tagify delim-slash'
                ),
                array(
                    'type' => 'html',
                    'name' => '<div class="card">
                              <div class="card-header">
                                ' . $this->l('Critères disponibles') . '
                              </div>
                              <div class="card-body">
                                <h5 class="card-title"></h5>
                                <p class="card-text selected-criterias">' . $this->getAllAvailableAttributes() . '</p>
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
                    'class' => 'tagify',
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
                )
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
            'token' => Tools::getAdminTokenLite('AdminAlgoliaSeoMassiveController'),
            'agl_ajax_url' => $this->context->link->getAdminLink('AdminModules')
        ));

        $this->context->controller->redirect_after = true;

        return parent::renderForm();
    }

    private function initOptionFields()
    {
        $this->fields_options = array();
    }

    public function getAllAvailableAttributes()
    {
        $html = "";
        $attributes = Module::getInstanceByName('ads_algolia_front')->getAlgoliaAttributes();
        foreach ($attributes as $attribute)
            $html .= '<div class="col-xs-12 col-lg-3"><p class="h4">' . $attribute["name"] . '</p><div class="short-code"><input title="Cliquer pour copier" class="agl-short-code" type="text" value="{'. $attribute["tax"] .'}"><span class="text-copy">Copié</span></div></div>';
        $html .= '<div class="col-xs-12 col-lg-3"><p class="h4">Nom du site</p><div class="short-code"><input title="Cliquer pour copier" class="agl-short-code" type="text" value="{ps_shop_name}"><span class="text-copy">Copié</span></div></div>';
        return $html;
    }
}
