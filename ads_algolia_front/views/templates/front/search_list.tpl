{include file="./breadcrumb_list.tpl"}

{addJsDefL name=min_item}{l s='Please select at least one product' js=1}{/addJsDefL}
{addJsDefL name=max_item}{l s='Vous ne pouvez pas ajouter plus de %d véhicule(s) au comparateur' sprintf=$comparator_max_item js=1}{/addJsDefL}
{addJsDef comparedProductsIds=$compared_products}
{addJsDef comparator_max_item=$comparator_max_item}
<script type="text/html" id="no-result">
    {literal}
    <div class="no-result no-result--search-list text-center w-100 my-4">
        <h1 class="mb-4">Votre recherche n'aboutit à aucun résultat dans notre stock de véhicule</h1>
        <div class="d-flex flex-wrap align-content-stretch justify-content-center">
            <div class="no-result__content-left mr-md-5">
                <p>Il y a peut-être trop de critères dans vos filtres de recherche, supprimez en quelques uns.</p>
                <div class="view-vehicle__expert__portrait">
                    <img src="{/literal}{$link_img_default_no_result}{literal}"
                         alt="Supprimer certains critères dans la recherche" class="img-fluid z-depth-1">
                </div>
            </div>
            <div class="no-result__content-right">
                <h2 class="mb-3">N'hésitez pas à nous contacter</h2>
                <div class="container_buttons">
                    <h4>Faire estimer mon véhicule auprès de mon agence</h4>
                    <a href="{/literal}{$link_default_reprise_vehicle}{literal}" class="btn"
                       title="Faire reprendre mon véhicule">
                        <i class="fa fa-file mr-2"></i>Faire reprendre mon véhicule
                    </a>
                </div>
                <div class="container_buttons">
                    <h4>Prendre rendez-vous et contacter mon agence</h4>
                    <a href="{/literal}{$link_default_contact}{literal}" class="btn" title="Contacter ma concession">
                        <i class="fa fa-envelope mr-2"></i>Contacter ma concession
                    </a>
                </div>
            </div>
        </div>
    </div>
    {/literal}
