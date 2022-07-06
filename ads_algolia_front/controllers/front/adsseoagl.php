<?php

class ads_algolia_frontadsseoaglModuleFrontController extends ModuleFrontController
{

    public function init()
    {
        $this->page_name = "recherche"; // page_name and body id
        $this->display_column_left = true;
        $this->display_column_right = false;

        $this->context->smarty->assign(array(
            'page_seo' => true
        ));

        parent::init();
    }

    public function initContent()
    {
        parent::initContent();
        $this->context->smarty->assign(array(
            'meta_title' => $this->module->seoConstructor("METATITLE"),
            'meta_keywords' => $this->module->seoConstructor("METAKEYWORDS"),
            'meta_description' => $this->module->seoConstructor("METADESC"),
            'title_h1_listing' => $this->module->seoConstructor("H1"),
            'fils_ariane_listing' => Configuration::get('PS_ADS_ALGOLIA_FRONT_FILS_ARIANE'),
            'link_default_reprise_vehicle' => Configuration::get('PS_URL_REPRISE_VEHICLE_DEFAULT'),
            'link_default_contact' => Configuration::get('PS_URL_CONTACT_DEFAULT'),
            'link_img_default_no_result' => $this->module->getPath() . 'views/img/lot_of_criterions_selected.jpg'
        ));

        //SEO - Noindex if necessary
        $this->module->setTagNoIndex();

        $this->setTemplate('search_list.tpl');
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->context->controller->addJS($this->module->getPath() . 'views/js/lib/algolia/instantsearch.min.js');
        $this->context->controller->addJS($this->module->getPath() . 'views/js/lib/jquery/ion.rangeslider/ion.rangeSlider.min.js');
        $this->context->controller->addJS($this->module->getPath() . 'views/js/lib/algolia/widgets/instantsearch-ion.rangeSlider.min.js');
        $this->context->controller->addJS($this->module->getPath() . 'views/js/lib/lodash/4.17.11-npm/core.js');
        //$this->context->controller->addJS($this->module->getPath() . 'views/js/build/vendors.adsaf_script.js');
        //$this->context->controller->addJS($this->module->getPath() . 'views/js/build/main.adsaf_script.js');
        //$this->context->controller->addJS($this->module->getPath() . 'views/js/lib/react/react.production.min.js');
        //$this->context->controller->addJS($this->module->getPath() . 'views/js/lib/react-dom/react-dom.production.min.js');
        $this->context->controller->addJS($this->module->getPath() . 'views/js/src/main/searchpage.js');

        //$this->context->controller->addCSS('https://cdn.jsdelivr.net/npm/instantsearch.js@2.6.0/dist/instantsearch.min.css');
        //$this->context->controller->addCSS('https://cdn.jsdelivr.net/npm/instantsearch.js@2.6.0/dist/instantsearch-theme-algolia.min.css');
        $this->context->controller->addCSS($this->module->getPath() . 'views/css/lib/ion.rangeSlider-2.2.0/src/ion.rangeSlider.css', 'all');
        $this->context->controller->addCSS($this->module->getPath() . 'views/css/lib/ion.rangeSlider-2.2.0/src/ion.rangeSlider.skinFlat.css', 'all');
        $this->context->controller->addCSS($this->module->getPath() . 'views/css/listing.css');


        Media::addJsDef(array('algoliaTranslateLabel' => $this->module->getAlgoliaTranslation()));
        Media::addJsDef(array('allModelString' => $this->module->l('Tous les modèles')));
        Media::addJsDef(array('aglLabelVehicle' => $this->module->l('véhicule')));

        //PUB
        Media::addJsDef(array('pubOffrePath' => Configuration::get('PS_ADS_ALGOLIA_FRONT_PUB_IMG')));
        Media::addJsDef(array('pubOffreLink' => Configuration::get('PS_ADS_ALGOLIA_FRONT_PUB_LINK')));
        Media::addJsDef(array('pubOffreTarget' => (int)Configuration::get('PS_ADS_ALGOLIA_FRONT_PUB_TARGET')));

        $type_de_vehicule = $this->module->getHitsWithSlug("type_de_vehicule", Tools::getValue("type_de_vehicule"));
        $brand = $this->module->getHitsWithSlug("brand", Tools::getValue("brand"));
        $model = $this->module->getHitsWithSlug("model", Tools::getValue("model"));

        $algoliaVehicleParameters = new stdClass();
        $algoliaVehicleParameters->aglDFR = array();
        if (count($type_de_vehicule) > 0)
            $algoliaVehicleParameters->aglDFR["type_de_vehicule.name"] = array($type_de_vehicule[0]["type_de_vehicule"]["name"]);
        if (count($brand) > 0)
            $algoliaVehicleParameters->aglDFR["marque.name"] = array($brand[0]["marque"]["name"]);
        if (count($model) > 0)
            $algoliaVehicleParameters->aglDFR["modele.name"] = array($model[0]["modele"]["name"]);

        $link = new Link();
        $seo_breadcrumb = array();
        $seo_breadcrumb = array(
            "category" => array(
                "label" => ucfirst(Tools::getValue("category")),
                "path" => $link->getModuleLink($this->module->name, "adsseoagl", array("category" => Tools::getValue("category"), "type_de_vehicule" => "", "brand" => "", "model" => ""))
            )
        );
        if (count($type_de_vehicule) > 0) {
            $seo_breadcrumb["type_de_vehicule"] = array(
                "label" => $algoliaVehicleParameters->aglDFR["type_de_vehicule.name"][0],
                "path" => $link->getModuleLink($this->module->name, "adsseoagl", array("category" => Tools::getValue("category"), "type_de_vehicule" => Tools::getValue("type_de_vehicule"), "brand" => "", "model" => ""))
            );
            if (count($brand) > 0)
                $seo_breadcrumb["brand"] = array(
                    "label" => $algoliaVehicleParameters->aglDFR["marque.name"][0],
                    "path" => $link->getModuleLink($this->module->name, "adsseoagl", array("category" => Tools::getValue("category"), "type_de_vehicule" => Tools::getValue("type_de_vehicule"), "brand" => Tools::getValue("brand"), "model" => ""))
                );
            if (count($model) > 0)
                $seo_breadcrumb["model"] = array(
                    "label" => $algoliaVehicleParameters->aglDFR["modele.name"][0],
                    "path" => $link->getModuleLink($this->module->name, "adsseoagl", array("category" => Tools::getValue("category"), "type_de_vehicule" => Tools::getValue("type_de_vehicule"), "brand" => Tools::getValue("brand"), "model" => Tools::getValue("model")))
                );
        } else {
            if (count($brand) > 0)
                $seo_breadcrumb["brand"] = array(
                    "label" => $algoliaVehicleParameters->aglDFR["marque.name"],
                    "path" => $link->getModuleLink($this->module->name, "adsseoagl", array("category" => Tools::getValue("category"), "type_de_vehicule" => "", "brand" => Tools::getValue("brand"), "model" => ""))
                );
            if (count($model) > 0)
                $seo_breadcrumb["model"] = array(
                    "label" => $algoliaVehicleParameters->aglDFR["modele.name"],
                    "path" => $link->getModuleLink($this->module->name, "adsseoagl", array("category" => Tools::getValue("category"), "type_de_vehicule" => "", "brand" => Tools::getValue("brand"), "model" => Tools::getValue("model")))
                );
        }
        $this->context->smarty->assign(array(
            'seo_breadcrumb' => $seo_breadcrumb
        ));

        Media::addJsDef(array('algoliaVehicleParameters' => $algoliaVehicleParameters));

    }

}
