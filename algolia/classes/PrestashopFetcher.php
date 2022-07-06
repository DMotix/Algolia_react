<?php

namespace Algolia\Core;

class PrestashopFetcher
{
    private static $instance;
    static $attributes = array(
        "name" => null,
        "reference" => "getTrimmedRef",
        'on_sale' => null,
        "available_now" => null,
        "category" => "getCategoryName",
        "categories" => "getCategoriesNames",
        "manufacturer" => "getManufacturerName",
        "categories_without_path" => "getCategoriesNamesWithoutPath",
        "date_add" => null,
        "date_upd" => null,
        "description" => null,
        "description_short" => null,
        "ean13" => null,
        "image_link_large" => "generateImageLinkLarge",
        "image_link_medium" => "generateImageLinkMedium",
        //"image_link_small" => "generateImageLinkSmall",
        "link" => "generateLinkRewrite",
        // "name"                      => null,
        "price" => null,
        "old_price_tax_incl" => "getOldPriceTaxIncl",
        "old_price_tax_excl" => "getOldPriceTaxExcl",
        "price_tax_incl" => "getPriceTaxIncl",
        "price_tax_excl" => "getPriceTaxExcl",
        "is_promo" => "getIsPromo",
        "reduction_tax_incl" => "getReductionTaxIncl",
        "reduction_tax_excl" => "getReductionTaxExcl",
        "monthly_payment" => "getMonthlyPayment",
        "monthly_payment.amount" => "getMonthlyPaymentAmount",
        // "reference"                 => "getTrimmedRef",
        "supplier" => "getSupplier",
        "supplier.name" => "getSupplierName",
        "supplier.city" => "getSupplierCity",
        'ordered_qty' => 'getOrderedQty',
        'stock_qty' => 'getStockQty',
        'condition' => null,
        'weight' => null,
        'pageviews' => 'getPageViews',
        'sales' => 'getSales',
        "remise" => "getRemise",
        "is_vo" => "getIsVO",
        "is_vu" => "getIsVU",
        "number_images" => "getNumberImages",
        "show_price" => "getShowPrice",
        "is_ht" => "getIsHT",
        "price_empty" => "getPriceIsEmpty",
        "currency_iso_code" => "getIsoCurrency",
        "eco_prime" => "getEcoPrime",
        "energy_class" => "getEnergyClass",
        "_tags" => "getTags",
        "categorization" => "getCategorization",
        "vd" => null
    );
    private $product_definition = false;
    private $reduction_price = 0;

    public static function getInstance()
    {
        if (!isset(static::$instance))
            static::$instance = new self();

        return static::$instance;
    }

    public function __construct()
    {
        $this->product_definition = \Product::$definition['fields'];
    }

    public function getProductObj($id_product, $language)
    {
        return (array)$this->initProduct($id_product, $language);
    }

    private function try_cast($value)
    {
        if (is_numeric($value) && floatval($value) == floatval(intval($value)))
            return intval($value);

        if (is_numeric($value))
            return floatval($value);

        return $value;
    }

