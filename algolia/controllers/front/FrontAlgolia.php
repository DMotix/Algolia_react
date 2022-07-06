<?php

class FrontAlgoliaController {

    private $algolia_helper;
    private $algolia_registry;
    private $theme_helper;
    private $indexer;
    private $attribute_helper;
    private $module;

    public function __construct(&$module) {
        $this->module = $module;

        $this->algolia_registry = \Algolia\Core\Registry::getInstance();
        $this->theme_helper = new \Algolia\Core\ThemeHelper($this->module);
        $this->indexer = new \Algolia\Core\Indexer();
        $this->attribute_helper = new \Algolia\Core\AttributesHelper();

        if ($this->algolia_registry->validCredential) {
            $this->algolia_helper = new \Algolia\Core\AlgoliaHelper(
                    $this->algolia_registry->app_id, $this->algolia_registry->search_key, $this->algolia_registry->admin_key
            );
        }
    }

    public function hookDisplayHeader() {
        global $cookie;
        
        if ($this->algolia_registry->validCredential == false)
            return false;

        $search_url = Context::getContext()->link->getModuleLink('algolia', 'search');
        $this->module->getContext()->smarty->assign('algolia_search_url', $search_url);

        $current_language = \Language::getIsoById($cookie->id_lang);

        $indices = array();

        if ($this->algolia_registry->number_products > 0)
            $indices[] = array('index_name' => $this->algolia_registry->index_name . 'all_' . $current_language, 'name' => 'products', 'order1' => 1, 'order2' => 0, 'nbHits' => $this->algolia_registry->number_products);

        if ($this->algolia_registry->number_categories > 0)
            $indices[] = array('index_name' => $this->algolia_registry->index_name . 'categories_' . $current_language, 'name' => 'categories', 'order1' => 0, 'order2' => 0, 'nbHits' => $this->algolia_registry->number_categories);

        $facets = array();
        $sorting_indices = array();

        $attributes = $this->attribute_helper->getAllAttributes($cookie->id_lang);

        foreach ($this->algolia_registry->sortable as $sortable)
            $sorting_indices[] = array(
                'index_name' => $this->algolia_registry->index_name . 'all_' . $current_language . '_' . $sortable['name'] . '_' . $sortable['sort'],
                'label' => $sortable['name'] . '_' . $sortable['sort']
            );

        foreach ($attributes as $key => $value) {
            if (isset($this->algolia_registry->metas[$key]) && isset($this->algolia_registry->metas[$key]['facetable']) && $this->algolia_registry->metas[$key]['facetable'])
                $facets[] = array('tax' => $value->name, 'name' => $value->name, 'order' => $value->order, 'order2' => 0, 'type' => $value->facet_type);
        }


        $currency = new CurrencyCore($cookie->id_currency);
        $currency = $currency->sign;

        //die(var_dump($this->algolia_registry->search_key));

        $algoliaSettings = array(
            'app_id' => $this->algolia_registry->app_id,
            'search_key' => $this->algolia_registry->search_key,
            'indices' => $indices,
            'sorting_indices' => $sorting_indices,
            'index_name' => $this->algolia_registry->index_name,
            'type_of_search' => $this->algolia_registry->type_of_search,
            'instant_jquery_selector' => str_replace("\\", "", $this->algolia_registry->instant_jquery_selector),
            'facets' => $facets,
            'number_by_page' => $this->algolia_registry->number_by_page,
            'search_input_selector' => str_replace("\\", "", $this->algolia_registry->search_input_selector),
            "plugin_url" => $this->module->getPath(),
            "language" => $current_language,
            'theme' => $this->theme_helper->get_current_theme(),
            'currency' => $currency,
            'facets_order_type' => $this->algolia_registry->facets_order_type,
            'use_left_column' => $this->algolia_registry->use_left_column
        );

        Media::addJsDef(array('algoliaSettings' => $algoliaSettings));

        $this->module->getContext()->smarty->assign(array(
            'algolia_page' => urlencode('?category=1#q=&page=0&refinements=[{"categories"%3A"Shop All "}]&numerics_refinements={}&index_name="all_en"')
        ));

        // get search terms and see if meta tags need to be overridden
        $terms = $this->algolia_helper->getSearchTermList();
        $prefix = $this->algolia_registry->search_prefix;
        foreach ($terms as $term) {
            $url = $prefix . '/' . $term['url'];
            if (strpos($_SERVER['REQUEST_URI'], $url) !== false) {
                $this->module->getContext()->smarty->assign('meta_title', $term['title']);
                $this->module->getContext()->smarty->assign('meta_description', $term['description']);
                break;
            }
        }
    }

    public function hookActionSearch($params) {
        if (in_array('instant', $this->algolia_registry->type_of_search)) {
            global $cookie;

            $current_language = \Language::getIsoById($cookie->id_lang);

            $url = '/index.php#q=' . $params['expr'] . '&page=0&refinements=%5B%5D&numerics_refinements=%7B%7D&index_name=%22' . $this->algolia_registry->index_name . 'all_' . $current_language . '%22';


            header('Location: ' . $url);

            die();
        }
    }

