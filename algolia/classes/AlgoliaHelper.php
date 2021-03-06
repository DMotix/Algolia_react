<?php namespace Algolia\Core;

class AlgoliaHelper
{
    private $algolia_client;
    private $algolia_registry;
    private $attribute_helper;

    private $app_id;
    private $search_key;
    private $admin_key;

    public function __construct($app_id, $search_key, $admin_key)
    {
        $this->algolia_client   = new \AlgoliaSearch\Client($app_id, $admin_key);
        $this->algolia_registry = \Algolia\Core\Registry::getInstance();
        $this->attribute_helper = new AttributesHelper();

        $this->app_id           = $app_id;
        $this->admin_key        = $admin_key;
        $this->search_key       = $search_key;
    }

    public function checkRights()
    {
//        try
//        {
//            /* Check app_id && admin_key => Exception thrown if not working */
//            $this->algolia_client->listIndexes();
//
//            /* Check search_key_rights */
//            $keys_values = $this->algolia_client->getUserKeyACL($this->search_key);
//
//            if ( ! ($keys_values && isset($keys_values['acl']) && in_array('search', $keys_values['acl'])))
//                throw new \Exception("Search key does not have search right");
//
//            $this->algolia_registry->validCredential = true;
//        }
//        catch(\Exception $e)
//        {
//            $this->algolia_registry->validCredential = false;
//        }
        $this->algolia_registry->validCredential = true;
    }

    public function setSettings($index_name, $settings)
    {
        $index = $this->algolia_client->initIndex($index_name);
        $index->setSettings($settings);
    }

    public function getSettings($index_name)
    {
        $index = $this->algolia_client->initIndex($index_name);

        try
        {
            $settings = $index->getSettings();

            return $settings;
        }
        catch (\Exception $e)
        {

        }

        return array();
    }

    public function mergeSettings($index_name, $settings)
    {
        $onlineSettings = $this->getSettings($index_name);

        $removes = array('slaves');

        foreach ($removes as $remove)
            if (isset($onlineSettings[$remove]))
                unset($onlineSettings[$remove]);

            foreach ($settings as $key => $value)
                $onlineSettings[$key] = $value;

            return $onlineSettings;
        }

