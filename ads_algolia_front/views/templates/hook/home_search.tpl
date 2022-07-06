<section id="search-home" class="color-block" data-bgactive="{if $home_search_bg}1{else}0{/if}"
         {if $home_search_bg}style="background-image:url({$home_search_bg})"{/if}>
    <div class="hero-zone__content px-5 py-4 mx-auto">
        {* TITLE *}
        <div class="container_title_search_home">
            <h2 class="d-none d-md-block">{if isset($home_search_title) && $home_search_title}{$home_search_title}{else}{l s='Acheter sa voiture avec ' mod='ads_algolia_front'}{$shop_name}{/if}</h2>
            <h3 class="d-none d-md-block">
                <div class="stats-container"></div>
            </h3>
            {* CURRENT REFINED VALUES *}
            <div class="d-none d-md-flex justify-content-start container_critere_selected">
                <div id="clear-all" class="pr-md-3"></div>
                <div class="container_refined_values" id="current-refined-values"></div>
            </div>
            {* SEARCH BAR *}
        </div>
        <div class="container_critere_search_home p-0">
            <form id="search-home-section" action="{$path_to_list}" class="form-inline md-form mb-3">
                <div class="group-form group-form--search-home d-flex justify-content-center w-100">
                    <input type="search" id="search-home-input"
                           class="form-control input-alternate mr-sm-3 autocomplete-search" value=""
                           placeholder="{l s='Recherche par marque et/ou modèle' mod='ads_algolia_front'}"
                           autocomplete="off" name="q">
                    <button type="submit" class="btn btn-search-home ml-3 waves-effect waves-light{if (Configuration::get("PS_ADS_ALGOLIA_FRONT_HOME_SMALLBTN"))} btn-search-small{/if}">
                        {if (Configuration::get("PS_ADS_ALGOLIA_FRONT_HOME_SMALLBTN"))}
                            <span class="d-none d-md-block"><i
                                        class="icon-magnifier"></i></span>
                            <span class="d-block d-md-none"><i class="icon-magnifier"></i></span>
                            {else}
                            <span class="d-none d-md-block"><i
                                        class="icon-checkmark-circle mr-2"></i>{l s='Accéder aux résultats' mod='ads_algolia_front'}</span>
                            <span class="d-block d-md-none"><i class="icon-chevron-right-circle"></i></span>
                        {/if}
                    </button>
                </div>
            </form>
            <div class="homepage-motor clearfix d-flex justify-content-between align-content-stretch mb-3">
                <div class="homepage-motor__dropdown btn-group dropup w-100 mr-3">
                    <div id="feature-type_de_vehicule"
                         class="clearfix auto-collapsed ais-refinement-list--item__block"></div>
                </div>
                <div class="homepage-motor__dropdown btn-group dropup w-100 mr-3">
                    <div id="vehicle-brand" class="clearfix auto-collapsed ais-refinement-list--item__block"></div>
                </div>
                <div class="homepage-motor__dropdown btn-group dropup w-100 mr-3">
                    <div id="vehicle-model" class="clearfix auto-collapsed ais-refinement-list--item__block"></div>
                </div>
                <div class="homepage-motor__dropdown btn-group dropup w-100">
                    <div id="feature-energy" class="clearfix auto-collapsed ais-refinement-list--item__block"></div>
                </div>
            </div>
            <div class="container_slider_price_mensualite clearfix w-50 d-none d-md-block">
                <div class="d-flex flex-wrap justify-content-start align-items-stretch p-0" id="tab_price_mensualite">
                    <button class="btn btn-sm rgba-stylish m-0 mr-2 button_collapse_price collapsed" type="button"
                            data-toggle="collapse" data-target="#price_tab"
                            aria-expanded="false"
                            aria-controls="price_tab">{l s='Budget' mod='ads_algolia_front'}</button>
                    {if Configuration::get('PS_GS_FINANCEMENT_ENABLED')}
                        <button class="btn btn-sm rgba-stylish m-0 button_collapse_mensualite collapsed" type="button"
                                data-toggle="collapse" data-target="#mensualite_tab"
                                aria-expanded="false"
                                aria-controls="mensualite_tab">{l s='Mensualité' mod='ads_algolia_front'}</button>
                    {/if}
                </div>
                <div class="m-0" id="tab_price_mensualite_content">
                    <div class="collapse multi-collapse" id="price_tab" data-parent="#tab_price_mensualite_content">
                        <div class="p-4 price__slider ion-slider-wrapper clearfix">
                            <input type="text" id="price" name="slider-price" value=""/>
                        </div>
                    </div>
                    {if Configuration::get('PS_GS_FINANCEMENT_ENABLED')}
                        <div class="collapse multi-collapse" id="mensualite_tab"
                             data-parent="#tab_price_mensualite_content">
                            <div class="p-4 monthly__slider ion-slider-wrapper clearfix">
                                <input type="text" id="monthly" name="slider-monthly" value=""/>
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
            <div class="container_link_all_offers text-right mt-3">
                <a href="/recherche"
                   class="d-inline-block link_all_offers">{l s='Tous nos véhicules' mod='ads_algolia_front'}<i
                            class="fa fa-angle-right ml-2"></i></a>
            </div>
        </div>