    public function hookActionProductListOverride($params) {
        if (in_array('instant', $this->algolia_registry->type_of_search) == false)
            return;

        if ($this->algolia_registry->replace_categories == false || isset($this->algolia_registry->metas['categories']) == false)
            return;

        if (isset($_GET['id_category']) == false || is_numeric($_GET['id_category']) == false)
            return;

        $category_id = $_GET['id_category'];

        $path = array();
        $context = Context::getContext();
        $interval = Category::getInterval($category_id);
        $id_root_category = $context->shop->getCategory();
        $interval_root = Category::getInterval($id_root_category);

        if ($interval) {
            $sql = 'SELECT c.id_category, cl.name, cl.link_rewrite
                        FROM ' . _DB_PREFIX_ . 'category c
                        LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON (cl.id_category = c.id_category' . Shop::addSqlRestrictionOnLang('cl') . ')
                        ' . Shop::addSqlAssociation('category', 'c') . '
                        WHERE c.nleft <= ' . $interval['nleft'] . '
                            AND c.nright >= ' . $interval['nright'] . '
                            AND c.nleft >= ' . $interval_root['nleft'] . '
                            AND c.nright <= ' . $interval_root['nright'] . '
                            AND cl.id_lang = ' . (int) $context->language->id . '
                            AND c.active = 1
                            AND c.level_depth > ' . (int) $interval_root['level_depth'] . '
                        ORDER BY c.level_depth ASC';

            $categories = Db::getInstance()->executeS($sql);

            foreach ($categories as $category) {
                $path[] = $category['name'];
            }
        }

        $path = implode(' /// ', $path);

        global $cookie;

        $current_language = \Language::getIsoById($cookie->id_lang);

        $path = str_replace('$', '%24', $path);
        $url = '/index.php?category=1#q=&page=0&refinements=%5B%7B%22categories%22%3A%22' . addslashes($path) . '%22%7D%5D&numerics_refinements=%7B%7D&index_name=%22' . $this->algolia_registry->index_name . 'all_' . $current_language . '%22';

        $this->module->getContext()->smarty->assign(array(
            'algolia_results_url' => $url
        ));

        $valid_facets = $this->module->getCategoryFacets((int) Tools::getValue('id_category'));

        // $top_category_content = Hook::exec('displayCategoryTop');
        // $top_category_content = str_replace(PHP_EOL, '', addslashes($top_category_content));

        Media::AddJSDef(array('algolia_results_url' => $url, 'valid_facets' => $valid_facets));

        // header('Location: '.$url);
        // die();
    }

    public function hookActionProductAdd($params) {
        $this->indexer->indexProduct($params['product']);
    }

    public function hookActionProductUpdate($params) {
        $this->indexer->indexProduct($params['product']);
    }

    public function hookCustomProductUpdate($product) {
        $this->indexer->indexProduct($product);
    }

    public function hookActionProductDelete($params) {
        $this->indexer->deleteProduct($params['product']->id);
    }

    public function hookDisplayBackOfficeHeader() {
        $this->module->getContext()->controller->addCSS($this->module->getPath() . 'css/dashboard.css');
        if (strcmp(Tools::getValue('configure'), $this->module->name) === 0)
            $this->module->getContext()->controller->addCSS($this->module->getPath() . 'css/configure.css');
    }

    public function hookDisplayFooter() {

        $path = $this->module->getPath();

        include __DIR__ . '/../../themes/' . $this->algolia_registry->theme . '/templates.php';
    }

    public function hookAssignProductList() {
        $hookExecuted = false;
        Hook::exec('actionProductListOverride', array(
            'nbProducts' => &$this->nbProducts,
            'catProducts' => &$this->cat_products,
            'hookExecuted' => &$hookExecuted,
        ));

        // The hook was not executed, standard working
        if (!$hookExecuted) {
            $this->context->smarty->assign('categoryNameComplement', '');
            if (!$this->algolia_registry->replace_categories) {
                $this->nbProducts = $this->category->getProducts(null, null, null, $this->orderBy, $this->orderWay, true);
                $this->pagination((int) $this->nbProducts); // Pagination must be call after "getProducts"
                $this->cat_products = $this->category->getProducts($this->context->language->id, (int) $this->p, (int) $this->n, $this->orderBy, $this->orderWay);
            }
        }
        // Hook executed, use the override
        else
        // Pagination must be call after "getProducts"
            $this->pagination($this->nbProducts);

        Hook::exec('actionProductListModifier', array(
            'nb_products' => &$this->nbProducts,
            'cat_products' => &$this->cat_products,
        ));

        foreach ($this->cat_products as &$product)
            if ($product['id_product_attribute'] && isset($product['product_attribute_minimal_quantity']))
                $product['minimal_quantity'] = $product['product_attribute_minimal_quantity'];

        $this->addColorsToProductList($this->cat_products);

        $this->context->smarty->assign('nb_products', $this->nbProducts);
    }

}
