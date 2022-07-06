{capture name=path}
    {if $seo_breadcrumb}
        {foreach from=$seo_breadcrumb key=seo_key item=seo_item name=seo_item}
            {assign var=label value=$seo_breadcrumb[$seo_key]["label"]}
            {if is_array($seo_breadcrumb[$seo_key]["label"]) AND sizeof($seo_breadcrumb[$seo_key]["label"])}
                {assign var=label value=$seo_breadcrumb[$seo_key]["label"][0]}
            {/if}
            <li class="breadcrumb-item"><i class="fa fa-angle-right" aria-hidden="true"></i></li>
            <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                {if $seo_breadcrumb[$seo_key]["path"]}
                <a href="{$seo_breadcrumb[$seo_key]["path"]}" itemprop="item" class="black-text">
                    {/if}
                    <span itemprop="name">{$label}</span>
                    {if $seo_breadcrumb[$seo_key]["path"]}
                </a>
                {/if}
            </li>
        {/foreach}
    {/if}
{/capture}