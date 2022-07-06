<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminActionBarController
 *
 * @author Quentin
 */
class AdminAlgoliaFrontController extends ModuleAdminController {
    private $html;

    public function __construct() {
        $this->meta_title = $this->l('Paramètres');
        $this->bootstrap = true;

        parent::__construct();

        $this->token = Tools::getAdminTokenLite('AdminModules');
        $this->table = 'Configuration';
        $this->class = 'Configuration';

        self::$currentIndex = 'index.php?controller=AdminModules&configure=' . $this->module->name;
        
        $this->initOptionFields();
    }

    public function updateOptions() {
        $this->processUpdateOptions();
    }

    private function initOptionFields() {
        $this->fields_options = array(
            'main_settings' => array(
                'title' => $this->l('Paramètres'),
                'class' => "col-xs-12",
                'tabs' => array(
                    'general' => $this->l('Général'),
                    'home' => $this->l('Homepage'),
                    'listing' => $this->l('Listing véhicules'),
                    'seo' => $this->l('SEO'),
                    'pub' => $this->l('Pub')
                ),
                'fields' => array(
                    'PS_ADS_ALGOLIA_FRONT_SEARCH_HORIZONTAL' => array(
                        'type' => 'bool',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'title' => $this->l("Affichage mode ligne de la recherche"),
                        'tab' => 'general',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_SEARCH_HORIZONTAL_SIMPLE' => array(
                        'type' => 'bool',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'title' => $this->l("Affichage mode ligne simplifié de la recherche"),
                        'tab' => 'general',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_BG_IMG' => array(
                        'type' => 'text',
                        'title' => $this->l("URL image background"),
                        'tab' => 'general',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_H2' => array(
                        'type' => 'text',
                        'title' => $this->l("Titre"),
                        'tab' => 'home',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_H3' => array(
                        'type' => 'text',
                        'title' => $this->l("Sous-titre"),
                        'tab' => 'home',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_HOME_SMALLBTN' => array(
                        'type' => 'bool',
                        'title' => $this->l("Bouton recherche small"),
                        'tab' => 'home',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_META_TITLE' => array(
                        'type' => 'text',
                        'title' => $this->l("META Title"),
                        'tab' => 'listing',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_META_DESC' => array(
                        'type' => 'text',
                        'title' => $this->l("META Description"),
                        'tab' => 'listing',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_META_KEYWORD' => array(
                        'type' => 'text',
                        'title' => $this->l("META Keyword"),
                        'class' => "tagify_keyword",
                        'tab' => 'listing',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_TITLE' => array(
                        'type' => 'text',
                        'title' => $this->l("Titre H1"),
                        'tab' => 'listing',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_FILS_ARIANE' => array(
                        'type' => 'text',
                        'title' => $this->l("Fils d'ariane"),
                        'desc' => "Si non renseigné, le Titre H1 est utilisé à la place",
                        'tab' => 'listing',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_METATITLE_CATEGORY' => array(
                        'type' => 'text',
                        'title' => $this->l("Page catégorie"),
                        'desc' => 'Variable(s) disponible(s) : {{shop_name}} {{category}}',
                        'tab' => 'seo',
                        'sub_title' => strtoupper('Meta Title'),
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_METATITLE_TYPEDEVEHICULE' => array(
                        'type' => 'text',
                        'title' => $this->l("Page type de véhicule"),
                        'desc' => 'Variable(s) disponible(s) : {{shop_name}} {{category}} {{type_de_vehicule}}',
                        'tab' => 'seo',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_METATITLE_BRAND' => array(
                        'type' => 'text',
                        'title' => $this->l("Page marque"),
                        'desc' => 'Variable(s) disponible(s) : {{shop_name}} {{category}} {{type_de_vehicule}} {{brand}}',
                        'tab' => 'seo',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_METATITLE_MODEL' => array(
                        'type' => 'text',
                        'title' => $this->l("Page modèle"),
                        'desc' => 'Variable(s) disponible(s) : {{shop_name}} {{category}} {{type_de_vehicule}} {{brand}} {{model}}',
                        'tab' => 'seo',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_METADESC_CATEGORY' => array(
                        'type' => 'text',
                        'title' => $this->l("Page catégorie"),
                        'desc' => 'Variable(s) disponible(s) : {{count}} {{shop_name}} {{category}}',
                        'tab' => 'seo',
                        'sub_title' => strtoupper('Meta Description'),
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_METADESC_TYPEDEVEHICULE' => array(
                        'type' => 'text',
                        'title' => $this->l("Page type de véhicule"),
                        'desc' => 'Variable(s) disponible(s) : {{count}} {{shop_name}} {{category}} {{type_de_vehicule}}',
                        'tab' => 'seo',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_METADESC_BRAND' => array(
                        'type' => 'text',
                        'title' => $this->l("Page marque"),
                        'desc' => 'Variable(s) disponible(s) : {{count}} {{shop_name}} {{category}} {{type_de_vehicule}} {{brand}}',
                        'tab' => 'seo',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_METADESC_MODEL' => array(
                        'type' => 'text',
                        'title' => $this->l("Page modèle"),
                        'desc' => 'Variable(s) disponible(s) : {{count}} {{shop_name}} {{category}} {{type_de_vehicule}} {{brand}} {{model}}',
                        'tab' => 'seo',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_METAKEYWORDS_CATEGORY' => array(
                        'type' => 'text',
                        'title' => $this->l("Page catégorie"),
                        'desc' => 'Variable(s) disponible(s) : {{count}} {{shop_name}} {{category}}',
                        'tab' => 'seo',
                        'sub_title' => strtoupper('Meta Keywords'),
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_METAKEYWORDS_TYPEDEVEHICULE' => array(
                        'type' => 'text',
                        'title' => $this->l("Page type de véhicule"),
                        'desc' => 'Variable(s) disponible(s) : {{count}} {{shop_name}} {{category}} {{type_de_vehicule}}',
                        'tab' => 'seo',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_METAKEYWORDS_BRAND' => array(
                        'type' => 'text',
                        'title' => $this->l("Page marque"),
                        'desc' => 'Variable(s) disponible(s) : {{count}} {{shop_name}} {{category}} {{type_de_vehicule}} {{brand}}',
                        'tab' => 'seo',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_METAKEYWORDS_MODEL' => array(
                        'type' => 'text',
                        'title' => $this->l("Page modèle"),
                        'desc' => 'Variable(s) disponible(s) : {{count}} {{shop_name}} {{category}} {{type_de_vehicule}} {{brand}} {{model}}',
                        'tab' => 'seo',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_H1_CATEGORY' => array(
                        'type' => 'text',
                        'title' => $this->l("Page catégorie"),
                        'desc' => 'Variable(s) disponible(s) : {{count}} {{shop_name}} {{category}}',
                        'tab' => 'seo',
                        'sub_title' => strtoupper('H1'),
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_H1_TYPEDEVEHICULE' => array(
                        'type' => 'text',
                        'title' => $this->l("Page type de véhicule"),
                        'desc' => 'Variable(s) disponible(s) : {{count}} {{shop_name}} {{category}} {{type_de_vehicule}}',
                        'tab' => 'seo',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_H1_BRAND' => array(
                        'type' => 'text',
                        'title' => $this->l("Page marque"),
                        'desc' => 'Variable(s) disponible(s) : {{count}} {{shop_name}} {{category}} {{type_de_vehicule}} {{brand}}',
                        'tab' => 'seo',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_H1_MODEL' => array(
                        'type' => 'text',
                        'title' => $this->l("Page modèle"),
                        'desc' => 'Variable(s) disponible(s) : {{count}} {{shop_name}} {{category}} {{type_de_vehicule}} {{brand}} {{model}}',
                        'tab' => 'seo',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_PUB_IMG' => array(
                        'type' => 'text',
                        'title' => $this->l("URL image pub listing"),
                        'tab' => 'pub',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_PUB_LINK' => array(
                        'type' => 'text',
                        'title' => $this->l("Lien image pub listing"),
                        'tab' => 'pub',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                    'PS_ADS_ALGOLIA_FRONT_PUB_TARGET' => array(
                        'type' => 'bool',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                        'title' => $this->l("Lien target _blank"),
                        'tab' => 'pub',
                        'visibility' => Shop::CONTEXT_SHOP
                    ),
                ),
                'submit' => array('title' => $this->l('Enregistrer'))
        ));
    }

    public function renderOptions() {
        $this->context->controller->addJqueryPlugin('tagify');
        $html = '<script type="text/javascript">
                    $(document).ready(function() {
                        /* amélioration de la saisie des keywords */
                        if($(".tagify_keyword").length > 0)
                            $(".tagify_keyword").tagify({delimiters: [13,59], addTagPrompt: "Ajouter un email", outputDelimiter: ";"});
                        $("#Configuration_form").submit(function() {
                            if($(".tagify_keyword").length > 0)
                                $(this).find(".tagify_keyword").val($(".tagify_keyword").tagify("serialize"));
                        });
                    });										
                </script>';

        return parent::renderOptions() . $html;
    }  

}
