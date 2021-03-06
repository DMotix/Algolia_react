<?php

namespace Algolia\Core;

class AttributesHelper {

    private $algolia_registry;

    public function __construct() {
        $this->algolia_registry = Registry::getInstance();
    }

    public function getAllAttributes($id_lang) {
        $attributes = array();

        $metas = $this->algolia_registry->metas;

        $type = 'attribute';
        foreach (array_keys(\Algolia\Core\PrestashopFetcher::$attributes) as $defaultAttribute) {
            $id = $defaultAttribute;

            $attributes[$id] = new \stdClass();
            $attributes[$id]->id = 0;
            $attributes[$id]->type = $type;
            $attributes[$id]->name = $defaultAttribute;
            $attributes[$id]->checked = true;
            $attributes[$id]->order = isset($metas[$id]) && isset($metas[$id]['order']) ? $metas[$id]['order'] : 10000;
            $attributes[$id]->facetable = isset($metas[$id]) && isset($metas[$id]['facetable']) ? $metas[$id]['facetable'] : false;
            $attributes[$id]->retrievable = isset($metas[$id]) && isset($metas[$id]['retrievable']) ? $metas[$id]['retrievable'] : true;
            $attributes[$id]->facet_type = isset($metas[$id]) && isset($metas[$id]['type']) ? $metas[$id]['type'] : 'conjunctive';
            $attributes[$id]->collapsed = isset($metas[$id]) && isset($metas[$id]['collapsed']) ? $metas[$id]['collapsed'] : false;
            $attributes[$id]->show_front = isset($metas[$id]) && isset($metas[$id]['show_front']) ? $metas[$id]['show_front'] : false;
            $attributes[$id]->show_home = isset($metas[$id]) && isset($metas[$id]['show_home']) ? $metas[$id]['show_home'] : false;
            $attributes[$id]->label = isset($metas[$id]) && isset($metas[$id]['label']) ? $metas[$id]['label'] : false;
            $attributes[$id]->icon = isset($metas[$id]) && isset($metas[$id]['icon']) ? $metas[$id]['icon'] : false;
            $attributes[$id]->css_class = isset($metas[$id]) && isset($metas[$id]['css_class']) ? $metas[$id]['css_class'] : false;
            $attributes[$id]->hide_css_front = isset($metas[$id]) && isset($metas[$id]['hide_css_front']) ? $metas[$id]['hide_css_front'] : false;
        }

        $type = 'feature';
        foreach (\Feature::getFeatures($id_lang) as $feature) {
            $name = \Tools::slugify($feature['name'], "_");
            if ($name == "marque" || $name == "modele" || $name == "type_de_vehicule") {
                $name = $name . ".name";
            }
            $id = $type . '_' . $feature['id_feature'];
            $id = $name;

            $attributes[$id] = new \stdClass();

            $attributes[$id]->id = $feature['id_feature'];
            $attributes[$id]->type = $type;
            $attributes[$id]->name = $name;
            $attributes[$id]->order = isset($metas[$id]) && isset($metas[$id]['order']) ? $metas[$id]['order'] : 10000;
            $attributes[$id]->checked = isset($metas[$id]) && isset($metas[$id]['indexable']) ? $metas[$id]['indexable'] : false;
            $attributes[$id]->facetable = isset($metas[$id]) && isset($metas[$id]['facetable']) ? $metas[$id]['facetable'] : false;
            $attributes[$id]->retrievable = isset($metas[$id]) && isset($metas[$id]['retrievable']) ? $metas[$id]['retrievable'] : true;
            $attributes[$id]->facet_type = isset($metas[$id]) && isset($metas[$id]['type']) ? $metas[$id]['type'] : 'conjunctive';
            $attributes[$id]->collapsed = isset($metas[$id]) && isset($metas[$id]['collapsed']) ? $metas[$id]['collapsed'] : false;
            $attributes[$id]->show_front = isset($metas[$id]) && isset($metas[$id]['show_front']) ? $metas[$id]['show_front'] : false;
            $attributes[$id]->show_home = isset($metas[$id]) && isset($metas[$id]['show_home']) ? $metas[$id]['show_home'] : false;
            $attributes[$id]->label = isset($metas[$id]) && isset($metas[$id]['label']) ? $metas[$id]['label'] : false;
            $attributes[$id]->icon = isset($metas[$id]) && isset($metas[$id]['icon']) ? $metas[$id]['icon'] : false;
            $attributes[$id]->css_class = isset($metas[$id]) && isset($metas[$id]['css_class']) ? $metas[$id]['css_class'] : false;
            $attributes[$id]->hide_css_front = isset($metas[$id]) && isset($metas[$id]['hide_css_front']) ? $metas[$id]['hide_css_front'] : false;
        }
        foreach (\Feature::getFeatures($id_lang) as $feature) {
            $name = \Tools::slugify($feature['name'], "_");
            $extras_model = array("fullName", "fullNames", "slug");
            $extras_marque = array("slug");
            if ($name == "marque" || $name == "type_de_vehicule") {
                foreach ($extras_marque as $extra) {
                    $id = $type . \Tools::slugify($extra) . '_' . $feature['id_feature'];
                    $newName = $name . "." . $extra;
                    $attributes[$id] = new \stdClass();

                    $attributes[$id]->id = $name;
                    $attributes[$id]->type = $type;
                    $attributes[$id]->name = $newName;
                    $attributes[$id]->order = isset($metas[$id]) && isset($metas[$id]['order']) ? $metas[$id]['order'] : 10000;
                    $attributes[$id]->checked = isset($metas[$id]) && isset($metas[$id]['indexable']) ? $metas[$id]['indexable'] : false;
                    $attributes[$id]->facetable = isset($metas[$id]) && isset($metas[$id]['facetable']) ? $metas[$id]['facetable'] : false;
                    $attributes[$id]->retrievable = isset($metas[$id]) && isset($metas[$id]['retrievable']) ? $metas[$id]['retrievable'] : true;
                    $attributes[$id]->facet_type = isset($metas[$id]) && isset($metas[$id]['type']) ? $metas[$id]['type'] : 'conjunctive';
                    $attributes[$id]->collapsed = isset($metas[$id]) && isset($metas[$id]['collapsed']) ? $metas[$id]['collapsed'] : false;
                    $attributes[$id]->show_front = isset($metas[$id]) && isset($metas[$id]['show_front']) ? $metas[$id]['show_front'] : false;
                    $attributes[$id]->show_home = isset($metas[$id]) && isset($metas[$id]['show_home']) ? $metas[$id]['show_home'] : false;
                    $attributes[$id]->label = isset($metas[$id]) && isset($metas[$id]['label']) ? $metas[$id]['label'] : false;
                    $attributes[$id]->icon = isset($metas[$id]) && isset($metas[$id]['icon']) ? $metas[$id]['icon'] : false;
                    $attributes[$id]->css_class = isset($metas[$id]) && isset($metas[$id]['css_class']) ? $metas[$id]['css_class'] : false;
                    $attributes[$id]->hide_css_front = isset($metas[$id]) && isset($metas[$id]['hide_css_front']) ? $metas[$id]['hide_css_front'] : false;
                }
            }
            if ($name == "modele") {
                foreach ($extras_model as $extra) {
                    $id = $type . \Tools::slugify($extra) . '_' . $feature['id_feature'];
                    $newName = $name . "." . $extra;
                    $attributes[$id] = new \stdClass();

                    $attributes[$id]->id = $name;
                    $attributes[$id]->type = $type;
                    $attributes[$id]->name = $newName;
                    $attributes[$id]->order = isset($metas[$id]) && isset($metas[$id]['order']) ? $metas[$id]['order'] : 10000;
                    $attributes[$id]->checked = isset($metas[$id]) && isset($metas[$id]['indexable']) ? $metas[$id]['indexable'] : false;
                    $attributes[$id]->facetable = isset($metas[$id]) && isset($metas[$id]['facetable']) ? $metas[$id]['facetable'] : false;
                    $attributes[$id]->retrievable = isset($metas[$id]) && isset($metas[$id]['retrievable']) ? $metas[$id]['retrievable'] : true;
                    $attributes[$id]->facet_type = isset($metas[$id]) && isset($metas[$id]['type']) ? $metas[$id]['type'] : 'conjunctive';
                    $attributes[$id]->collapsed = isset($metas[$id]) && isset($metas[$id]['collapsed']) ? $metas[$id]['collapsed'] : false;
                    $attributes[$id]->show_front = isset($metas[$id]) && isset($metas[$id]['show_front']) ? $metas[$id]['show_front'] : false;
                    $attributes[$id]->show_home = isset($metas[$id]) && isset($metas[$id]['show_home']) ? $metas[$id]['show_home'] : false;
                    $attributes[$id]->label = isset($metas[$id]) && isset($metas[$id]['label']) ? $metas[$id]['label'] : false;
                    $attributes[$id]->icon = isset($metas[$id]) && isset($metas[$id]['icon']) ? $metas[$id]['icon'] : false;
                    $attributes[$id]->css_class = isset($metas[$id]) && isset($metas[$id]['css_class']) ? $metas[$id]['css_class'] : false;
                    $attributes[$id]->hide_css_front = isset($metas[$id]) && isset($metas[$id]['hide_css_front']) ? $metas[$id]['hide_css_front'] : false;
                }
            }
        }

        $type = 'group';
        foreach ($this->getAttributes($id_lang) as $attribute) {
            $id = $type . '_' . $attribute['id'];

            $attributes[$id] = new \stdClass();

            $attributes[$id]->id = $attribute['id'];
            $attributes[$id]->type = $type;
            $attributes[$id]->name = $attribute['attribute_group'];
            $attributes[$id]->order = isset($metas[$id]) && isset($metas[$id]['order']) ? $metas[$id]['order'] : 10000;
            $attributes[$id]->checked = isset($metas[$id]) && isset($metas[$id]['indexable']) ? $metas[$id]['indexable'] : false;
            $attributes[$id]->facetable = isset($metas[$id]) && isset($metas[$id]['facetable']) ? $metas[$id]['facetable'] : false;
            $attributes[$id]->retrievable = isset($metas[$id]) && isset($metas[$id]['retrievable']) ? $metas[$id]['retrievable'] : true;
            $attributes[$id]->facet_type = isset($metas[$id]) && isset($metas[$id]['type']) ? $metas[$id]['type'] : 'conjunctive';
            $attributes[$id]->collapsed = isset($metas[$id]) && isset($metas[$id]['collapsed']) ? $metas[$id]['collapsed'] : false;
            $attributes[$id]->show_front = isset($metas[$id]) && isset($metas[$id]['show_front']) ? $metas[$id]['show_front'] : false;
            $attributes[$id]->show_home = isset($metas[$id]) && isset($metas[$id]['show_home']) ? $metas[$id]['show_home'] : false;
            $attributes[$id]->label = isset($metas[$id]) && isset($metas[$id]['label']) ? $metas[$id]['label'] : false;
            $attributes[$id]->icon = isset($metas[$id]) && isset($metas[$id]['icon']) ? $metas[$id]['icon'] : false;
            $attributes[$id]->css_class = isset($metas[$id]) && isset($metas[$id]['css_class']) ? $metas[$id]['css_class'] : false;
            $attributes[$id]->hide_css_front = isset($metas[$id]) && isset($metas[$id]['hide_css_front']) ? $metas[$id]['hide_css_front'] : false;
        }


        return $attributes;
    }

    private function getAttributes($id_lang) {
        return \Db::getInstance()->executeS('
            SELECT DISTINCT agl.`id_attribute_group` as `id`, agl.`name` AS `attribute_group`
            FROM `' . _DB_PREFIX_ . 'attribute_group` ag
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl
                ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $id_lang . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a
                ON a.`id_attribute_group` = ag.`id_attribute_group`
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al
                ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) $id_lang . ')
            ' . \Shop::addSqlAssociation('attribute_group', 'ag') . '
            ' . \Shop::addSqlAssociation('attribute', 'a') . '
            ' . (false ? 'WHERE a.`id_attribute` IS NOT NULL AND al.`name` IS NOT NULL AND agl.`id_attribute_group` IS NOT NULL' : '') . '
            ORDER BY agl.`name` ASC, a.`position` ASC
        ');
    }

    public function getSearchableAttributes($id_lang) {
        $searchable = array();

        foreach ($this->getAllAttributes($id_lang) as $key => $value)
            if ($value->checked)
                $searchable[$key] = $value->name;

        return $searchable;
    }

}
