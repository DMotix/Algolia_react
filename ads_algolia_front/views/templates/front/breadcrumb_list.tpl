{capture name=path}
    {if isset($seo_breadcrumb) AND $seo_breadcrumb}
        {foreach from=$seo_breadcrumb key=seo_key item=seo_item name=seo_item}
            {assign var=label value=$seo_breadcrumb[$seo_key]["label"]}
            {if is_array($seo_breadcrumb[$seo_key]["label"]) AND sizeof($seo_breadcrumb[$seo_key]["label"])}
                {assign var=label value=$seo_breadcrumb[$seo_key]["label"][0]}
            {/if}
            <li class="breadcrumb-item"><i class="fa fa-angle-right" aria-hidden="true"></i></li>
            <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                <a href="{$seo_breadcrumb[$seo_key]["path"]}" itemprop="item" class="black-text">
                    <span itemprop="name">{$label}</span>
                </a>
            </li>
        {/foreach}
    {else}
        <li class="breadcrumb-item"><i class="fa fa-angle-right" aria-hidden="true"></i></li>
        <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
            <span class="black-text"
                  itemprop="name">{if isset($fils_ariane_listing) && $fils_ariane_listing}{$fils_ariane_listing}{elseif isset($title_h1_listing) && $title_h1_listing}{$title_h1_listing}{else}{l s='Recherche de votre véhicule'}{/if}</span>
        </li>
    {/if}
{/capture}