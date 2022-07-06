<script type="text/template" id="autocomplete-template">
    <div class="result">
        <div class="title">
            {{#image_link_small}}
            <div class="thumb">
                <img style="width: 30px" src="//{{{image_link_small}}}" />
            </div>
            {{/image_link_small}}
            <div class="info{{^image_link_small}}-without-thumb{{/image_link_small}}">
            {{#_highlightResult.path}}
                {{{_highlightResult.path.value}}} ({{product_count}})
            {{/_highlightResult.path}}
            {{^_highlightResult.path}}
                {{{ _highlightResult.name.value }}}
            {{/_highlightResult.path}}

            
            {{#_highlightResult.category}}
                <div class="algoliasearch-autocomplete-price">{{{_highlightResult.category.value}}}</div>
            {{/_highlightResult.category}}
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</script>

<script type="text/template" id="instant-content-template">
    <div class="hits{{#facets_count}} with_facets{{/facets_count}} center_column col-xs-12 col-sm-12">
        {{#hits.length}}
        <h1 class="page-heading algolia-product-listing">
            <span class="heading-counter">
            {{nbHits}} result{{^nbHits_one}}s{{/nbHits_one}} {{#query}}found matching "<strong>{{query}}</strong>"{{/query}}
            </span>
            <div class="infos">
                {{#sorting_indices.length}}
                <div style="float: right; margin-right: 10px;">
                    Order by
                    <select id="index_to_use">

                        {{#sorting_indices}}
                        <option {{#sortSelected}}{{index_name}}{{/sortSelected}} value="{{index_name}}">{{label}}</option>
                        {{/sorting_indices}}
                    </select>
                </div>
                {{/sorting_indices.length}}
                <div style="clear: both;"></div>
            </div>
        </h1>
        {{/hits.length}}


        <ul class="product_list grid row">


        {{#hits}}


        <li class="ajax_block_product col-xs-12 col-sm-6 col-md-4">
            <div class="product-container" itemscope itemtype="http://schema.org/Product">
                <div class="left-block">
                    <div class="product-image-container">
                        <a class="product_img_link" href="{{link}}" title="{{{ _highlightResult.name.value }}}" itemprop="url">
                            {{#image_link_large}}
                                <img class="replace-2x img-responsive" src="//{{{ image_link_large }}}" width="175" height="175"/>
                            {{/image_link_large}}

                        </a>
                        <div class="quick-view-wrapper-mobile">
                            <a class="quick-view-mobile" href="{{link}}" rel="{$product.link|escape:'html':'{{link}}'}">
                                <i class="icon-eye-open"></i>
                            </a>
                        </div>
                        <a class="quick-view" href="{{link}}" rel="{{link}}">
                            <span>Quick view</span>
                        </a>
                        <div class="content_price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">

                            <span itemprop="price" class="price product-price">
                              {{currency}}{{price_tax_excl}}
                            </span>

                        </div>
                    {{#newproduct}}
                        <a class="new-box" href="{{link}}">
                            <span class="new-label">New</span>
                        </a>
                   {{/newproduct}}            
                   {{#on_sale}}
                        <a class="sale-box" href="{{link}}">
                            <span class="sale-label">Sale!</span>
                        </a>
                   {{/on_sale}}

                    </div>
                </div>
                <div class="right-block">
                    <h5 itemprop="name">
                        <a class="product-name" href="{{link}}" title="{{{ _highlightResult.name.value }}}" itemprop="url" >
                           {{{name}}}
                        </a>
                    </h5>
                    <div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="content_price">
                        <span itemprop="price" class="price product-price">
                            {{currency}}{{price_tax_excl}}
                        </span>
                    </div>
                    <div class="button-container" style="display:none">
                        {{#stock_qty}}
                            <a class="button ajax_add_to_cart_button btn btn-default" rel="nofollow" title="Add to cart" data-id-product="{{objectID}}">
                                <span>Add to cart</span>
                            </a>
                        {{/stock_qty}}

                    </div>

                    <span class="availability">
                        {{#stock_qty}}
                        <span class="available-now">In stock</span>
                        {{/stock_qty}}
                        {{^stock_qty}}
                            <span class="out-of-stock">Back Ordered</span>
                        {{/stock_qty}}
                    </span>
                </div>

            </div>
        </li>


        {{/hits}}

        </ul>
        {{^hits.length}}
        <div class="infos">
            No results found matching "<strong>{{query}}</strong>". <span class="clear">Clear query and filters</span>
        </div>
        {{/hits.length}}
        <div style="clear: both;"></div>
    </div>
</script>

<script type="text/template" id="instant-facets-template">

<div class="column col-xs-12 col-sm-12">
    

<div class="facets{{#count}} with_facets{{/count}} block" >
    <h2 class="title_block">Refine Your Search</h2>
    <div class="block_content">
           <div class="facets-reset"><a href="javascript:void(0)" title="Reset All Filters" style="display:none" id="facets-reset-btn">Reset All Filters</a></div>
        {{#facets}}
        {{#count}}
        <div class="facet">
            <div class="name">
                {{ facet_categorie_name }}
            </div>
            <div class="filters-container">
                {{#sub_facets}}

                    {{#type.menu}}
                    <div data-tax="{{tax}}" data-name="{{nameattr}}" data-type="menu" class="{{#checked}}checked {{/checked}}sub_facet menu">
                        <input data-tax="{{tax}}" {{#checked}}checked{{/checked}} data-name="{{nameattr}}" class="facet_value" type="checkbox" />
                        {{name}} {{#print_count}}({{count}}){{/print_count}} {{#checked}}{{/checked}}
                    </div>
                    {{/type.menu}}

                    {{#type.conjunctive}}
                    <div data-name="{{tax}}" data-type="conjunctive" class="{{#checked}}checked {{/checked}}sub_facet conjunctive">
                        <input data-tax="{{tax}}" {{#checked}}checked{{/checked}} data-name="{{nameattr}}" class="facet_value" type="checkbox" />
                        {{name}} ({{count}}) {{#checked}}{{/checked}}
                    </div>
                    {{/type.conjunctive}}

                    {{#type.slider}}
                    <div class="algolia-slider algolia-slider-true" data-tax="{{tax}}" data-min="{{min}}" data-max="{{max}}"></div>
                    <div class="algolia-slider-info">
                        <div class="min" style="float: left;">{{current_min}}</div>
                        <div class="max" style="float: right;">{{current_max}}</div>
                        <div style="clear: both"></div>
                    </div>
                    {{/type.slider}}

                    {{#type.disjunctive}}
                    <div data-name="{{tax}}" data-type="disjunctive" class="{{#checked}}checked {{/checked}}sub_facet disjunctive">
                        <input data-tax="{{tax}}" {{#checked}}checked{{/checked}} data-name="{{nameattr}}" class="facet_value" type="checkbox" />
                        {{name}} ({{count}})
                    </div>
                    {{/type.disjunctive}}

                {{/sub_facets}}
            </div>
        </div>
        {{/count}}
        {{/facets}}
    </div>

</div>
</div>
</script>

<script type="text/template" id="instant-pagination-template">
<div class="pagination-wrapper{{#facets_count}} with_facets{{/facets_count}}">
    <div class="text-center">
        <ul class="algolia-pagination">
            <a href="#" data-page="{{prev_page}}">
                <li {{^prev_page}}class="disabled"{{/prev_page}}>
                    &laquo;
                </li>
            </a>

            {{#pages}}
            <a href="#" data-page="{{number}}" return false;">
                <li class="{{#current}}active{{/current}}{{#disabled}}disabled{{/disabled}}">
                    {{ number }}
                </li>
            </a>
            {{/pages}}

            <a href="#" data-page="{{next_page}}">
                <li {{^next_page}}class="disabled"{{/next_page}}>
                    &raquo;
                </li>
            </a>
        </ul>
    </div>
</div>
</script>