    private function initProduct($id_product, $language)
    {
        $product = new \stdClass();
        $ps_product = new \Product($id_product);

        /* Required by Algolia */
        $product->objectID = $ps_product->id;

        /** Default Attribute * */
        foreach (static::$attributes as $key => $value) {
            if ($value != null && method_exists($this, $value)) {
                $product->$key = $this->$value($product, $ps_product, $language['id_lang'], $language['iso_code']);
                continue;
            }

            if (isset($this->product_definition[$key]["lang"]) == true)
                $product->$key = $ps_product->{$key}[$language['id_lang']];
            else
                $product->$key = $ps_product->{$key};
        }

        /** Features * */
        $maker_name = strtoupper($product->manufacturer);
        foreach ($ps_product->getFrontFeatures($language['id_lang']) as $feature) {
            $name = \Tools::slugify($feature['name'], "_");
            $value = $feature['value'];

            $product->$name = $value;

            if ($name == "type_de_vehicule") {
                $product->{"type_de_vehicule"} = array(
                    "name" => $value,
                    "slug" => \Tools::link_rewrite($value)
                );
            }

            if ($name == "marque") {
                $product->{"marque"} = array(
                    "name" => $maker_name,
                    "slug" => \Tools::link_rewrite($value)
                );
            }

            if ($name == "modele") {
                $model = $value;
                $product->{"modele"} = array(
                    "name" => $value,
                    "slug" => \Tools::link_rewrite($value),
                    "fullName" => $maker_name . " > " . $value,
                    "fullNames" => array(
                        $maker_name . " > Tous les modèles",
                        $maker_name . " > " . $value
                    )
                );
            }
        }

        /** Equipements * */
        $equipements_with_name = \Product::getFrontEquipementsStatic($language['id_lang'], $ps_product->id);
        /** SLICED ARRAY DUE TO FATAL ERROR WITH ALGOLIA IF TOO LONG * */
        $equipements_with_name = array_slice($equipements_with_name, 0, 100);
        $product->equipements = $equipements_with_name;

        /** Shop association * */
        $shops_association = array();
        $id_shop_group = \Context::getContext()->shop->id_shop_group;
        foreach (\Shop::getShops(false, $id_shop_group, true) as $id_shop) {
            $sql = 'SELECT `active` FROM `' . _DB_PREFIX_ . 'product_shop` WHERE `id_product` = ' . (int)$id_product . ' AND `id_shop` = ' . (int)$id_shop;
            $res = \Db::getInstance()->getRow($sql);
            $shops_association["shop_" . (int)$id_shop] = (int)$res["active"];
        }
        $product->shops_association = $shops_association;

        /** Attribute groups * */
        foreach ($ps_product->getAttributesGroups($language['id_lang']) as $attribute) {
            if (isset($product->{$attribute['group_name']}) == false)
                $product->{$attribute['group_name']} = array();

            if (in_array($attribute['attribute_name'], $product->{$attribute['group_name']}) == false)
                $product->{$attribute['group_name']}[] = $attribute['attribute_name'];
        }

        /** Casting * */
        foreach ($product as $key => &$value)
            $value = $this->try_cast($value);

        return $product;
    }

    /**
     * GETTERS
     */
    private function getStockQty($product, $ps_product)
    {
        return \Product::getQuantity($ps_product->id);
    }

    private function getOrderedQty($product, $ps_product)
    {
        $product_sold = \Db::getInstance()->getRow('SELECT SUM(product_quantity) as total FROM `' . _DB_PREFIX_ . 'order_detail` where product_id = ' . $ps_product->id);

        return $product_sold['total'];
    }

    private function getOldPriceTaxExcl($product, $ps_product)
    {
        return \Product::getPriceStatic($ps_product->id, false, null, 0, null, false, false);
    }

    private function getOldPriceTaxIncl($product, $ps_product)
    {
        return \Product::getPriceStatic($ps_product->id, true, null, 0, null, false, false);
    }

    private function getPriceTaxExcl($product, $ps_product)
    {
        return \Product::getPriceStatic($ps_product->id, false, null, 0);
    }

    private function getPriceTaxIncl($product, $ps_product)
    {
        return \Product::getPriceStatic($ps_product->id, true, null, 0);
    }

    public function getIsPromo($product, $ps_product)
    {
        $currency_list = \Currency::getCurrencies(false, 1, new \Shop((int)\Context::getContext()->shop->id));
        if (isset($currency_list) && is_array($currency_list) && count($currency_list) > 0) {
            foreach ($currency_list as $currency) {
                $this->reduction_price = \Product::priceCalculation((int)\Context::getContext()->shop->id, (int)$ps_product->id, null, null, null, null, $currency['id_currency'], null, null, true, 6, true, true, true, $specific_price_output, true);
                break;
            }
        }

        return ($this->reduction_price != 0 ? true : false);
    }

    public function getReductionTaxIncl($product, $ps_product)
    {
        return $this->reduction_price;
    }

    public function getReductionTaxExcl($product, $ps_product)
    {
        $tax = new \Tax(1); //par défaut à 20%
        $tax_calculator = new \TaxCalculator(array($tax));
        return ($this->reduction_price != 0 ? $tax_calculator->removeTaxes($this->reduction_price) : 0);
    }

    private function getMonthlyPayment($product, $ps_product)
    {
        $obj = new \stdClass;
        $obj->amount = \Tools::ps_round((float)$ps_product->mensualite, 0);

        if (!\Configuration::get("PS_GS_FINANCEMENT_ENABLED"))
            $obj->amount = 0;

        return ($obj->amount > 0 ? $obj : null);
    }

    private function getMonthlyPaymentAmount($product, $ps_product)
    {
        $res = $this->getMonthlyPayment($product, $ps_product);
        return (is_object($res) ? $res->amount : null);
    }

