<?php

class ads_algolia_frontadsalertaglModuleFrontController extends ModuleFrontController
{

    public function init()
    {
        if (!$this->context->customer->isLogged())
            Tools::redirect($this->context->link->getPageLink('authentication', true, (int)$this->context->language->id, 'content_only=1&back='
                . urlencode($this->context->link->getModuleLink('ads_algolia_front', 'adsalertagl', array(), null, null, null, true)) . '?content_only=1'));
        $this->page_name = "alerte-recherche"; // page_name and body id
        $this->display_column_left = true;
        $this->display_column_right = false;

        parent::init();
    }

    public function initContent()
    {
        parent::initContent();
        $this->context->smarty->assign(array(
            'path_search_alert' => Configuration::get('PS_ADS_ALGOLIA_FRONT_META_TITLE'),
            'token' => Tools::getToken()
        ));

        $this->setTemplate('search_alert.tpl');

    }

    public function displayAjax()
    {
        $link = new Link();

        $parameters = json_decode(Tools::getValue('parameters',false));
        $search_alert = new SearchAlert();
        foreach($parameters as $key => $value) {
            $search_alert->{$key} = $value;
        }
        if (!$search_alert->validateFields(false, false)) {
            die(Tools::jsonEncode(array("error" => true, "message" => $search_alert->validateFields(false, true))));
        }
        $search_alert->add();
        echo Tools::jsonEncode(array("error" => false));
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->context->controller->addJS('https://cdn.jsdelivr.net/lodash/4.16.4/lodash.min.js');
        $this->context->controller->addJS($this->module->getPath() . 'views/js/lib/jquery/ajaxq/ajaxq.js');
        $this->context->controller->addJS($this->module->getPath() . 'views/js/search_alert/front.js');
        Media::addJsDef(array('path_search_alert' => $this->context->link->getModuleLink('ads_algolia_front', 'adsalertagl', array(), null, null, null, true)));

    }

}