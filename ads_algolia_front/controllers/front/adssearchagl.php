<?php

class ads_algolia_frontadssearchaglModuleFrontController extends ModuleFrontController
{

    public function init()
    {
        $this->page_name = "recherche"; // page_name and body id
        $this->display_column_left = true;
        $this->display_column_right = false;

        $this->context->smarty->assign(array(
            'page_seo' => false
        ));

        parent::init();
    }

    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign(array(
            'meta_title' => Configuration::get('PS_ADS_ALGOLIA_FRONT_META_TITLE'),
            'meta_keywords' => Configuration::get('PS_ADS_ALGOLIA_FRONT_META_KEYWORD'),
            'meta_description' => Configuration::get('PS_ADS_ALGOLIA_FRONT_META_DESC'),
            'title_h1_listing' => Configuration::get('PS_ADS_ALGOLIA_FRONT_TITLE'),
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
        Media::addJsDef(array('pubOffreTarget' => (int) Configuration::get('PS_ADS_ALGOLIA_FRONT_PUB_TARGET')));
    }

}