    private function generateImageLinkLarge($product, $ps_product, $id_lang)
    {
        $link = new \Link();
        $cover = \Image::getCover($ps_product->id);

        if ($cover['id_image'])
            $image_link = $link->getImageLink($ps_product->link_rewrite[$id_lang], $cover["id_image"], \ImageType::getFormatedName("large")); // nemo
        else
            $image_link = _THEME_PROD_DIR_ . 'fr-default-large_default.jpg';

        return $image_link;
    }

    private function generateImageLinkMedium($product, $ps_product, $id_lang)
    {
        $link = new \Link();
        $cover = \Image::getCover($ps_product->id);

        if ($cover['id_image'])
            $image_link = $link->getImageLink($ps_product->link_rewrite[$id_lang], $cover["id_image"], \ImageType::getFormatedName("medium")); // nemo
        else
            $image_link = _THEME_PROD_DIR_ . 'fr-default-medium_default.jpg';

        return $image_link;
    }

    private function generateImageLinkSmall($product, $ps_product, $id_lang)
    {
        $link = new \Link();
        $cover = \Image::getCover($ps_product->id);

        if ($cover['id_image'])
            $image_link = $link->getImageLink($ps_product->link_rewrite[$id_lang], $cover["id_image"], \ImageType::getFormatedName("small")); // nemo
        else
            $image_link = \Tools::getHttpHost(true) . _THEME_PROD_DIR_ . 'fr-default-small_default.jpg';

        return $image_link;
    }

    private function generateLinkRewrite($product, $ps_product, $id_lang)
    {
        $link = new \Link();
        if (\Shop::isFeatureActive())
            $link->setBaseUrlEnable(false);
        $product_link = $link->getProductLink($ps_product->id, $ps_product->link_rewrite[$id_lang], null, null, $id_lang);
        return $product_link;
    }

    private function getCategoryName($product, $ps_product, $id_lang)
    {
        $category = new \Category($ps_product->id_category_default, $id_lang);

        return $category->name;
    }

    private function getNestedCatsWithoutPath($cats, &$results, $id_lang)
    {
        foreach ($cats as $cat) {
            if (isset($cat['children']) && is_array($cat['children']) && count($cat['children']) > 0) {
                $this->getNestedCatsWithoutPath($cat['children'], $results, $id_lang);
            } else {
                if ($cat['is_root_category'] == 0)
                    $results[] = $cat['name'];
            }
        }
    }

    private function getCategoryPath($category_id)
    {
        $cats = array();
        $context = \Context::getContext();
        $interval = \Category::getInterval($category_id);
        $id_root_category = $context->shop->getCategory();

        if ($interval) {
            $sql = 'SELECT c.*, cl.*
                        FROM ' . _DB_PREFIX_ . 'category c
                        LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON c.`id_category` = cl.`id_category`' . \Shop::addSqlRestrictionOnLang('cl') . '
                        ' . \Shop::addSqlAssociation('category', 'c') . '
                        WHERE c.nleft <= ' . $interval['nleft'] . '
                            AND c.nright >= ' . $interval['nright'] . '
                            AND cl.id_lang = ' . (int)$context->language->id . '
                            AND c.active = 1
                        ORDER BY c.level_depth ASC';

            $categories = \Db::getInstance()->executeS($sql);

            foreach ($categories as $category) {
                $cats[] = $category;
            }
        }

        return $cats;
    }

    private function getNestedCategoriesData($id_lang, $ps_product)
    {
        $cats = \Db::getInstance()->executeS('
                SELECT c.*, cl.*
                FROM `' . _DB_PREFIX_ . 'category` c
                ' . \Shop::addSqlAssociation('category', 'c') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON c.`id_category` = cl.`id_category`' . \Shop::addSqlRestrictionOnLang('cl') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'category_product` cp ON cp.`id_product` = ' . $ps_product->id . '
                WHERE 1 AND `id_lang` = ' . (int)$id_lang . ' AND c.`active` = 1
                AND cp.`id_category` = c.`id_category`
                ORDER BY c.`level_depth` ASC, category_shop.`position` ASC'
        );

        $categories = array();
        $buff = array();

        if (!isset($root_category))
            $root_category = \Category::getRootCategory()->id;

        foreach ($cats as $row)
            foreach ($this->getCategoryPath($row['id_category']) as $other)
                $cats[] = $other;

        $cats = array_intersect_key($cats, array_unique(array_map('serialize', $cats)));

        usort($cats, function ($a, $b) {
            if ($a['level_depth'] < $b['level_depth'])
                return -1;
            if ($a['level_depth'] > $b['level_depth'])
                return 1;
            return 0;
        });

        foreach ($cats as $row) {
            $current = &$buff[$row['id_category']];
            $current = $row;

            if ($row['id_category'] == $root_category)
                $categories[$row['id_category']] = &$current;
            else
                $buff[$row['id_parent']]['children'][$row['id_category']] = &$current;
        }

        return $categories;
    }

