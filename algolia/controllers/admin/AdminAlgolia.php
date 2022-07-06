<?php

include_once(PS_ADMIN_DIR . '/../classes/AdminTab.php');

class AdminAlgoliaController extends ModuleAdminController
{
    private $algolia_registry;
    private $theme_helper;
    private $indexer;
    private $algolia_helper;
    private $attributes_helper;

    public function __construct()
    {
        parent::__construct();

        $this->bootstrap = true;

        $this->algolia_registry = Algolia\Core\Registry::getInstance();
        $this->theme_helper = new \Algolia\Core\ThemeHelper($this->module);
        $this->indexer = new \Algolia\Core\Indexer();
        $this->attributes_helper = new \Algolia\Core\AttributesHelper();

        if ($this->algolia_registry->validCredential) {
            $this->algolia_helper = new \Algolia\Core\AlgoliaHelper(
                $this->algolia_registry->app_id,
                $this->algolia_registry->search_key,
                $this->algolia_registry->admin_key
            );
        }
    }

    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign('module_dir', $this->module->getPath());
        $this->context->smarty->assign('warnings', array());
        $this->context->smarty->assign('algolia_registry', $this->algolia_registry);
        $this->context->smarty->assign('theme_helper', $this->theme_helper);
        $this->context->smarty->assign('path', $this->module->getPath());

        $products_count = \Db::getInstance()->executeS('SELECT count(*) as count FROM `' . _DB_PREFIX_ . 'product` WHERE `active` IS TRUE');

        $algoliaAdminSettings = array(
            "types" => array(array('type' => 'products', 'name' => 'Products', 'count' => (int)$products_count[0]['count'])),
            "batch_count" => $this->module->batch_count,
            "site_url" => $this->module->getPath()
        );

        Media::addJsDef(array('algoliaAdminSettings' => $algoliaAdminSettings));

        $facet_types = array_merge(array("conjunctive" => "Conjunctive", "disjunctive" => "Disjunctive"), $this->theme_helper->get_current_theme()->facet_types);

        global $cookie;

        $attributes = $this->attributes_helper->getAllAttributes($cookie->id_lang);
        $searchableAttributes = $this->attributes_helper->getSearchableAttributes($cookie->id_lang);

        $this->context->smarty->assign(array(
            'facet_types' => $facet_types,
            'attributes' => $attributes,
            'searchable_attributes' => $searchableAttributes,
            'ordered_tab' => array("ordered" => "Ordered", "unordered" => "Unordered"),
            'ascending_tab' => array('asc' => 'Ascending', 'desc' => 'Descending'),
            'customs' => array('custom_ranking' => 'CUSTOM_RANKING', 'custom_ranking_order' => 'CUSTOM_RANKING_ORDER', 'custom_ranking_sort' => 'CUSTOM_RANKING_SORT'),
            'sorts' => array('asc', 'desc'),
            'base_url' => _PS_BASE_URL_
        ));

