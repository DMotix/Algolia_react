<?php

class AlgoliaSearchLandingModuleFrontController  extends ModuleFrontController
{
    public function setMedia()
    {
        parent::setMedia();
        $this->addCSS(_THEME_CSS_DIR_.'product_list.css');
    }
    public function init()
    {
        parent::init();
        
        if(!Tools::getValue('searchterm') || !$searchterm = $this->module->_getSearchTermByUrl(Tools::getValue('searchterm')))
        {
            Tools::redirect('page-not-found');
        } else {
            // grab search term data from the db
            
            $url = 'index.php?category=1#q='.$searchterm['term'].'&page=0&refinements=[{"categories"%3A"Shop All "}]&numerics_refinements={}&index_name="webstore_local_all_fr"';

            Media::AddJSDef(array('algolia_results_url'=> $url, 'replace_center' => true));

        }

        
        
    }
}