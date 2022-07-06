{* BTN POUR MODULE ALGOLIA BACK OFFICE*}
{if $cookie->id_employee}
    <a href="#" class="btn-copy-seo btn btn-large w-100 mx-auto"
       id="getdisjunctiveFacetsRefinements" data-clipboard-text="#">{l s='Copier la recherche' mod='ads_algolia_front'}
        <i
                class="fa fa-save ml-1"></i></a>
{/if}
<div id="search-algolia">
    {if Configuration::get('PS_MS_STORE_LOCATOR') && Context::getContext()->cookie->__isset('store_locator')}
        <div class="full_stock_container form-check p-0 text-center">
            <input type="checkbox" name="display_full_stock" class="form-check-input" id="display_full_stock"/>
            <label class="form-check-label"
                   for="display_full_stock">{l s='Afficher tout le stock' mod='ads_algolia_front'}</label>
        </div>
    {/if}
    {foreach from=$agl_facets item=facet}
        {if $facet["type"] == "conjunctive"}
            <div id="{$facet["id"]}"
                 class="{if !$cookie->id_employee && $facet["hide_css_front"]}hidden {/if}{if $facet["collapsed"]}auto-collapsed {/if}clearfix ais-refinement-list--item__block {if $facet["css_class"]}{$facet["css_class"]}{/if}"></div>
        {elseif $facet["type"] == "slider"}
            <div class="{if !$cookie->id_employee && $facet["hide_css_front"]}hidden {/if}{$facet["id"]}__slider ion-slider-wrapper clearfix {if $facet["css_class"]}{$facet["css_class"]}{/if}">
                <div data-target="#collapse{$facet["id"]}" data-toggle="collapse"
                     class="{if $facet["collapsed"]}collapsed {/if}ais-refinement-list--header ais-header"
                     {if $facet["collapsed"]}aria-expanded="false"{/if}>
                    <header class="collapsible-header">{$facet["name"]}<span class="right truncate"></span><i
                                class="fa fa-minus-circle close-header-item"></i><i
                                class="fa fa-plus-circle open-header-item"></i></header>
                </div>
                <div class="in collapse {if !$facet["collapsed"]}show{/if}" id="collapse{$facet["id"]}">
                    <input type="text" id="{$facet["id"]}" name="slider-{$facet["id"]}" value=""/>
                </div>
            </div>
        {/if}
    {/foreach}
</div>
<script type="text/html" id="item-filter-template">
    {literal}
        <div class="form-check p-0">
            <input type="checkbox" class="form-check-input filled-in ais-refinement-list--checkbox"
                   value="{{ label }}" {{#isRefined}} checked {{/isRefined}}>
            <label class="form-check-label ais-refinement-list--label d-block">
                {{ label }}
                <span class="ais-refinement-list--count">{{ count }}</span>
            </label>
        </div>
    {/literal}
</script>