        if (Tools::getValue('submitSearchTerm')) {

            $errors = array();

            if (!Tools::getValue('term')) {
                $errors[] = $this->l('Please add a search Term');
            }
            if (!Tools::getValue('url')) {
                $errors[] = $this->l('Please add a Re-written url');
            }
            if (!Tools::getValue('title')) {
                $errors[] = $this->l('Please add a Meta Title');
            }
            if (!Tools::getValue('description')) {
                $errors[] = $this->l('Please add a Meta Description');
            }

            if (!sizeof($errors)) {
                if (!Tools::isSubmit('id_searchterm') || !(Tools::getValue('id_searchterm') > 0)) {
                    if (Db::getInstance()->execute('
                    INSERT INTO `' . _DB_PREFIX_ . 'searchterm`(`term`, `url`, `title`, `description`) 
                    VALUES (\'' . pSQL(Tools::getValue('term')) . '\',\'' . pSQL(Tools::getValue('url')) . '\',\'' . pSQL(Tools::getValue('title')) . '\',\'' . pSQL(Tools::getValue('description')) . '\')
                    ')) {
                        $this->context->smarty->assign(array(
                            'new_term_message' => $this->l('The term has been added successfully.')
                        ));
                    } else {
                        $errors[] = $this->l('An error occurred on adding of search term.');
                    }
                } else {
                    if (Db::getInstance()->execute('
                    UPDATE `' . _DB_PREFIX_ . 'searchterm`  
                    SET `term` = \'' . pSQL(Tools::getValue('term')) . '\' ,`url` = \'' . pSQL(Tools::getValue('url')) . '\',`title` = \'' . pSQL(Tools::getValue('title')) . '\',`description` = \'' . pSQL(Tools::getValue('description')) . '\'
                    WHERE `id_searchterm` = ' . (int)(Tools::getValue('id_searchterm'))
                    )) {
                        $this->context->smarty->assign(array(
                            'new_term_message' => $this->l('The term has been updated successfully.')
                        ));
                    } else {
                        $errors[] = $this->l('An error occurred on updating of search term.');
                    }
                }

            }

            if (count($errors) > 0) {
                $rule = array(
                    'id_searchterm' => Tools::getValue('id_searchterm'),
                    'term' => Tools::getValue('term'),
                    'url' => Tools::getValue('url'),
                    'title' => Tools::getValue('title'),
                    'description' => Tools::getValue('description')
                );
            }
        }

        if (Tools::isSubmit('deleteTermId') && Tools::getValue('deleteTermId') > 0) {
            $delId = Tools::getValue('deleteTermId');
            if (Db::getInstance()->delete('searchterm', 'id_searchterm = ' . $delId)) {
                $this->context->smarty->assign(array(
                    'new_term_message' => $this->l('The term has been deleted successfully.')
                ));
            } else {
                $errors[] = $this->l('An error occurred on deleting of search term.');
            }
        }

        if (Tools::isSubmit('updateSearchPrefix') && Tools::isSubmit('algolia_search_prefix')) {
            if (Tools::getValue('algolia_search_prefix') == '') {
                $this->context->smarty->assign(array(
                    'search_term_tab_errors' => $this->l('Search prefix cannot be empty.')
                ));
            } else {
                $this->algolia_registry->search_prefix = Tools::getValue('algolia_search_prefix');
            }
        }

        if (isset($errors) && sizeof($errors) && count($errors) > 0) {
            $this->context->smarty->assign(array(
                'new_term_errors' => $errors
            ));
        }

        if (isset($rule)) {
            $this->context->smarty->assign(array(
                'rule' => $rule
            ));
        }

        $searchTerms = isset($this->algolia_helper) ? $this->algolia_helper->getSearchTermList() : array();
        $this->context->smarty->assign(array(
            'search_terms' => $searchTerms,
            'algolia_search_prefix' => $this->algolia_registry->search_prefix
        ));

        $content = $this->context->smarty->fetch($this->getTemplatePath() . 'content.tpl');

        $this->context->smarty->assign(array(
            'content' => $content
        ));

        $this->context->controller->addJS($this->module->getPath() . 'js/admin.js');
        $this->context->controller->addCSS($this->module->getPath() . 'css/configure.css');
        $this->context->controller->addCSS($this->module->getPath() . 'css/admin.css');
        $this->context->controller->addJS($this->module->getPath() . '/libraries/jquery/jquery-ui.js');
        $this->context->controller->addCSS($this->module->getPath() . '/libraries/jquery/jquery-ui.min.css');

    }

    public function postProcess()
    {
        parent::postProcess();

        $action = Tools::getValue('action');

        if (method_exists($this, $action))
            $this->$action();
    }

    public function admin_post_update_extra_meta()
    {
        $metas = array();
        if (isset($_POST['ATTRIBUTE'])) {
            foreach ($_POST['ATTRIBUTE'] as $key => $value) {
                if (isset($value['INDEXABLE'])) {
                    $metas[$key] = array();
                    $metas[$key]["collapsed"] = isset($value["COLLAPSED"]) ? 1 : 0;
                    $metas[$key]["show_front"] = isset($value["SHOW_FRONT"]) ? 1 : 0;
                    $metas[$key]["hide_css_front"] = isset($value["HIDE_CSS_FRONT"]) ? 1 : 0;
                    $metas[$key]["show_home"] = isset($value["SHOW_HOME"]) ? 1 : 0;
                    $metas[$key]["label"] = isset($value["LABEL"]) && $value["LABEL"] ? $value["LABEL"] : "";
                    $metas[$key]["icon"] = isset($value["ICON"]) && $value["ICON"] ? $value["ICON"] : "";
                    $metas[$key]["css_class"] = isset($value["CSS_CLASS"]) && $value["CSS_CLASS"] ? $value["CSS_CLASS"] : "";
                    $metas[$key]["indexable"] = isset($value["INDEXABLE"]) ? 1 : 0;
                    $metas[$key]["facetable"] = $metas[$key]["indexable"] && isset($value["FACETABLE"]) ? 1 : 0;
                    $metas[$key]["retrievable"] = isset($value["RETRIEVABLE"]) ? 1 : 0;
                    $metas[$key]["type"] = isset($value["TYPE"]) ? $value['TYPE'] : 'conjunctive';
                    $metas[$key]["order"] = $value["ORDER"];
                    $metas[$key]["custom_ranking"] = isset($value["CUSTOM_RANKING"]) && $value["CUSTOM_RANKING"] ? $value["CUSTOM_RANKING"] : 0;
                    $metas[$key]["custom_ranking_sort"] = isset($value["CUSTOM_RANKING_SORT"]) && $value["CUSTOM_RANKING_SORT"] ? $value["CUSTOM_RANKING_SORT"] : 10000;
                    $metas[$key]["custom_ranking_order"] = isset($value["CUSTOM_RANKING_ORDER"]) && $value["CUSTOM_RANKING_ORDER"] ? $value["CUSTOM_RANKING_ORDER"] : 'asc';
                }
            }
        }

        $this->algolia_registry->metas = $metas;

        $this->algolia_helper->handleIndexCreation();

        Tools::redirectAdmin('index.php?controller=AdminAlgolia#extra-metas');
    }

    public function admin_post_update_account_info()
    {
        $app_id = !empty($_POST['APP_ID']) ? $_POST['APP_ID'] : '';
        $search_key = !empty($_POST['SEARCH_KEY']) ? $_POST['SEARCH_KEY'] : '';
        $admin_key = !empty($_POST['ADMIN_KEY']) ? $_POST['ADMIN_KEY'] : '';
        $index_name = !empty($_POST['INDEX_NAME']) ? $_POST['INDEX_NAME'] : '';

        $algolia_helper = new \Algolia\Core\AlgoliaHelper($app_id, $search_key, $admin_key);

        $this->algolia_registry->app_id = $app_id;
        $this->algolia_registry->search_key = $search_key;
        $this->algolia_registry->admin_key = $admin_key;
        $this->algolia_registry->index_name = $index_name;

        $algolia_helper->checkRights();

        Tools::redirectAdmin('index.php?controller=AdminAlgolia#ui_template');
    }

    public function admin_post_update_type_of_search()
    {
        $type_of_search = array();

        if (isset($_POST['AUTOCOMPLETE']) && $_POST['AUTOCOMPLETE'])
            $type_of_search[] = 'autocomplete';

        if (isset($_POST['INSTANT']) && $_POST['INSTANT'])
            $type_of_search[] = 'instant';

        $this->algolia_registry->type_of_search = $type_of_search;

        $this->algolia_registry->replace_categories = isset($_POST['REPLACE_CATEGORIES']) && $_POST['REPLACE_CATEGORIES'];

        $this->algolia_registry->use_left_column = isset($_POST['USE_LEFT_COLUMN']) && $_POST['USE_LEFT_COLUMN'];

        $this->algolia_registry->facets_order_type = (isset($_POST['facets_order_type']) && in_array($_POST['facets_order_type'], array('name_asc', 'name_desc', 'results_asc', 'results_desc'))) ? $_POST['facets_order_type'] : 'name_asc';

        if (isset($_POST['TYPE_OF_SEARCH']) && is_array($_POST['TYPE_OF_SEARCH']))
            $this->algolia_registry->type_of_search = $_POST['TYPE_OF_SEARCH'];

        if (isset($_POST['JQUERY_SELECTOR']))
            $this->algolia_registry->instant_jquery_selector = str_replace('"', '\'', $_POST['JQUERY_SELECTOR']);

        if (isset($_POST['NUMBER_BY_PAGE']) && is_numeric($_POST['NUMBER_BY_PAGE']))
            $this->algolia_registry->number_by_page = $_POST['NUMBER_BY_PAGE'];

        if (isset($_POST['NUMBER_OF_WORD_FOR_CONTENT']) && is_numeric($_POST['NUMBER_OF_WORD_FOR_CONTENT']))
            $this->algolia_registry->number_of_word_for_content = $_POST['NUMBER_OF_WORD_FOR_CONTENT'];

        if (isset($_POST['NUMBER_PRODUCTS']) && is_numeric($_POST['NUMBER_PRODUCTS']))
            $this->algolia_registry->number_products = $_POST['NUMBER_PRODUCTS'];

        if (isset($_POST['NUMBER_CATEGORIES']) && is_numeric($_POST['NUMBER_CATEGORIES']))
            $this->algolia_registry->number_categories = $_POST['NUMBER_CATEGORIES'];


        $search_input_selector = !empty($_POST['SEARCH_INPUT_SELECTOR']) ? $_POST['SEARCH_INPUT_SELECTOR'] : '';
        $theme = !empty($_POST['THEME']) ? $_POST['THEME'] : 'default';

        $this->algolia_registry->search_input_selector = str_replace('"', '\'', $search_input_selector);
        $this->algolia_registry->theme = $theme;

        $this->algolia_helper->handleIndexCreation();

        Tools::redirectAdmin('index.php?controller=AdminAlgolia#ui_template');
    }

    public function admin_post_update_searchable_attributes()
    {
        if (isset($_POST['ATTRIBUTES']) && is_array($_POST['ATTRIBUTES'])) {
            $searchable = array();

            $i = 0;

            foreach ($_POST['ATTRIBUTES'] as $key => $value) {
                if (isset($value['SEARCHABLE'])) {
                    $searchable[$key] = array();

                    $searchable[$key]["ordered"] = $value['ORDERED'];
                    $searchable[$key]["order"] = $i;

                    $i++;
                }
            }

            $this->algolia_registry->searchable = $searchable;

            $this->algolia_helper->handleIndexCreation();
        }

        Tools::redirectAdmin('index.php?controller=AdminAlgolia#searchable_attributes');
    }

    public function admin_post_custom_ranking()
    {
        $metas = $this->algolia_registry->metas;

        if (isset($_POST['ATTRIBUTES']) && is_array($_POST['ATTRIBUTES'])) {
            $i = 1; // keep 1 and not 0 to avoid bad condition when saving metas

            foreach ($_POST['ATTRIBUTES'] as $key => $value) {
                $metas[$key]['custom_ranking'] = isset($value['CUSTOM_RANKING']) ? 1 : 0;
                $metas[$key]["custom_ranking_order"] = $value["CUSTOM_RANKING_ORDER"];

                if ($metas[$key]['custom_ranking'])
                    $metas[$key]["custom_ranking_sort"] = $i;
                else
                    $metas[$key]["custom_ranking_sort"] = 10000;

                $i++;
            }

            $this->algolia_registry->metas = $metas;
        }

        $this->algolia_helper->handleIndexCreation();

        Tools::redirectAdmin('index.php?controller=AdminAlgolia#custom-ranking');
    }

    public function admin_post_update_sortable_attributes()
    {
        $this->indexer->indexCategories();

        if (isset($_POST['ATTRIBUTES']) && is_array($_POST['ATTRIBUTES'])) {
            $sortable = array();

            foreach ($_POST['ATTRIBUTES'] as $key => $values) {
                if (isset($values['asc']))
                    $sortable[$key . '_asc'] = array('name' => $key, 'sort' => 'asc', 'order' => $values['ORDER_asc']);

                if (isset($values['desc']))
                    $sortable[$key . '_desc'] = array('name' => $key, 'sort' => 'desc', 'order' => $values['ORDER_asc']);
            }

            uasort($sortable, function ($a, $b) {
                if ($a['order'] < $b['order'])
                    return -1;
                return 1;
            });

            $this->algolia_registry->sortable = $sortable;

            $this->algolia_helper->handleIndexCreation();
        }

        Tools::redirectAdmin('index.php?controller=AdminAlgolia#sortable_attributes');
    }

    public function admin_post_reset_config_to_default()
    {
        $this->algolia_registry->reset_config_to_default();
    }


    public function admin_post_reindex()
    {
        foreach ($_POST as $post) {
            $subaction = explode("__", $post);

            if (count($subaction) == 1 && $subaction[0] != "reindex") {
                if ($subaction[0] == 'handle_index_creation') {
                    $this->algolia_helper->handleIndexCreation();
                }

                if ($subaction[0] == 'index_categories') {
                    $this->indexer->indexCategories();

                }
                if ($subaction[0] == 'move_indexes') {
                    $this->indexer->moveTempIndexes();
                }
            }

            if (count($subaction) == 3) {
                $this->indexer->indexProductsPart($this->module->batch_count, $subaction[2]);
            }
        }

        /** Leave it there since this is a javascript query **/
        die();
    }
}