    private function getCategoriesNamesWithoutPath($product, $ps_product, $id_lang)
    {
        $categories = $this->getNestedCategoriesData($id_lang, $ps_product);

        $results = array();
        $this->getNestedCatsWithoutPath($categories, $results, $id_lang);

        //We delete main category from categories
        $category_default = array($this->getCategoryName($product, $ps_product, $id_lang));
        $results = array_diff($results, $category_default);
        $results = array_values($results);

        return $results;
    }

    public function getNestedCats($key, $value, $prefix, &$solution)
    {
        if (is_string($key) && $key == "name") {

            if ($value == 'Home')
                return;

            if (empty($prefix)) {
                $prefix .= "$value";
            } else {
                $prefix .= " /// $value";
            }

            array_push($solution, $prefix);
            return $prefix;
        } else { // $key is numeric or children and value is an array
            $p = $prefix;
            if (is_numeric($key) || $key == 'children')
                foreach ($value as $k => $v) {
                    $prefix = $this->getNestedCats($k, $v, $prefix, $solution);
                }
            return $p;
        }
    }

    // private function getNestedCats($cats, $names, &$results, $id_lang)
    // {
    //     // foreach ($cats as $cat)
    //     // {
    //     // }
    //     // // try to return a string with all subcats
    //     // foreach ($cats as $cat)
    //     // {
    //     //     if (isset($cat['children']) && is_array($cat['children']) && count($cat['children']) > 0)
    //     //     {
    //     //         if ($cat['is_root_category'] == 0)
    //     //             $names[] = $cat['name'];
    //     //         $this->getNestedCats($cat['children'], $names, $results, $id_lang);
    //     //     }
    //     //     else
    //     //     {
    //     //         if ($cat['is_root_category'] == 0)
    //     //             $names[] = $cat['name'];
    //     //         $results[] = $names;
    //     //         // array_pop($names);
    //     //     }
    //     // }
    // }

    private function getCategoriesNames($product, $ps_product, $id_lang)
    {
        $categories = $this->getNestedCategoriesData($id_lang, $ps_product);

        $results = array();

        $this->getNestedCats(0, $categories[2], '', $results);

        $final_results = array();

        foreach ($results as $key => $value) {
            $final_results["$key"] = $value;
        }

        // var_dump($final_results);
        // var_dump($results);
        // $this->getNestedCats($categories, array(), $results, $id_lang);
        // // nemo here
        // foreach ($categories as $cat)
        // {
        //     if ($cat['is_root_category'] == 0)
        //         $results[] = array($cat['name']);
        //     if (isset($cat['children']) && is_array($cat['children']) && count($cat['children']) > 0)
        //     {
        //         if ($cat['is_root_category'] == 0)
        //             $names[] = $cat['name'];
        //         $names = $this->getNestedCats($cat['children'], $names, $id_lang);
        //         $results[] = $names;
        //     }
        // }
        // foreach ($results as $result)
        // {
        //     for ($i = count($result) - 1; $i > 0; $i--)
        //     {
        //         $results[] = array_slice($result, 0, $i);
        //     }
        // }
        // $results = array_intersect_key($results, array_unique(array_map('serialize', $results)));
        // foreach ($results as &$result)
        //     $result = implode(' /// ', $result);


        return $final_results;
    }

    private function getManufacturerName($product, $ps_product)
    {
        // var_dump($ps_product->id);
        // var_dump(Manufacturer::getNameById((int)$ps_product->id_manufacturer));
        // var_dump('ciao');
        // die();
        return \Manufacturer::getNameById((int)$ps_product->id_manufacturer);
    }

    private function getSupplier($product, $ps_product)
    {
        $obj = new \stdClass;
        $obj->name = \Supplier::getNameById((int)$ps_product->id_supplier);
        $supplier_infos = \Supplier::getInfosSupplier((int)$ps_product->id_supplier);
        $obj->id = (int)$supplier_infos['id_supplier'];
        $obj->code = $supplier_infos['code_supplier'];
        $obj->zipcode = $supplier_infos['code_postal'];
        $obj->city = $supplier_infos['ville'];

        return $obj;
    }

    private function getSupplierName($product, $ps_product)
    {
        $res = $this->getSupplier($product, $ps_product);
        return $res->name;
    }

