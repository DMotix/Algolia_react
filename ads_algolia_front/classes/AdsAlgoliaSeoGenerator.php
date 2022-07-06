<?php

class AdsAlgoliaSeoGenerator
{
    public $product_object;
    public $massive_seo;
    public $combination;
    public $combination_src;
    const SEO_DELIMITOR = "/";


    public function shortTagConverterSimple($short_tag, $return_array_value = true)
    {
        //delete all {} from string
        $short_tag_tmp = preg_replace('/[{}]/', '', $short_tag);
        $short_tag_sanitize_arr = explode(".", $short_tag_tmp);
        $return_value = $this->product_object;
        foreach ($short_tag_sanitize_arr as $prop) {
            $return_value = $return_value->{$prop};
        }
        if ($return_array_value && !is_array($return_value))
            $return_value = array($return_value);
        return $return_value;
    }


    public function shortTagConverterComplex($string)
    {
        //delete all {} from string
        preg_match_all('/{(.*?)}/s', $string, $matches, PREG_SET_ORDER);
        if (empty($matches))
            return $string;
        $array_mapped_values = array();
        foreach ($matches as $key => $match) {
            $short_tag_sanitize_arr = explode(".", $match[1]);
            $return_value = $this->product_object;
            foreach ($short_tag_sanitize_arr as $prop) {
                $return_value = $return_value->{$prop};
            }
            $array_mapped_values[$match[0]] = $return_value;
        }
        //Replace of all occurrences of tags in string given
        return strtr($string, $array_mapped_values);
    }

    public function get_combinations($arrays)
    {
        $result = array(array());
        foreach ($arrays as $property => $property_values) {
            $tmp = array();
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, array($property => $property_value));
                }
            }
            $result = $tmp;
        }
        return $result;
    }

    public function getAllUrlsSeoCombinations()
    {
        $criterias_current = explode(self::SEO_DELIMITOR, $this->massive_seo["criteria"]);
        $combinations_original = array();
        $combinations_custom = array();
        foreach ($criterias_current as $criteria_current) {
            $combinations_original[$criteria_current] = $this->shortTagConverterSimple($criteria_current);
            $combinations_custom[$criteria_current] = $this->shortTagConverterSimple($criteria_current);
            //Exception for is_vo
            if ($criteria_current == "{is_vo}") {
                unset($combinations_custom[$criteria_current]);
                $combinations_custom["{category}"] = $this->shortTagConverterSimple("{category}");
            }
        }
        //Build final $combinations
        $combinations = new stdClass();
        $combinations->original = $this->get_combinations($combinations_original);
        $combinations->custom = $this->get_combinations($combinations_custom);
        return $combinations;
    }

    public function link_rewrite($item)
    {
        return Tools::link_rewrite($item);
    }

    public function tag_converter($item)
    {
        //first step replace with combination first
        $search = array_keys($this->combination);
        $replace = array_values($this->combination);
        if (is_string($item))
            $item = str_replace($search, $replace, $item);

        //second step replace all tags missing
        $item = $this->shortTagConverterComplex($item);
        return $item;
    }

    public function criteriaGenerator()
    {
        $criteria = new stdClass();
        $criteria->aglDFR = new stdClass();
        $criteria->aglNR = new stdClass();
        foreach ($this->combination_src as $key => $value) {
            $key = preg_replace('/[{}]/', '', $key);
            $criteria->aglDFR->$key = array($value);
        }
        return base64_encode(serialize($criteria));
    }

    public function generateSeoPage()
    {
        //Criterias
        $seo_pages = array();
        $all_combinations = $this->getAllUrlsSeoCombinations();
        foreach ($all_combinations->custom as $key => $combination) {
            $this->combination = $combination;
            $this->combination_src = $all_combinations->original[$key];
            $seo_url = implode(self::SEO_DELIMITOR, array_map(array($this, 'link_rewrite'), $combination));
            $seo_pages[$seo_url] = array(
                "meta_title" => $this->massive_seo["meta_title"],
                "meta_description" => $this->massive_seo["meta_description"],
                "meta_keywords" => $this->massive_seo["meta_keywords"],
                "title" => $this->massive_seo["title"],
                "description_top" => $this->massive_seo["description_top"],
                "description_footer" => $this->massive_seo["description_footer"],
                "criteria" => $this->criteriaGenerator(),
                "seo_url" => $seo_url,
                "auto" => 1,
                "active" => 1,
                "id_supplier" => null
            );
            $seo_pages[$seo_url] = array_map(array($this, 'tag_converter'), $seo_pages[$seo_url]);
        };
        return $seo_pages;
    }
}