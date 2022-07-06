<?php

/*
 * File: /upgrade/upgrade-1.1.php
 */

function upgrade_module_1_1($module)
{
    //On change la variable configuration en passant category à facetable à true (pour pages SEO catégorie)
    $algolia_registry = Algolia\Core\Registry::getInstance();
    $old_metas = $algolia_registry->metas;
    $new_metas = $old_metas;
    $new_metas["category"]["facetable"] = 1;
    $algolia_registry->metas = $new_metas;
    $algolia_registry->update();
    //Envoi sur algolia
    $algolia_helper = new Algolia\Core\AlgoliaHelper(
        $algolia_registry->app_id,
        $algolia_registry->search_key,
        $algolia_registry->admin_key
    );
    $algolia_helper->handleIndexCreation();
    return true;
}