</script>
<script type="text/html" id="stats-container-template">
    {literal}
    <h2 class="m-0 py-2">{{#query}}Résultat pour la recherche "{{ query }}"
        :{{/query}}{{#hasNoResults}}{/literal}{l s='Aucun véhicule en vente' mod='ads_algolia_front'}{literal}{{/hasNoResults}}{{#hasOneResult}}
            <span>1</span>
        {/literal}{l s='véhicule en vente' mod='ads_algolia_front'}{literal}{{/hasOneResult}}{{#hasManyResults}}
            <span>{{nbHits}}</span>
        {/literal}{l s='véhicules en vente' mod='ads_algolia_front'}{literal}{{/hasManyResults}}</h2>
    {/literal}
</script>
<script type="text/html" id="m-stats-container-template">
    {literal}
    <h2 class="m-0">{{#query}}Résultat pour la recherche "{{ query }}"
        :{{/query}}{{#hasNoResults}}{/literal}{l s='Aucun véhicule en vente' mod='ads_algolia_front'}{literal}{{/hasNoResults}}{{#hasOneResult}}
            <span>1</span>
        {/literal}{l s='véhicule en vente' mod='ads_algolia_front'}{literal}{{/hasOneResult}}{{#hasManyResults}}
            <span>{{nbHits}}</span>
        {/literal}{l s='véhicules en vente' mod='ads_algolia_front'}{literal}{{/hasManyResults}}</h2>
    {/literal}
</script>
{if Module::isInstalled('ads_encheres') && Module::isEnabled('ads_encheres')}
    <script type="text/html" id="vehicle-item-template">
        {literal}
        <div class="container_vehicule clearfix" itemtype="http://schema.org/Product" itemscope>
            <meta itemprop="name" content="{{ name}}"/>
            <meta itemprop="description" content="{{ name}}"/>
            <meta itemprop="brand" content="{{ marque.name}}"/>
            <meta itemprop="model" content="{{ modele.name}}"/>
            <meta itemprop="manufacturer" content="{{ manufacturer}}"/>
            <meta itemprop="productID" content="{{ objectID}}"/>
            <meta itemprop="category" content="{{ category}}"/>
            <!--Card-->
            <div class="card z-depth-0">
                <!--Grid column-->
                <div class="d-flex align-items-top items_img_vehicule">
                    <!--Card image-->
                    <div class="container_img_vehicule view overlay hm-white-slight">
                        <img src="{{ image_link_large}}" itemprop="image" class="img-fluid"/>
                        <div class="mask waves-effect waves-light"></div>
                        <div class="item-vehicle__content__labels">
                            {{#eco_prime}}
                            {/literal}
                            {include file="$tpl_dir./icons-svg.tpl" type='eco_prime'}
                            {literal}
                            {{/eco_prime}}
                        </div>
                    </div>
                    <!--Card image-->
                </div>
                <!--Grid column-->
                <div class="container_infos">
                    <!--Card content-->
                    <div class="card-body text-left">
                        <div class="card-title">
                            <!-- Marque et Modèle véhicule -->
                            <h3 class="container_name_product text-uppercase mb-1">
                                <strong>{{ marque.name}} {{ modele.name}}</strong>
                            </h3>
                        </div>
                        <div class="container_infos_bottom_vehicule d-flex flex-wrap align-content-stretch justify-content-between">
                            <p class="m-0">
                                <span class="container_kilometrage">
                                    {{ kilometrage}}
                                </span>
                            </p>
                            <p class="m-0">
                                {{ annee}}
                            </p>
                        </div>
                        <!--Card footer-->
                        <div class="card-footer pb-0">
                            <div class="container_price_mensualite d-flex flex-wrap align-content-stretch justify-content-between"
                                itemprop="offers" itemtype="http://schema.org/Offer" itemscope>
                                <link itemprop="url" href="{{ link}}"/>
                                <div class="container_price text-center">
                                    <meta itemprop="seller" content="merchant"/>
                                    <meta itemprop="availability" content="https://schema.org/InStock"/>
                                    <meta itemprop="eligibleQuantity" content="{{stock_qty}}"/>
                                    {{#show_price}}
                                        <meta itemprop="priceCurrency" content="{{currency_iso_code}}"/>
                                        {{^price_empty}}
                                        {{^is_ht}}
                                        <meta itemprop="price" content="{{ price_tax_incl_raw}}"/>
                                        {{/is_ht}}
                                        {{#is_ht}}
                                        <meta itemprop="price" content="{{ price_tax_excl_raw}}"/>
                                        {{/is_ht}}
                                        {{/price_empty}}
                                        <div itemprop="seller" itemtype="http://schema.org/Organization" itemscope>
                                            <meta itemprop="name" content="{{ supplier.name}}"/>
                                        </div>
                                        <span>
                                            {{#price_empty}}
                                                Estimation en cours
                                            {{/price_empty}}
                                            {{^price_empty}}
                                                {{^is_ht}}
                                                    Estimation : {{ price_tax_incl}} <small>TTC</small>{{#is_vu}}<sup>*<span>Offre réservée aux professionnels</span></sup>{{/is_vu}}
                                                {{/is_ht}}
                                                {{#is_ht}}
                                                    Estimation : {{ price_tax_excl}} <small>HT</small>{{#is_vu}}<sup>*<span>Offre réservée aux professionnels</span></sup>{{/is_vu}}
                                                {{/is_ht}}
                                            {{/price_empty}}
                                        </span>
                                    {{/show_price}}
                                </div>
                                {{#hasMonthlyPayment}}
                                <div class="right item-vehicle_monthly empty" data-product="{{ objectID}}"></div>
                                {{/hasMonthlyPayment}}
                            </div>
                        </div>
                    </div>
                    <!--Card content-->
                </div>
                <a href="{{ link}}" title="{{ linkTitle}}" class="link_product"></a>
                <div class="container_btn">
                    <a class="add_to_compare m-1 p-1 d-block" href="{{ link}}" data-product="{{ objectID}}"
                    data-toggle="tooltip" data-placement="top" title="Comparer">
                        <i class="fa fa-exchange pr-2 pt-2 pb-1"></i>
                    </a>
                </div>
            </div>
            <!--Card-->
        </div>
        {/literal}
    </script>
{else}
    <script type="text/html" id="vehicle-item-template">
        {literal}
        <div class="container_vehicule clearfix" itemtype="http://schema.org/Product" itemscope>
            <meta itemprop="name" content="{{ name}}"/>
            <meta itemprop="description" content="{{ name}}"/>
            <meta itemprop="brand" content="{{ marque.name}}"/>
            <meta itemprop="model" content="{{ modele.name}}"/>
            <meta itemprop="manufacturer" content="{{ manufacturer}}"/>
            <meta itemprop="productID" content="{{ objectID}}"/>
            <meta itemprop="category" content="{{ category}}"/>
            <!--Card-->
            <div class="card z-depth-0">
                <!--Grid column-->
                <div class="d-flex align-items-top items_img_vehicule">
                    <!--Card image-->
                    <div class="container_img_vehicule view overlay hm-white-slight">
                        <img src="{{ image_link_large}}" itemprop="image" class="img-fluid"/>
                        <div class="mask waves-effect waves-light"></div>
                        <div class="item-vehicle__content__labels">
                            {{#eco_prime}}
                            {/literal}
                            {include file="$tpl_dir./icons-svg.tpl" type='eco_prime'}
                            {literal}
                            {{/eco_prime}}
                        </div>
                    </div>
                    <!--Card image-->
                </div>
                <!--Grid column-->
                <div class="container_infos">
                    <!--Card content-->
                    <div class="card-body text-left">
                        <div class="card-title">
                            <!-- Marque et Modèle véhicule -->
                            <h3 class="container_name_product text-uppercase mb-1">
                                <strong>{{ marque.name}} {{ modele.name}}</strong>
                            </h3>
                            <!-- Version ou Finition véhicule -->
                            <h4 class="container_version_product truncate mb-4">
                                {{ version}}{{ finition}}
                            </h4>
                        </div>
                        <div class="container_localisation text-left py-2">
                            <i class="fa fa-map fa-2x mr-2"></i>Disponible à {{ supplier.city}}
                        </div>
                        <div class="container_infos_bottom_vehicule d-flex flex-wrap align-content-stretch justify-content-between">
                            <p class="m-0">
                            {{^is_vo}}
                                {{#remise}}
                                    <span class="container_remise">
                                            {{ remise}}
                                    </span>
                                {{/remise}}
                            {{/is_vo}}
                            {{#is_vo}}
                                <span class="container_kilometrage">
                                        {{ kilometrage}}
                                </span>
                            {{/is_vo}}
                            </p>
                            {{#have_images}}
                            <p class="m-0">
                                {{ number_images}}<i class="fa fa-camera ml-2"></i>
                            </p>
                            {{/have_images}}
                        </div>
                        <!--Card footer-->
                        <div class="card-footer pb-0">
                            <div class="container_price_mensualite d-flex flex-wrap align-content-stretch justify-content-between"
                                itemprop="offers" itemtype="http://schema.org/Offer" itemscope>
                                <link itemprop="url" href="{{ link}}"/>
                                <div class="container_price text-center">
                                    <p class="m-0">
                                        <meta itemprop="seller" content="merchant"/>
                                        <meta itemprop="availability" content="https://schema.org/InStock"/>
                                        <meta itemprop="eligibleQuantity" content="{{stock_qty}}"/>
                                        {{#show_price}}
                                        <meta itemprop="priceCurrency" content="{{currency_iso_code}}"/>
                                        {{^price_empty}}
                                        {{^is_ht}}
                                        <meta itemprop="price" content="{{ price_tax_incl_raw}}"/>
                                        {{/is_ht}}
                                        {{#is_ht}}
                                        <meta itemprop="price" content="{{ price_tax_excl_raw}}"/>
                                        {{/is_ht}}
                                        {{/price_empty}}
                                    <div itemprop="seller" itemtype="http://schema.org/Organization" itemscope>
                                        <meta itemprop="name" content="{{ supplier.name}}"/>
                                    </div>
                                    <span>
                                            {{#price_empty}}
                                                Prix : Nous consulter
                                            {{/price_empty}}
                                            {{^price_empty}}
                                                {{^is_ht}}
                                                    {{ price_tax_incl}} <small>TTC</small>{{#is_vu}}<sup>*<span>Offre réservée aux professionnels</span></sup>{{/is_vu}}
                                                {{/is_ht}}
                                                {{#is_ht}}
                                                    {{ price_tax_excl}} <small>HT</small>{{#is_vu}}<sup>*<span>Offre réservée aux professionnels</span></sup>{{/is_vu}}
                                                {{/is_ht}}
                                            {{/price_empty}}
                                        </span>
                                        <br>
                                        
                                        <span class="old-price">
                                            {{^price_empty}}
                                                {{^is_ht}}
                                                    {{ old_price_tax_incl}} <small>TTC</small>{{#is_vu}}<sup>*<span>Offre réservée aux professionnels</span></sup>{{/is_vu}}
                                                {{/is_ht}}
                                            {{/price_empty}}
                                        </span>
                                        <style>
                                        .old-price {
                                                    margin-bottom: 0;
                                                    font-size: 14px;
                                                    line-height: 14px;
                                                    font-weight: 300;
                                                    text-decoration: line-through;
                                                    color: #7777;
                                            }
                                        </style>
                                    {{/show_price}}
                                    </p>

                                    
                               
                                </div>
                                {{#hasMonthlyPayment}}
                                <div class="right item-vehicle_monthly empty" data-product="{{ objectID}}"></div>
                                {{/hasMonthlyPayment}}
                            </div>
                        </div>
                    </div>
                    <!--Card content-->
                </div>
                <a href="{{ link}}" title="{{ linkTitle}}" class="link_product"></a>
                <div class="container_btn">
                    <a class="add_to_compare m-1 p-1 d-block" href="{{ link}}" data-product="{{ objectID}}"
                    data-toggle="tooltip" data-placement="top" title="Comparer">
                        <i class="fa fa-exchange pr-2 pt-2 pb-1"></i>
                    </a>
                </div>
            </div>
            <!--Card-->
        </div>
        {/literal}
    </script>
{/if}

<div id="container_filter_top_listing"
     class="d-flex flex-wrap align-content-stretch justify-content-between px-0 px-md-3">
    <div class="container_header_listing w-100">
        <h1 class="text-center text-uppercase d-none d-md-block">{if isset($title_h1_listing) && $title_h1_listing}{$title_h1_listing}{else}{l s='Recherchez votre véhicule dans notre stock'}{/if}</h1>
        {if isset($ads_seo_description_top) && $ads_seo_description_top}
            <div class="py-3 container_seo_content container_seo_content_top">{$ads_seo_description_top}</div>
        {/if}
        <div class="d-flex flex-wrap align-content-stretch justify-content-between mb-2 mb-sm-3">
            <div class="stats-container d-none d-md-block"></div>
            {* TODO - Finish search alert development}
            <div class="alert-container d-none d-md-block">
                <a href="#" class="quick-view nav-link colored py-2"
                   rel="{$link->getModuleLink('ads_algolia_front', 'adsalertagl')}">
                    <i class="fa fa-bell" aria-hidden="true"></i>
                    {l s='Alerte mail' mod='ads_algolia_front'}
                </a>
            </div>
            {/*}
            <div class="container_fixed_mobile d-flex flex-wrap align-content-stretch justify-content-start">
                <div id="container_btn_display_mobile_filter" class="d-block d-md-none col-4">
                    <a href="#" data-activates="left_column" class="button-collapse btn btn-filter-mobile d-block">
                        <i class="fa fa-filter fa-2x"></i>
                    </a>
                </div>
                <div id="sort-by-container" class="col-6 pl-0 col-md-auto"></div>
            </div>
            <div class="m-stats-container w-80 d-block d-md-none text-center mt-3"></div>
            <div class="alert-container w-20 d-block d-md-none text-center mt-3">
                <a href="#" class="quick-view nav-link colored p-0"
                   rel="{$link->getModuleLink('ads_algolia_front', 'adsalertagl')}">
                    <i class="fa fa-bell" aria-hidden="true"></i>
                    {l s='Alerte mail' mod='ads_algolia_front'}
                </a>
            </div>
        </div>
        <div class="critere_selected d-flex flex-nowrap align-content-stretch justify-content-start">
            <div id="clear-all" class="pr-md-3"></div>
            <div id="current-refined-values"></div>
        </div>
    </div>
</div>

<div id="infinite-hits-container">
    <!-- Hits widget will appear here -->
</div>

{if isset($ads_seo_description_footer) && $ads_seo_description_footer}
    <div class="py-3 container_seo_content container_seo_content_footer">{$ads_seo_description_footer}</div>
{/if}