</section>
<script type="text/html" id="stats-container-template">
    {literal}
        {{#query}}{/literal}{l s='Résultat pour la recherche' mod='ads_algolia_front'}{literal} "{{ query }}" :{{/query}}{{#hasNoResults}}
        <span class="badge badge-pill bg-colored">0</span> <span
            class="text-critere">{/literal}{l s='véhicule trouvé' mod='ads_algolia_front'}{literal}</span>{{/hasNoResults}}{{#hasOneResult}}
        <span class="badge badge-pill bg-colored">1</span> <span
            class="text-critere">{/literal}{l s='véhicule trouvé' mod='ads_algolia_front'}{literal}</span>{{/hasOneResult}}{{#hasManyResults}}
        <span class="badge badge-pill bg-colored">{{nbHits}}</span> <span
            class="text-critere">{/literal}{l s='véhicules trouvés' mod='ads_algolia_front'}{literal}</span>{{/hasManyResults}}
    {/literal}
</script>

{*oldone with react *}
{*
<section id="search-home" class="color-block hidden" data-bgactive="{if $home_search_bg}1{else}0{/if}"
         {if $home_search_bg}style="background-image:url({$home_search_bg})"{/if}>
    <div class="hero-zone__content mx-auto">
        <div class="container_title_search_home">
            <h2>{if isset($home_search_title) && $home_search_title}{$home_search_title}{else}{l s='Acheter sa voiture avec ' mod='ads_algolia_front'}{$shop_name}{/if}</h2>
            <h3>{if isset($home_search_subtitle) && $home_search_subtitle}{$home_search_subtitle}{else}{l s='Moins cher | sans effort | sans risque' mod='ads_algolia_front'}{/if}</h3>
        </div>
        <div class="container_critere_search_home col-12 col-sm-10 mx-auto">
            <form id="search-home-section" action="{$path_to_list}" class="form-inline md-form mb-3">
                <div class="group-form group-form--search-home d-flex justify-content-center w-100">
                    <input type="search" id="search-home-input"
                           class="form-control input-alternate mr-sm-3 autocomplete-search" value=""
                           placeholder="{l s='Recherche par marque et/ou modèle' mod='ads_algolia_front'}"
                           autocomplete="off" name="q">
                    <button type="submit" class="btn btn-search-home ml-3"><i class="fa fa-search"
                                                                              aria-hidden="true"></i></button>
                </div>
            </form>
            <div class="homepage-motor clearfix d-flex align-content-stretch">
                <div class="text_or_search text-left"><span>{l s='ou' mod='ads_algolia_front'}</span></div>
                <div class="homepage-motor__dropdown btn-group dropup w-100 mr-3">
                    <button id="makermodels" class="btn dropdown-btn w-100" type="button">
                        {l s='Marque / Modèle' mod='ads_algolia_front'}
                    </button>
                    <div class="dropdown-menu dropdown-menu--search-home dropdown-menu--search-home--maker"
                         aria-labelledby="makermodels">
                        <div id="manufacturer-model-selection"></div>
                    </div>
                </div>
                <div class="homepage-motor__dropdown btn-group dropup w-100 mr-3">
                    <button id="category" class="btn dropdown-btn w-100" type="button">
                        {l s='Energie' mod='ads_algolia_front'}
                    </button>
                    <div class="dropdown-menu dropdown-menu--search-home dropdown-menu--search-home--energy"
                         aria-labelledby="energy">
                        <div id="energy-selection"></div>
                    </div>
                </div>
                <div class="homepage-motor__dropdown btn-group dropup w-100 mr-3">
                    <button id="category" class="btn dropdown-btn w-100" type="button">
                        {l s='Type' mod='ads_algolia_front'}
                    </button>
                    <div class="dropdown-menu dropdown-menu--search-home dropdown-menu--search-home--category"
                         aria-labelledby="category">
                        <div id="bodywork-selection"></div>
                    </div>
                </div>
                <div class="homepage-motor__dropdown btn-group dropup w-100">
                    <button id="price" class="btn dropdown-btn w-100" type="button">
                        {l s='Prix' mod='ads_algolia_front'}
                    </button>
                    <div class="dropdown-menu dropdown-menu--search-home dropdown-menu--search-home--price"
                         aria-labelledby="price">
                        {if !Configuration::get('PS_GS_FINANCEMENT_ENABLED')}
                            <div id="price-selection"></div>
                        {else}
                            <div class="row">
                                <div id="price-selection" class="col-md-6 pl-0 pr-1"></div>
                                <div id="monthly-selection" class="col-md-6 pr-0 pl-1"></div>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
            <div class="container_link_all_offers text-right mt-3">
                <a href="{$path_to_list}"
                   class="d-inline-block link_all_offers">{l s='Tous nos véhicules' mod='ads_algolia_front'}</a>
            </div>
        </div>
    </div>
</section>
*}