    private function getSupplierCity($product, $ps_product)
    {
        $res = $this->getSupplier($product, $ps_product);
        return $res->city;
    }

    // by nemo
    private function getTrimmedRef($product, $ps_product)
    {
        return "_" . $ps_product->reference;
    }

    // nemo again
    public function getPageViews($product, $ps_product)
    {
        $sql = 'SELECT IFNULL(SUM(pv.counter), 0)
                FROM ' . _DB_PREFIX_ . 'page pa
                LEFT JOIN ' . _DB_PREFIX_ . 'page_viewed pv ON pa.id_page = pv.id_page
                WHERE pa.id_object = ' . $ps_product->id . ' AND pa.id_page_type = ' . (int)\Page::getPageTypeByName('product');

        return \Db::getInstance()->getValue($sql);
    }

    public function getSales($product, $ps_product)
    {
        $sales = \ProductSale::getNbrSales($ps_product->id);

        return $sales > 0 ? $sales : 0;
    }

    public function getRemise($product, $ps_product, $id_lang)
    {
        $price_remise = $ps_product->getPrice(true, NULL);
        $price_not_remise = $ps_product->getPriceWithoutReduct(false, NULL);
        $remise = 0;

        if ($price_remise != NULL && $price_remise != 0 && $price_not_remise != NULL && $price_not_remise != 0) {
            $remise = \Tools::ps_round((100 * ($price_not_remise - $price_remise)) / $price_not_remise, 0); //on calcul la remise
        }

        return $remise > 0 ? $remise : null;
    }

    public function getIsVO($product, $ps_product, $id_lang)
    {
        return ($ps_product->type_vehicule == "VO" ? true : false);
    }

    public function getEcoPrime($product, $ps_product)
    {
        return ($ps_product->eco_prime == 1 ? 1 : null);
    }

    public function getEnergyClass($product, $ps_product, $id_lang)
    {
        return ($ps_product->getEnergyClass($id_lang) !== false ? $ps_product->getEnergyClass($id_lang) : null);
    }

    public function getTags($product, $ps_product, $id_lang)
    {
        $tags = \Tag::getProductTags($ps_product->id);
        $results = array();
        if (!$tags)
            return null;
        foreach ($tags as $key => $tags_tab) {
            if ($key == $id_lang)
                foreach ($tags_tab as $tag) {
                    $results[] = $tag;
                }
        }
        return ($results ? $results : null);
    }

    public function getCategorization($product, $ps_product, $id_lang)
    {
        $tags = \Tag::getProductTags($ps_product->id, true);
        if (!$tags)
            return null;
        foreach ($tags as $key => $tags_tab) {
            if ($key == $id_lang)
                foreach ($tags_tab as $tag) {
                    $results[] = $tag;
                }
        }
        if (!$results)
            return null;
        
        $categorization = array(
            "name" => array_values($results)[0],
            "slug" => \Tools::slugify(array_values($results)[0])
        );
        
        return $categorization;        
    }

    public function getIsVU($product, $ps_product, $id_lang)
    {
        $is_vu = "";
        foreach ($ps_product->getFrontFeatures($id_lang) as $feature) {
            $name = \Tools::slugify($feature['name'], "_");
            $value = $feature['value'];
            if ($name == "type") {
                $is_vu = $value;
            }
        }

        return ($is_vu == "VU" ? true : false);
    }

    public function getNumberImages($product, $ps_product, $id_lang)
    {
        return count(\Image::getImages($id_lang, $ps_product->id));
    }

    public function getShowPrice($product, $ps_product, $id_lang)
    {
        return $ps_product->show_price;
    }

    public function getIsHT($product, $ps_product, $id_lang)
    {
        $method_display_price = \Product::getTaxCalculationMethod((int)\Context::getContext()->cookie->id_customer);
        if ($this->getIsVU($product, $ps_product, $id_lang))
            $method_display_price = 1;
        return ($method_display_price == 1 ? true : false);
    }

    public function getPriceIsEmpty($product, $ps_product, $id_lang)
    {
        $price_HT = $this->getPriceTaxExcl($product, $ps_product);
        $price_TTC = $this->getPriceTaxIncl($product, $ps_product);

        return ($price_HT == 0 && $price_TTC == 0 ? true : false);
    }

    public function getIsoCurrency($product, $ps_product, $id_lang)
    {
        $currency = \Currency::getCurrencyInstance(\Configuration::get('PS_CURRENCY_DEFAULT'));

        return $currency->iso_code;
    }

}
