<?php

class ads_algolia_frontadsseolandingaglModuleFrontController extends ModuleFrontController
{

    private $id_seo = false;
    private $realURI;

    public function init()
    {
        $this->page_name = "recherche"; // page_name and body id
        $this->display_column_left = true;
        $this->display_column_right = false;

        $this->context->smarty->assign(array(
            'page_seo' => true
        ));
        parent::init();
        $this->_setSEOTags();
    }

    private function _setSEOTags()
    {
        $seo_url = Tools::getValue('seo_url', false);

        if ($seo_url) {
            $resultSeoUrl = AdsAlgoliaSeo::getSeoSearchBySeoUrl($seo_url, (int)$this->context->cookie->id_lang, (int)$this->context->shop->id);
            if (!$resultSeoUrl) {
                Tools::redirect($this->context->link->getModuleLink($this->module->name, "adssearchagl"));
                die;
            }
            if ($resultSeoUrl[0]['deleted']) {
                header("Status: 301 Moved Permanently", false, 301);
                Tools::redirect($this->context->link->getModuleLink($this->module->name, "adssearchagl"));
                die();
            }
            $obj = new AdsAlgoliaSeo($resultSeoUrl[0]["id_seo"]);
            $cross_links = array();
            foreach ($obj->getCrossLinksOptionsSelected(Context::getContext()->shop->id, Context::getContext()->language->id) as $key => $value) {
                $seo_cross = new AdsAlgoliaSeo((int)$key, Context::getContext()->language->id, Context::getContext()->shop->id);
                $cross_links[] = array(
                    "title" => $value,
                    "link" => Context::getContext()->link->getModuleLink($this->module->name, "adsseolandingagl", array("seo_url" => $seo_cross->seo_url))
                );
            }
            $this->context->smarty->assign(array(
                'page_name' => 'recherche',
                'meta_title' => $resultSeoUrl[0]['meta_title'],
                'meta_description' => $resultSeoUrl[0]['meta_description'],
                'meta_keywords' => $resultSeoUrl[0]['meta_keywords'],
                'title_h1_listing' => $resultSeoUrl[0]['title'],
                'ads_seo_description_top' => $resultSeoUrl[0]['description_top'],
                'ads_seo_description_footer' => $resultSeoUrl[0]['description_footer'],
                'ads_seo_crosslinks' => $cross_links,
                'fils_ariane_listing' => Configuration::get('PS_ADS_ALGOLIA_FRONT_FILS_ARIANE'),
                'link_default_reprise_vehicle' => Configuration::get('PS_URL_REPRISE_VEHICLE_DEFAULT'),
                'link_default_contact' => Configuration::get('PS_URL_CONTACT_DEFAULT'),
                'link_img_default_no_result' => $this->module->getPath() . 'views/img/lot_of_criterions_selected.jpg'
            ));
        }
    }

    public function initContent()
    {
        parent::initContent();

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
        $this->context->controller->addJS($this->module->getPath() . 'views/js/src/main/searchpage.js');

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

        $seo_url = Tools::getValue('seo_url', false);
        $resultSeoUrl = AdsAlgoliaSeo::getSeoSearchBySeoUrl($seo_url, (int)$this->context->cookie->id_lang, (int)$this->context->shop->id);

        $seo = new AdsAlgoliaSeo($resultSeoUrl[0]['id_seo']);
        $criterias = $seo->criteriaGenerator((int)$this->context->language->id, unserialize(base64_decode(($seo->criteria))));

        $algoliaVehicleParameters = $criterias;

        $link = new Link();
        $seo_breadcrumb = array();

        $this->context->smarty->assign(array(
            'seo_breadcrumb' => $seo_breadcrumb
        ));

        Media::addJsDef(array('algoliaVehicleParameters' => $algoliaVehicleParameters));

    }

    public function process()
    {
        $seo_url = Tools::getValue('seo_url', false);
        if ($seo_url && $this->id_seo) {
            $resultSeoUrl = AdsAlgoliaSeo::getSeoSearchByIdSeo((int)$this->id_seo, (int)$this->context->cookie->id_lang, (int)$this->context->shop->id);
            if (!$resultSeoUrl) {
                Tools::redirect($this->context->link->getModuleLink($this->module->name, "adssearchagl"));
                die;
            }
            if ($resultSeoUrl[0]['deleted']) {
                header("Status: 301 Moved Permanently", false, 301);
                Tools::redirect($this->context->link->getModuleLink($this->module->name, "adssearchagl"));
                die();
            }
            $currentUri = explode('?', $_SERVER['REQUEST_URI']);
            $currentUri = $currentUri[0];
            $this->realURI = __PS_BASE_URI__ . (Language::countActiveLanguages() > 1 ? Language::getIsoById($this->context->cookie->id_lang) . '/' : '') . 's/' . $resultSeoUrl[0]['id_seo'] . '/' . $resultSeoUrl[0]['seo_url'];
            if ($resultSeoUrl[0]['seo_url'] != $seo_url || $currentUri != $this->realURI) {
                header("Status: 301 Moved Permanently", false, 301);
                header("Location: " . $this->realURI);
                die();
            }
        }
    }

}