        public function handleIndexCreation()
        {
            foreach (\Language::getLanguages() as $language)
            {
                $index_name                 = $this->algolia_registry->index_name;
                $facets                     = array();
                $customRankingTemp          = array();
                $attributesToIndex          = array();
                $unretrievableAttributes    = array();

            // foreach ($this->attribute_helper->getAllAttributes($language['id_lang']) as $key => $value)
            //     if (isset($this->algolia_registry->searchable[$key]))
            //         if ($this->algolia_registry->searchable[$key]['ordered'] == 'unordered')
            //             $attributesToIndex[] = 'unordered('.$value->name.')';
            //         else
            //             $attributesToIndex[] = $value->name;

                $allattrs = $this->attribute_helper->getAllAttributes($language['id_lang']);


                foreach ($this->algolia_registry->searchable as $key => $value) {
                    if(isset($allattrs[$key]))
                    {
                        if ($value['ordered'] == 'unordered')
                            $attributesToIndex[] = 'unordered('.$allattrs[$key]->name.')';
                        else
                            $attributesToIndex[] = $allattrs[$key]->name;
                    }
                }
                foreach ($this->attribute_helper->getAllAttributes($language['id_lang']) as $key => $value)
                {
                    if (isset($this->algolia_registry->metas[$key]) && $this->algolia_registry->metas[$key]['facetable'])
                        $facets[] = "searchable(" . $value->name . ")";

                    if (isset($this->algolia_registry->metas[$key]) && $this->algolia_registry->metas[$key]['retrievable'] == false)
                        $unretrievableAttributes[] = $value->name;

                    if (isset($this->algolia_registry->metas[$key]) && $this->algolia_registry->metas[$key]['custom_ranking'])
                        $customRankingTemp[] = array(
                            'sort' => $this->algolia_registry->metas[$key]['custom_ranking_sort'],
                            'value' => $this->algolia_registry->metas[$key]['custom_ranking_order'] . '(' . $value->name . ')'
                            );
                }

            /**
             * Prepare Settings
             */

            usort($customRankingTemp, function ($a, $b) {
                if ($a['sort'] < $b['sort'])
                    return -1;
                if ($a['sort'] == $b['sort'])
                    return 0;
                return 1;
            });

            $customRanking = array_map(function ($obj) {
                return $obj['value'];
            }, $customRankingTemp);

            $settings = array(
                'attributesToIndex'         => $attributesToIndex,
                'attributesForFaceting'     => array_values(array_unique($facets)),
                'unretrievableAttributes'   => $unretrievableAttributes,
                'customRanking'             => $customRanking
                );

            /**
             * Handle Instant Search Indexes
             */

            $mergeSettings = $this->mergeSettings($index_name.'all', $settings);

            $this->setSettings($index_name.'all_'.$language['iso_code'], $mergeSettings);
            $this->setSettings($index_name.'all_'.$language['iso_code'].'_temp', $mergeSettings);

            /**
             * Handle Slaves
             */


            if (count($this->algolia_registry->sortable) > 0)
            {
                $allAttributes = $this->attribute_helper->getAllAttributes($language['id_lang']);

                $slaves = array();

                foreach ($this->algolia_registry->sortable as $values)
                    $slaves[] = $index_name . 'all_' . $language['iso_code'] . '_' . $allAttributes[$values['name']]->name . '_' . $values['sort'];

                $this->setSettings($index_name.'all_'.$language['iso_code'], array('slaves' => $slaves));

                foreach ($this->algolia_registry->sortable as $values)
                {
                    /* HACK change $values['name'] by $allAttributes[$values['name']]->name */
                    $mergeSettings['ranking'] = array($values['sort'].'('.$allAttributes[$values['name']]->name.')', 'typo', 'geo', 'words', 'proximity', 'attribute', 'exact', 'custom');

                    $this->setSettings($index_name . 'all_' . $language['iso_code'] . '_' . $allAttributes[$values['name']]->name . '_' . $values['sort'], $mergeSettings);
                }
            }
        }
    }

    public function move($temp_index_name, $index_name)
    {
        $this->algolia_client->moveIndex($temp_index_name, $index_name);
    }

    public function pushObjects($index_name, $objects)
    {
        $index = $this->algolia_client->initIndex($index_name);

        $index->saveObjects($objects);
    }

    public function pushObject($index_name, $object)
    {
        $index = $this->algolia_client->initIndex($index_name);

        $index->saveObject($object);
    }

    public function deleteObject($index_name, $object)
    {
        $index = $this->algolia_client->initIndex($index_name);

        $index->deleteObject($object);
    }


    public function deleteObjects($index_name, $objects)
    {
        $index = $this->algolia_client->initIndex($index_name);

        $index->deleteObjects($objects);
    }

    public function getSearchTermList()
    {
        $terms =  \Db::getInstance()->executeS('
            SELECT s.id_searchterm, s.term,s.url,s.title,s.description 
            FROM '._DB_PREFIX_.'searchterm s ');
        $retArr = array();
        foreach ($terms as $rule) {
            $id          = (int)($rule['id_searchterm']);
            $term        = (!preg_match('/^0$/Ui', $rule['term']) ? htmlentities($rule['term'], ENT_QUOTES, 'UTF-8') : Configuration::get('PS_SHOP_NAME'));
            $url         = (!preg_match('/^0$/Ui', $rule['url']) ? htmlentities($rule['url'], ENT_QUOTES, 'UTF-8') : Configuration::get('PS_SHOP_NAME'));
            $title       = (!preg_match('/^0$/Ui', $rule['title']) ? htmlentities($rule['title'], ENT_QUOTES, 'UTF-8') : Configuration::get('PS_SHOP_NAME'));
            $description = (!preg_match('/^0$/Ui', $rule['description']) ? htmlentities($rule['description'], ENT_QUOTES, 'UTF-8') : Configuration::get('PS_SHOP_NAME'));
            $retArr[] = array(
                'id'          => $id,
                'term'        => $term,
                'url'         => $url,
                'title'       => $title,
                'description' => $description
                );
        }
        return $retArr;
    }
}