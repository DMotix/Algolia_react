<?php

class AlgoliaCronModuleFrontController extends ModuleFrontController
{


    public function init()
    {

        parent::init();
        $this->cron_url = $this->context->link->getModuleLink('algolia', 'cron') .'?customaction=subaction';
        $this->algolia_registry     = Algolia\Core\Registry::getInstance();
        $this->theme_helper         = new \Algolia\Core\ThemeHelper($this->module);
        $this->indexer              = new \Algolia\Core\Indexer();
        $this->attributes_helper    = new \Algolia\Core\AttributesHelper();

        if ($this->algolia_registry->validCredential)
        {
            $this->algolia_helper   = new \Algolia\Core\AlgoliaHelper(
                $this->algolia_registry->app_id,
                $this->algolia_registry->search_key,
                $this->algolia_registry->admin_key
            );
        }
        switch (Tools::getValue('customaction'))
        {
            case 'startindex':
                $this->startIndex();
                break;
            
            case 'subaction':
                $this->subAction();
                break;
        }
    }

    public function startIndex()
    {
        if(!Tools::getValue('k') || Tools::getValue('k') != Tools::encrypt('hashtagrc'))
        {
            die('Bad token');
        }
        else
        {
            

            $products_count = Db::getInstance()->executeS('SELECT count(*) as count FROM `'._DB_PREFIX_.'product` WHERE `active` IS TRUE');


            $algoliaAdminSettings = array(
                "types"         => array(array('type' => 'products', 'name' => 'Products', 'count' => (int) $products_count[0]['count'])),
                "batch_count"   => $this->module->batch_count
            );

            $actions = array();
            $actions[] = array('subaction'=> "handle_index_creation", 'name' => "Setup indices", 'sup'=> "");


            foreach ($algoliaAdminSettings['types'] as $t)
            {
                $number = ceil($t['count'] / $this->module->batch_count);
                for ($i = 0; $i < $number; $i++)
                {
                    $actions[] = array(
                        'name' => 'Upload Products',
                        'subaction' => 'type__products__' . $i,
                        'sup' => ($i === $number - 1 ? $t['count'] : ($i + 1) * $algoliaAdminSettings['batch_count']) . "/" . $t['count']
                        );
                }
            }

            $actions[] = array('subaction'=> "index_categories", 'name'=> "Upload Categories", 'sup'=> "" );

            $actions[] = array('subaction'=> "move_indexes", 'name'=> "Move indices to production", 'sup'=> "" );

            foreach ($actions as $action) {
                $fields_string = 'name='.$action['name'].'&subaction='.$action['subaction'].'&sup='.$action['sup'];
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $this->cron_url); 
                curl_setopt($ch,CURLOPT_POST, count($action));
                curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
                $content = curl_exec($ch);
                // d($content);
                curl_close($ch);
            }
            //$this->algolia_helper->handleIndexCreation();
        }       
    }

    public function subAction()
    {
        // d($_POST);
        foreach ($_POST as $post)
        {
            $subaction = explode("__", $post);

            if (count($subaction) == 1 && $subaction[0] != "reindex")
            {
                if ($subaction[0] == 'handle_index_creation')
                {
                    $this->algolia_helper->handleIndexCreation();
                }

                if ($subaction[0] == 'index_categories')
                {
                    $this->indexer->indexCategories();

                }
                if ($subaction[0] == 'move_indexes')
                {
                    $this->indexer->moveTempIndexes();
                }
            }

            if (count($subaction) == 3)
            {
                $this->indexer->indexProductsPart($this->module->batch_count, $subaction[2]);
            }
        }

        /** Leave it there since this is a javascript query **/
        die();      
        
    }
}